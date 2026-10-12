import { proDriverRequest, viewableDateTimeHelper } from './helpers.js';

const MESSAGE_POLL_INTERVAL = 3000;
const contactsLoading = document.querySelector('#messenger-contacts-loading');
const contactsError = document.querySelector('#messenger-contacts-error');
const conversationsEmpty = document.querySelector('#messenger-conversations-empty');
const dispatchConversations = document.querySelector('#messenger-dispatch-conversations');
const driverConversations = document.querySelector('#messenger-driver-conversations');
const conversationSearch = document.querySelector('#messenger-conversation-search');
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
const confirmedReadPositions = new Map();
const readRequestsInFlight = new Set();
const newMessageButton = document.querySelector('#messenger-new-message');
const contactPicker = document.querySelector('#messenger-contact-picker');
const pickerClose = document.querySelector('#messenger-picker-close');
const pickerSearch = document.querySelector('#messenger-picker-search');
const pickerContacts = document.querySelector('#messenger-picker-contacts');
const pickerEmpty = document.querySelector('#messenger-picker-empty');

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
let unreadCountsByRecipient = new Map();
let conversationSummariesByRecipient = new Map();
let unreadPollTimer = null;
let unreadRequestInFlight = false;
let unreadRefreshRequested = false;
let unreadPollFailures = 0;
let unreadStateVersion = 0;
let readCheckTimer = null;

function hideElement(element) {
    element?.classList.add('d-none');
};

function showElement(element) {
    element?.classList.remove('d-none');
};

function createConversationButton(contact) {
    const button = document.createElement('button');

    button.type = 'button';
    button.className = 'list-group-item list-group-item-action';
    button.classList.toggle('dispatch-thread', contact.role === 'dispatch');
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

    const badge = document.createElement('span');
    badge.className = 'badge rounded-pill text-bg-danger d-none';
    badge.dataset.unreadBadge = 'true';

    const count = unreadCountsByRecipient.get(Number(contact.userId)) ?? 0;

    badge.textContent = String(count);
    badge.setAttribute('aria-label', `${count} unread messages`);
    badge.classList.toggle('d-none', count === 0);

    const indicators = document.createElement('div');
    indicators.className = 'd-flex align-items-center gap-2 flex-shrink-0';
    indicators.append(badge, icon);

    layout.append(contactDetails, indicators);
    button.appendChild(layout);

    const selectedRecipientId = openingRecipientId ?? activeContact?.userId;
    const isSelected = String(contact.userId) === String(selectedRecipientId);

    button.classList.toggle('active', isSelected);
    button.setAttribute('aria-pressed', String(isSelected));

    button.addEventListener('click', () => {
        openMessengerConversation(contact);
    });

    description.dataset.conversationPreview = 'true';

    const timestamp = document.createElement('small');
    timestamp.className = 'text-body-secondary d-block mt-1 d-none';
    timestamp.dataset.conversationTimestamp = 'true';

    contactDetails.appendChild(timestamp);

    updateContactPreview(button);

    return button;
};

function renderConversations(contacts) {
    const dispatchFragment = document.createDocumentFragment();
    const driverFragment = document.createDocumentFragment();

    contacts.forEach(contact => {
        const contactButton = createConversationButton(contact);

        if (contact.role === 'dispatch') {
            dispatchFragment.appendChild(contactButton);
            return;
        }

        if (contact.role === 'driver') {
            driverFragment.appendChild(contactButton);
        }
    });

    dispatchConversations?.replaceChildren(dispatchFragment);
    driverConversations?.replaceChildren(driverFragment);

    const visibleDriverConversationCount = driverConversations?.children.length ?? 0;

    if (visibleDriverConversationCount === 0) {
        showElement(conversationsEmpty);
    } else {
        hideElement(conversationsEmpty);
    }

    updateConversationRows();
};

function refreshConversationSidebar(forceRender = false) {
    const searchValue = conversationSearch?.value.trim().toLowerCase() ?? '';

    const visibleConversationContacts = messengerContacts.filter(contact => {
        const recipientId = Number(contact.userId);
        const summary = conversationSummariesByRecipient.get(recipientId);
        const isSelected = recipientId === Number(openingRecipientId ?? activeContact?.userId);
        const hasMessages = summary?.latest_message_id != null;

        if (!hasMessages && !isSelected) {
            return false;
        }

        return contact.role === 'dispatch' || contact.displayName.toLowerCase().includes(searchValue);
    });

    const existingButtons = [
        ...(dispatchConversations?.querySelectorAll('button[data-recipient-id]') ?? []),
        ...(driverConversations?.querySelectorAll('button[data-recipient-id]') ?? [])
    ];

    const existingIds = new Set(existingButtons.map(button => Number(button.dataset.recipientId)));

    const membershipChanged = existingIds.size !== visibleConversationContacts.length || visibleConversationContacts.some(contact => !existingIds.has(Number(contact.userId)));

    if (forceRender || membershipChanged) {
        renderConversations(visibleConversationContacts);
    } else {
        updateConversationRows();
    }
};

function filterDriverConversations() {
    refreshConversationSidebar(true);
};

function showContactsError(message) {
    hideElement(contactsLoading);

    if (contactsError) {
        contactsError.textContent = message || 'Messenger contacts could not be loaded.';
        showElement(contactsError);
    }
};

function renderContactPicker() {
    const searchValue = pickerSearch?.value.trim().toLowerCase() ?? '';
    const fragment = document.createDocumentFragment();
    const contacts = messengerContacts.filter(contact => contact.displayName.toLowerCase().includes(searchValue));

    contacts.forEach(contact => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'list-group-item list-group-item-action';

        const name = document.createElement('div');
        name.className = 'fw-semibold';
        name.textContent = contact.displayName;

        const role = document.createElement('small');
        role.className = 'text-body-secondary';
        role.textContent = contact.role === 'dispatch' ? 'Dispatch' : 'Driver';

        button.append(name, role);

        button.addEventListener('click', async () => {
            if (isSendingMessage) {
                return;
            }

            // Clear the sidebar filter so the selected contact is visible.
            if (conversationSearch) {
                conversationSearch.value = '';
            }

            refreshConversationSidebar(true);
            contactPicker.close();

            await openMessengerConversation(contact);

            if (activeContact?.userId === contact.userId && !messageInput?.disabled) {
                messageInput?.focus();
            }
        });

        fragment.appendChild(button);
    });

    pickerContacts?.replaceChildren(fragment);
    pickerEmpty?.classList.toggle('d-none', contacts.length !== 0);
};

function openContactPicker() {
    if (!contactPicker || isSendingMessage) {
        return;
    }

    if (pickerSearch) {
        pickerSearch.value = '';
    }

    renderContactPicker();

    if (!contactPicker.open) {
        contactPicker.showModal();
    }

    pickerSearch?.focus();
};

async function loadMessengerContacts() {
    hideElement(contactsError);
    hideElement(conversationsEmpty);
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
        refreshConversationSidebar(true);
        updateSectionUnreadTotals();
        hideElement(contactsLoading);

        if (newMessageButton) {
            newMessageButton.disabled = isSendingMessage;
        }
    } catch (error) {
        console.error('[MESSENGER] Failed loading contacts:', error);
        showContactsError(error?.message || 'Messenger contacts could not be loaded.');
    }
};

function updateSelectedContact() {
    const selectedRecipientId = openingRecipientId ?? activeContact?.userId;

    const buttons = [
        ...(dispatchConversations?.querySelectorAll('button[data-recipient-id]') ?? []),
        ...(driverConversations?.querySelectorAll('button[data-recipient-id]') ?? [])
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

    if (newMessageButton) {
        newMessageButton.disabled = !messengerContacts.length || isSendingMessage;
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
        scheduleReadCheck();

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

function updateUnreadBadges() {
    const buttons = [
        ...(dispatchConversations?.querySelectorAll('button[data-recipient-id]') ?? []),
        ...(driverConversations?.querySelectorAll('button[data-recipient-id]') ?? [])
    ];

    buttons.forEach(button => {
        const badge = button.querySelector('[data-unread-badge]');
        const count = unreadCountsByRecipient.get(
            Number(button.dataset.recipientId)
        ) ?? 0;

        if (badge) {
            badge.textContent = String(count);
            badge.setAttribute('aria-label', `${count} unread messages`);
            badge.classList.toggle('d-none', count === 0);
        }
    });
    updateSectionUnreadTotals();
};

function stopUnreadPolling() {
    if (unreadPollTimer !== null) {
        clearTimeout(unreadPollTimer);
        unreadPollTimer = null;
    }
};

function scheduleUnreadPoll(delay = MESSAGE_POLL_INTERVAL) {
    stopUnreadPolling();

    if (document.hidden || !navigator.onLine) {
        return;
    }

    unreadPollTimer = setTimeout(() => {
        unreadPollTimer = null;
        refreshMessengerUnread();
    }, delay);
};

async function refreshMessengerUnread() {
    if (document.hidden || !navigator.onLine) {
        return;
    }

    if (unreadRequestInFlight) {
        unreadRefreshRequested = true;
        return;
    }

    stopUnreadPolling();
    unreadRequestInFlight = true;
    unreadRefreshRequested = false;

    const stateVersion = unreadStateVersion;

    try {
        const response = await proDriverRequest('/messenger-unread', {
            method: 'GET',
            cache: 'no-store',
            headers: {
                'X-CSRF-Token': driverToken
            }
        });

        if (response?.status !== 'success' || !Array.isArray(response.unreadData?.conversations)) {
            throw new Error(response?.message || 'Unread counts could not be loaded.');
        }

        // A read update completed while this summary was loading.
        if (stateVersion !== unreadStateVersion) {
            unreadRefreshRequested = true;
            return;
        }

        const counts = new Map();

        response.unreadData.conversations.forEach(summary => {
            if (summary.recipient_id !== null) {
                const recipientId = Number(summary.recipient_id);
                counts.set(recipientId, (counts.get(recipientId) ?? 0) + Number(summary.unread_count));
            }
        });

        unreadCountsByRecipient = counts;
        const summaries = new Map();

        response.unreadData.conversations.forEach(summary => {
            if (summary.conversation_type === 'direct' && summary.recipient_id !== null) {
                summaries.set(Number(summary.recipient_id), summary);
            }
        });

        conversationSummariesByRecipient = summaries;
        refreshConversationSidebar();
        unreadPollFailures = 0;
        updateUnreadBadges();
    } catch (error) {
        unreadPollFailures++;
        console.error('[MESSENGER] Unread refresh failed:', error);
    } finally {
        unreadRequestInFlight = false;
        const delay = unreadRefreshRequested ? 0 : Math.min(MESSAGE_POLL_INTERVAL * (2 ** Math.min(unreadPollFailures, 4)), 30000);

        scheduleUnreadPoll(delay);
    }
};

function newestRetrievedMessagesAreVisible() {
    if (document.hidden || !navigator.onLine || openingRecipientId !== null || activeConversationId === null || latestRetrievedMessageId < 1 || !messageArea) {
        return false;
    }

    const distanceFromBottom = messageArea.scrollHeight - messageArea.scrollTop - messageArea.clientHeight;
    if (distanceFromBottom > 2) {
        return false;
    }

    const lastMessage = messageList?.lastElementChild;
    if (!lastMessage) {
        return false;
    }

    // Also account for the whole page being scrolled on mobile.
    const bounds = lastMessage.getBoundingClientRect();
    return (bounds.bottom > 0 && bounds.bottom <= window.innerHeight && bounds.right > 0 && bounds.left < window.innerWidth);
};

function scheduleReadCheck() {
    if (readCheckTimer !== null) {
        clearTimeout(readCheckTimer);
    }

    readCheckTimer = setTimeout(() => {
        readCheckTimer = null;
        markVisibleMessagesRead();
    }, 200);
};

async function markVisibleMessagesRead() {
    if (!newestRetrievedMessagesAreVisible()) {
        return;
    }

    const conversationId = activeConversationId;

    // Use the retrieval cursor, never a higher sent-message ID.
    const messageId = latestRetrievedMessageId;
    const confirmedPosition = confirmedReadPositions.get(conversationId) ?? 0;

    if (messageId <= confirmedPosition || readRequestsInFlight.has(conversationId)) {
        return;
    }

    readRequestsInFlight.add(conversationId);
    let savedSuccessfully = false;

    try {
        const response = await proDriverRequest('/messenger-mark-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': driverToken
            },
            body: JSON.stringify({
                conversation_id: conversationId,
                message_id: messageId
            })
        });

        const readData = response?.readData;
        const savedPosition = Number(readData?.last_read_message_id);

        if (response?.status !== 'success' || Number(readData?.conversation_id) !== conversationId || !Number.isSafeInteger(savedPosition) || savedPosition < messageId) {
            throw new Error(response?.message || 'The read position could not be saved.');
        }

        confirmedReadPositions.set(conversationId, Math.max(confirmedReadPositions.get(conversationId) ?? 0, savedPosition));
        savedSuccessfully = true;
        unreadStateVersion++;

        // Fetch authoritative counts rather than assuming zero.
        refreshMessengerUnread();
    } catch (error) {
        console.error('[MESSENGER] Read update failed:', error);
    } finally {
        readRequestsInFlight.delete(conversationId);
        if (savedSuccessfully && activeConversationId === conversationId && latestRetrievedMessageId > messageId) {
            scheduleReadCheck();
        }
    }
};

function updateContactPreview(button) {
    const recipientId = Number(button.dataset.recipientId);
    const summary = conversationSummariesByRecipient.get(recipientId);

    const preview = button.querySelector('[data-conversation-preview]');
    const timestamp = button.querySelector('[data-conversation-timestamp]');

    const hasMessage = summary?.latest_message_id != null;

    if (preview) {
        preview.textContent = hasMessage ? `${summary.latest_is_own ? 'You: ' : ''}${String(summary.latest_message_body ?? '').replace(/\s+/g, ' ').trim()}` : button.dataset.contactRole === 'dispatch' ? 'Dispatch communications' : 'Start or open conversation';
    }

    if (timestamp) {
        timestamp.textContent = hasMessage ? dtHelper(summary.latest_message_created_at, 'datetime') : '';
        timestamp.classList.toggle('d-none', !hasMessage);
    }
};

function updateConversationRows() {
    [dispatchConversations, driverConversations].forEach(list => {
        if (!list) {
            return;
        }

        const buttons = Array.from(list.querySelectorAll('button[data-recipient-id]'));
        buttons.forEach(updateContactPreview);

        const sortedButtons = [...buttons].sort((a, b) => {
            const aSummary = conversationSummariesByRecipient.get(Number(a.dataset.recipientId));
            const bSummary = conversationSummariesByRecipient.get(Number(b.dataset.recipientId));
            const aHasMessage = aSummary?.latest_message_id != null;
            const bHasMessage = bSummary?.latest_message_id != null;

            if (aHasMessage !== bHasMessage) {
                return aHasMessage ? -1 : 1;
            }

            if (aHasMessage && bHasMessage) {
                const timestampOrder = String(bSummary.latest_message_created_at).localeCompare(String(aSummary.latest_message_created_at));

                if (timestampOrder !== 0) {
                    return timestampOrder;
                }

                const messageOrder = Number(bSummary.latest_message_id) - Number(aSummary.latest_message_id);

                if (messageOrder !== 0) {
                    return messageOrder;
                }
            }

            // Contacts without messages remain alphabetically ordered.
            const aName = messengerContacts.find(contact => Number(contact.userId) === Number(a.dataset.recipientId))?.displayName ?? '';

            const bName = messengerContacts.find(contact => Number(contact.userId) === Number(b.dataset.recipientId))?.displayName ?? '';

            return aName.localeCompare(bName, 'en', {
                sensitivity: 'base',
                numeric: true
            }) || Number(a.dataset.recipientId) - Number(b.dataset.recipientId);
        });

        const orderChanged = sortedButtons.some((button, index) => button !== buttons[index]);

        if (orderChanged) {
            const previousScrollTop = list.scrollTop;
            const focusedElement = document.activeElement;
            const restoreFocus = list.contains(focusedElement);

            // Move existing buttons, preserving handlers and active styling.
            sortedButtons.forEach(button => list.appendChild(button));

            if (restoreFocus) {
                focusedElement.focus({ preventScroll: true });
            }

            list.scrollTop = previousScrollTop;
        }
    });
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
    refreshConversationSidebar();

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
            refreshConversationSidebar();
            updateSelectedContact();
            updateMessengerComposer();
            scheduleMessengerPoll();
            scheduleReadCheck();
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
        refreshMessengerUnread(); 

        if (messageArea) {
            messageArea.scrollTop = messageArea.scrollHeight;
        }

        scheduleReadCheck();
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

function initializeMessengerSections() {
    const sidebar = document.querySelector('.messenger-sidebar[data-messenger-user-id]');
    const userId = sidebar?.dataset.messengerUserId;

    if (!sidebar || !userId) {
        return;
    }

    sidebar.querySelectorAll('details[data-messenger-section]').forEach(section => {
        const storageKey = `messenger:${userId}:section:${section.dataset.messengerSection}`;

        try {
            const savedState = localStorage.getItem(storageKey);

            if (savedState !== null) {
                section.open = savedState === 'open';
            }
        } catch (error) {
            console.warn('[MESSENGER] Section preference unavailable:', error);
        }

        section.addEventListener('toggle', () => {
            try {
                localStorage.setItem(storageKey, section.open ? 'open' : 'closed');
            } catch (error) {
                console.warn('[MESSENGER] Section preference not saved:', error);
            }

            // Collapsing the sidebar can bring the chat into view.
            scheduleReadCheck();
        });
    });
};

function updateSectionUnreadTotals() {
    const totals = {
        driver: 0,
        dispatch: 0
    };

    messengerContacts.forEach(contact => {
        if (Object.hasOwn(totals, contact.role)) {
            totals[contact.role] += unreadCountsByRecipient.get(Number(contact.userId)) ?? 0;
        }
    });

    const badges = [
        [document.querySelector('#messenger-driver-unread-total'), totals.driver],
        [document.querySelector('#messenger-dispatch-unread-total'), totals.dispatch]
    ];

    badges.forEach(([badge, count]) => {
        if (!badge) {
            return;
        }

        badge.textContent = String(count);
        badge.setAttribute('aria-label', `${count} unread messages`);
        badge.classList.toggle('d-none', count === 0);
    });
};

newMessageButton?.addEventListener('click', openContactPicker);

pickerClose?.addEventListener('click', () => {
    contactPicker?.close();
});

pickerSearch?.addEventListener('input', renderContactPicker);

document.addEventListener('visibilitychange', () => {
    stopMessengerPolling();
    stopUnreadPolling();

    if (!document.hidden) {
        messagePollFailures = 0;
        unreadPollFailures = 0;

        scheduleMessengerPoll(0);
        scheduleUnreadPoll(0);
        scheduleReadCheck();
    }
});

window.addEventListener('offline', () => {
    stopMessengerPolling();
    stopUnreadPolling();
});

window.addEventListener('online', () => {
    messagePollFailures = 0;
    unreadPollFailures = 0;

    scheduleMessengerPoll(0);
    scheduleUnreadPoll(0);
    scheduleReadCheck();
});

messageArea?.addEventListener('scroll', scheduleReadCheck, {
    passive: true
});

window.addEventListener('scroll', scheduleReadCheck, {
    passive: true
});

window.addEventListener('resize', scheduleReadCheck);

messageForm?.addEventListener('submit', sendMessengerMessage);

messageInput?.addEventListener('input', updateMessengerComposer);

conversationSearch?.addEventListener('input', filterDriverConversations);

updateMessengerComposer();
initializeMessengerSections();
loadMessengerContacts();
scheduleUnreadPoll(0);
