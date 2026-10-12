import { proDriverRequest, viewableDateTimeHelper } from './helpers.js';

const MESSAGE_POLL_INTERVAL = 3000;
const contactsLoading = document.querySelector('#messenger-contacts-loading');
const contactsError = document.querySelector('#messenger-contacts-error');
const contactsEmpty = document.querySelector('#messenger-contacts-empty');
const dispatchContacts = document.querySelector('#messenger-dispatch-contacts');
const driverContacts = document.querySelector('#messenger-driver-contacts');
const contactSearch = document.querySelector('#messenger-contact-search');
const driverToken = document.querySelector('#drvrToken')?.value;
const conversationName = document.querySelector('#messenger-conversation-name');
const conversationDescription = document.querySelector('#messenger-conversation-description');
const conversationError = document.querySelector('#messenger-conversation-error');
const conversationLoading = document.querySelector('#messenger-conversation-loading');
const conversationPlaceholder = document.querySelector('#messenger-conversation-placeholder');
const dtHelper = viewableDateTimeHelper;
const messageArea = document.querySelector('#messenger-message-area');
const messageList = document.querySelector('#messenger-message-list');
const messageForm = document.querySelector('#messenger-message-form');
const messageInput = document.querySelector('#messengerMessage');
const sendButton = document.querySelector('#messenger-send-button');
let latestRetrievedMessageId = 0;
let messagePollTimer = null;
let messagePollInFlight = false;
let messagePollFailures = 0;

let conversationMessages = [];
let isSendingMessage = false;
let pendingSend = null;

let activeConversationId = null;
let activeContact = null;
let openingRecipientId = null;
let conversationRequestVersion = 0;

let messengerContacts = [];

function hideElement(element) {
    element?.classList.add('d-none');
};

function showElement(element) {
    element?.classList.remove('d-none');
};

function createContactButton(contact) {
    const button = document.createElement('button');

    button.type = 'button';
    button.className = 'list-group-item list-group-item-action';
    button.dataset.recipientId = String(contact.userId);
    button.dataset.contactRole = contact.role;

    const layout = document.createElement('div');
    layout.className = 'd-flex justify-content-between align-items-center';

    const contactDetails = document.createElement('div');
    contactDetails.className = 'me-2 overflow-hidden';

    const displayName = document.createElement('div');
    displayName.className = contact.role === 'dispatch' ? 'fw-bold' : 'fw-semibold';

    displayName.textContent = contact.displayName;

    const description = document.createElement('small');
    description.className = 'text-body-secondary d-block text-truncate';
    description.textContent = contact.role === 'dispatch' ? 'Dispatch communications' : 'Start or open conversation';

    contactDetails.append(displayName, description);

    const icon = document.createElement('i');
    icon.className = 'fa-solid fa-chevron-right text-body-secondary';
    icon.setAttribute('aria-hidden', 'true');

    layout.append(contactDetails, icon);
    button.appendChild(layout);

    const selectedRecipientId = openingRecipientId ?? activeContact?.userId;
    const isSelected = String(contact.userId) === String(selectedRecipientId);

    button.classList.toggle('active', isSelected);
    button.setAttribute('aria-pressed', String(isSelected));

    button.addEventListener('click', () => {
        openMessengerConversation(contact);
    });

    return button;
};

function renderContacts(contacts) {
    const dispatchFragment = document.createDocumentFragment();
    const driverFragment = document.createDocumentFragment();

    contacts.forEach(contact => {
        const contactButton = createContactButton(contact);

        if (contact.role === 'dispatch') {
            dispatchFragment.appendChild(contactButton);
            return;
        }

        if (contact.role === 'driver') {
            driverFragment.appendChild(contactButton);
        }
    });

    dispatchContacts?.replaceChildren(dispatchFragment);
    driverContacts?.replaceChildren(driverFragment);

    const visibleContactCount = (dispatchContacts?.children.length ?? 0) + (driverContacts?.children.length ?? 0);

    if (visibleContactCount === 0) {
        showElement(contactsEmpty);
    } else {
        hideElement(contactsEmpty);
    }
};

function filterDriverContacts() {
    const searchValue = contactSearch?.value.trim().toLowerCase() ?? '';

    const filteredContacts = messengerContacts.filter(contact => {
        if (contact.role === 'dispatch') {
            return true;
        }

        return contact.displayName.toLowerCase().includes(searchValue);
    });

    renderContacts(filteredContacts);
};

function showContactsError(message) {
    hideElement(contactsLoading);

    if (contactsError) {
        contactsError.textContent = message || 'Messenger contacts could not be loaded.';
        showElement(contactsError);
    }
};

async function loadMessengerContacts() {
    hideElement(contactsError);
    hideElement(contactsEmpty);
    showElement(contactsLoading);

    try {
        const response = await proDriverRequest('/messenger-contacts', {
            method: 'GET',
            cache: 'no-store',
            headers: {
                'X-CSRF-Token': driverToken
            }
        });

        if (!response || response.status !== 'success' || !Array.isArray(response.contacts)) {
            throw new Error(response?.message || 'Invalid Messenger contacts response.');
        }

        messengerContacts = response.contacts;
        renderContacts(messengerContacts);
        hideElement(contactsLoading);
    } catch (error) {
        console.error('[MESSENGER] Failed loading contacts:', error);
        showContactsError(error?.message || 'Messenger contacts could not be loaded.');
    }
};

function updateSelectedContact() {
    const selectedRecipientId = openingRecipientId ?? activeContact?.userId;

    const buttons = [
        ...(dispatchContacts?.querySelectorAll('button[data-recipient-id]') ?? []),
        ...(driverContacts?.querySelectorAll('button[data-recipient-id]') ?? [])
    ];

    buttons.forEach(button => {
        const isSelected = button.dataset.recipientId === String(selectedRecipientId);

        button.classList.toggle('active', isSelected);
        button.setAttribute('aria-pressed', String(isSelected));
    });
};

function renderMessengerMessages(messages, contact) {
    const fragment = document.createDocumentFragment();

    messages.forEach(message => {
        const row = document.createElement('div');
        row.className = message.is_own ? 'message-row message-row-sent mb-3 text-end' : 'message-row message-row-received mb-3';

        const meta = document.createElement('div');
        meta.className = 'message-meta small text-body-secondary mb-1';

        const senderName = message.is_own ? 'You' : contact.displayName;
        meta.textContent = `${senderName} · ${dtHelper(message.created_at, 'datetime')}`;

        const bubble = document.createElement('div');
        bubble.className = message.is_own ? 'message-bubble sent-bubble d-inline-block' : contact.role === 'dispatch' ? 'message-bubble dispatch-bubble' : 'message-bubble received-bubble';

        bubble.style.whiteSpace = 'pre-wrap';
        bubble.style.overflowWrap = 'anywhere';
        bubble.textContent = message.message_body;

        row.append(meta, bubble);
        fragment.appendChild(row);
    });

    messageList?.replaceChildren(fragment);

    if (messages.length === 0) {
        if (conversationPlaceholder) {
            conversationPlaceholder.textContent = 'No messages yet.';
        }

        showElement(conversationPlaceholder);
    } else {
        hideElement(conversationPlaceholder);
    }
};

async function loadMessengerHistory(conversationId, contact, requestVersion) {
    if (conversationDescription) {
        conversationDescription.textContent = 'Loading messages...';
    }

    const response = await proDriverRequest(`/messenger-retrieve-messages?conversation_id=${encodeURIComponent(conversationId)}&after_message_id=0`, {
            method: 'GET',
            cache: 'no-store',
            headers: {
                'X-CSRF-Token': driverToken
            }
        }
    );

    if (requestVersion !== conversationRequestVersion) {
        return;
    }

    const messageData = response?.messageData;

    if (response?.status !== 'success' || !messageData || Number(messageData.conversation_id) !== conversationId || !Array.isArray(messageData.messages)) {
        throw new Error(response?.message || 'Messages could not be loaded.');
    }

    conversationMessages = messageData.messages;
    latestRetrievedMessageId = Number(messageData.latest_message_id);

    if (!Number.isSafeInteger(latestRetrievedMessageId) || latestRetrievedMessageId < 0) {
        throw new Error('Invalid Messenger message cursor.');
    }

    renderMessengerMessages(conversationMessages, contact);

    if (conversationDescription) {
        conversationDescription.textContent = contact.role === 'dispatch' ? 'Dispatch communications' : 'Driver conversation';
    }

    if (messageArea) {
        messageArea.scrollTop = messageArea.scrollHeight;
    }
};

function updateMessengerComposer() {
    const canCompose = activeConversationId !== null && activeContact !== null && openingRecipientId === null && !isSendingMessage;

    if (messageInput) {
        messageInput.disabled = !canCompose;
    }

    if (sendButton) {
        sendButton.disabled = !canCompose || !messageInput?.value.trim();
        sendButton.textContent = isSendingMessage ? 'Sending...' : 'Send';
    }
};

function stopMessengerPolling() {
    if (messagePollTimer !== null) {
        clearTimeout(messagePollTimer);
        messagePollTimer = null;
    }
};

function canPollMessenger() {
    return (!document.hidden && navigator.onLine && openingRecipientId === null && activeConversationId !== null && activeContact !== null);
};

function scheduleMessengerPoll(delay = MESSAGE_POLL_INTERVAL) {
    stopMessengerPolling();

    if (!canPollMessenger()) {
        return;
    }

    messagePollTimer = setTimeout(() => {
        messagePollTimer = null;
        pollMessengerMessages();
    }, delay);
};

async function pollMessengerMessages() {
    if (!canPollMessenger()) {
        return;
    }

    // Avoid overlapping requests, including across contact changes.
    if (messagePollInFlight || isSendingMessage) {
        scheduleMessengerPoll();
        return;
    }

    const conversationId = activeConversationId;
    const contact = activeContact;
    const requestVersion = conversationRequestVersion;
    const cursor = latestRetrievedMessageId;

    messagePollInFlight = true;

    try {
        const response = await proDriverRequest(
            `/messenger-retrieve-messages?conversation_id=${encodeURIComponent(conversationId)}&after_message_id=${cursor}`,
            {
                method: 'GET',
                cache: 'no-store',
                headers: {
                    'X-CSRF-Token': driverToken
                }
            }
        );

        if (requestVersion !== conversationRequestVersion || conversationId !== activeConversationId) {
            return;
        }

        const messageData = response?.messageData;
        const returnedCursor = Number(messageData?.latest_message_id);

        if (response?.status !== 'success' || !messageData || Number(messageData.conversation_id) !== conversationId || !Array.isArray(messageData.messages) || !Number.isSafeInteger(returnedCursor) || returnedCursor < cursor) {
            throw new Error(response?.message || 'New messages could not be loaded.');
        }

        const knownIds = new Set(conversationMessages.map(message => message.message_id));
        const newMessages = messageData.messages.filter(message => !knownIds.has(message.message_id));

        if (newMessages.length > 0) {
            const previousScrollTop = messageArea?.scrollTop ?? 0;
            const nearBottom = !messageArea || messageArea.scrollHeight - messageArea.scrollTop - messageArea.clientHeight <= 80;

            conversationMessages.push(...newMessages);
            conversationMessages.sort((a, b) => a.message_id - b.message_id);
            renderMessengerMessages(conversationMessages, contact);

            if (messageArea) {
                messageArea.scrollTop = nearBottom ? messageArea.scrollHeight : previousScrollTop;
            }
        }

        latestRetrievedMessageId = returnedCursor;
        messagePollFailures = 0;

        if (conversationDescription) {
            conversationDescription.textContent = contact.role === 'dispatch' ? 'Dispatch communications' : 'Driver conversation';
        }

    } catch (error) {
        if (requestVersion !== conversationRequestVersion || conversationId !== activeConversationId) {
            return;
        }

        messagePollFailures++;
        console.error('[MESSENGER] Polling failed:', error);

        if (conversationDescription) {
            conversationDescription.textContent = 'Connection interrupted. Retrying...';
        }

    } finally {
        messagePollInFlight = false;

        // A new conversation may already have scheduled its own check.
        if (messagePollTimer === null && canPollMessenger()) {
            const delay = Math.min(MESSAGE_POLL_INTERVAL * (2 ** Math.min(messagePollFailures, 4)), 30000);
            scheduleMessengerPoll(delay);
        }
    }
};

async function openMessengerConversation(contact) {
    if (isSendingMessage) {
        return;
    }

    if (openingRecipientId === contact.userId) {
        return;
    }

    if (openingRecipientId === null && activeContact?.userId === contact.userId && activeConversationId !== null) {
        return;
    }

    stopMessengerPolling();
    latestRetrievedMessageId = 0;
    messagePollFailures = 0;

    const requestVersion = ++conversationRequestVersion;

    openingRecipientId = contact.userId;
    activeConversationId = null;
    activeContact = null;
    conversationMessages = [];

    updateMessengerComposer();

    hideElement(conversationError);
    hideElement(conversationPlaceholder);
    showElement(conversationLoading);

    messageList?.replaceChildren();
    messageArea?.setAttribute('aria-busy', 'true');

    if (conversationName) {
        conversationName.textContent = contact.displayName;
    }

    if (conversationDescription) {
        conversationDescription.textContent = 'Opening conversation...';
    }

    updateSelectedContact();

    try {
        if (!driverToken) {
            throw new Error('Your session token is missing. Please reload the page.');
        }

        const response = await proDriverRequest('/messenger-conversation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': driverToken
            },
            body: JSON.stringify({
                recipient_id: contact.userId
            })
        });

        // A newer selection owns the panel now.
        if (requestVersion !== conversationRequestVersion) {
            return;
        }

        const conversationId = Number(response?.conversation?.conversation_id);
        if (response?.status !== 'success' || !Number.isSafeInteger(conversationId) || conversationId < 1) {
            throw new Error(response?.message || 'The conversation could not be opened.');
        }

        activeConversationId = conversationId;
        activeContact = contact;

        await loadMessengerHistory(conversationId, contact, requestVersion);
    } catch (error) {
        if (requestVersion !== conversationRequestVersion) {
            return;
        }

        activeConversationId = null;
        activeContact = null;

        console.error('[MESSENGER] Failed opening conversation:', error);

        if (conversationError) {
            conversationError.textContent = error?.message || 'The conversation could not be opened.';
            showElement(conversationError);
        }

        if (conversationDescription) {
            conversationDescription.textContent = 'Select the contact again to retry.';
        }

    } finally {
        if (requestVersion === conversationRequestVersion) {
            openingRecipientId = null;

            hideElement(conversationLoading);
            messageArea?.setAttribute('aria-busy', 'false');
            updateSelectedContact();
            updateMessengerComposer();
            scheduleMessengerPoll();
        }
    }
};

async function sendMessengerMessage(event) {
    event.preventDefault();

    if (isSendingMessage || openingRecipientId !== null || activeConversationId === null || activeContact === null) {
        return;
    }

    const messageBody = messageInput?.value.trim() ?? '';

    if (messageBody === '') {
        return;
    }

    hideElement(conversationError);

    try {
        if (!driverToken) {
            throw new Error('Your session token is missing. Please reload the page.');
        }

        /*
         * An unchanged retry uses the same UUID.
         * A different conversation or edited message gets a new UUID.
         */
        if (!pendingSend || pendingSend.conversationId !== activeConversationId || pendingSend.messageBody !== messageBody) {
            pendingSend = {
                conversationId: activeConversationId,
                messageBody,
                clientMessageId: crypto.randomUUID()
            };
        }

        const submission = pendingSend;

        isSendingMessage = true;
        updateMessengerComposer();

        const response = await proDriverRequest('/messenger-send-message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': driverToken
            },
            body: JSON.stringify({
                conversation_id: submission.conversationId,
                client_message_id: submission.clientMessageId,
                message_body: submission.messageBody
            })
        });

        const savedMessage = response?.messageData;

        if (response?.status !== 'success' || !savedMessage || !Number.isSafeInteger(savedMessage.message_id) || savedMessage.message_id < 1 || Number(savedMessage.conversation_id) !== submission.conversationId || savedMessage.client_message_id !== submission.clientMessageId || typeof savedMessage.message_body !== 'string') {
            throw new Error(response?.message || 'The message could not be sent.');
        }

        const alreadyDisplayed = conversationMessages.some(message => message.message_id === savedMessage.message_id);

        if (!alreadyDisplayed) {
            conversationMessages.push({
                ...savedMessage,
                is_own: true
            });

            conversationMessages.sort((a, b) => a.message_id - b.message_id);
        }

        renderMessengerMessages(conversationMessages, activeContact);

        if (messageInput) {
            messageInput.value = '';
        }

        pendingSend = null;

        if (messageArea) {
            messageArea.scrollTop = messageArea.scrollHeight;
        }

    } catch (error) {
        console.error('[MESSENGER] Failed sending message:', error);

        if (conversationError) {
            conversationError.textContent = error?.message || 'The message could not be sent. Please retry.';
            showElement(conversationError);
        }
        // Keep the draft and UUID for an unchanged retry.
    } finally {
        isSendingMessage = false;
        updateMessengerComposer();
    }
};

document.addEventListener('visibilitychange', () => {
    stopMessengerPolling();

    if (!document.hidden) {
        messagePollFailures = 0;
        scheduleMessengerPoll(0);
    }
});

window.addEventListener('offline', () => {
    stopMessengerPolling();
});

window.addEventListener('online', () => {
    messagePollFailures = 0;
    scheduleMessengerPoll(0);
});

messageForm?.addEventListener('submit', sendMessengerMessage);

messageInput?.addEventListener('input', updateMessengerComposer);

contactSearch?.addEventListener('input', filterDriverContacts);

updateMessengerComposer();
loadMessengerContacts();
