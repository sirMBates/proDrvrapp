import { proDriverRequest, viewableDateTimeHelper } from '../shared/helpers.js';
import { buildModal } from '../shared/appmodal.js';
import { Validation } from '../validation/validation.js';

const MESSAGE_POLL_INTERVAL = 3000;
const contactsLoading = document.querySelector('#messenger-contacts-loading');
const contactsError = document.querySelector('#messenger-contacts-error');
const conversationsEmpty = document.querySelector('#messenger-conversations-empty');
const groupsEmpty = document.querySelector('#messenger-groups-empty');
const adminConversations = document.querySelector('#messenger-admin-conversations');
const dispatchConversations = document.querySelector('#messenger-dispatch-conversations');
const groupConversations = document.querySelector('#messenger-group-conversations');
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
const olderControls = document.querySelector('#messenger-older-controls');
const loadOlderButton = document.querySelector('#messenger-load-older');
const olderError = document.querySelector('#messenger-older-error');
const deleteConversationButton = document.querySelector('#messenger-delete-conversation');
const selectConversationsButton = document.querySelector('#messenger-select-conversations');
const bulkControls = document.querySelector('#messenger-bulk-controls');
const selectionCount = document.querySelector('#messenger-selection-count');
const deleteSelectedButton = document.querySelector('#messenger-delete-selected');
const selectionCancelButton = document.querySelector('#messenger-selection-cancel');
const deleteError = document.querySelector('#messenger-delete-error');
const viewMembersButton = document.querySelector('#messenger-view-members');
const groupMembersDialog = document.querySelector('#messenger-group-members');
const groupMembersTitle = document.querySelector('#messenger-group-members-title');
const groupMembersClose = document.querySelector('#messenger-group-members-close');
const groupMembersLoading = document.querySelector('#messenger-group-members-loading');
const groupMembersError = document.querySelector('#messenger-group-members-error');
const groupMembersList = document.querySelector('#messenger-group-members-list');
const newGroupButton = document.querySelector('#messenger-new-group');
const groupPicker = document.querySelector('#messenger-group-picker');
const groupForm = document.querySelector('#messenger-group-form');
const groupPickerClose = document.querySelector('#messenger-group-picker-close');
const groupCreateCancel = document.querySelector('#messenger-group-create-cancel');
const groupTitleInput = document.querySelector('#messenger-group-title');
const groupSearch = document.querySelector('#messenger-group-search');
const groupPickerContacts = document.querySelector('#messenger-group-picker-contacts');
const groupPickerEmpty = document.querySelector('#messenger-group-picker-empty');
const groupSelectionCount = document.querySelector('#messenger-group-selection-count');
const groupContactFields = document.querySelector('#messenger-group-contact-fields');
const groupCreateError = document.querySelector('#messenger-group-create-error');
const groupCreateSubmit = document.querySelector('#messenger-group-create-submit');
const selectedGroupRecipientIds = new Set();
// Store conversation IDs for both direct chats and groups.
const selectedConversationIds = new Set();
const confirmDeletion = buildModal;

let latestRetrievedMessageId = 0;
let messagePollTimer = null;
let messagePollInFlight = false;
let messagePollFailures = 0;
let conversationMessages = [];
let isSelectingConversations = false;
let isSendingMessage = false;
let pendingSend = null;
let activeConversationId = null;
let activeContact = null;
let openingConversationKey = null;
let conversationRequestVersion = 0;
let messengerContacts = [];
let unreadCountsByRecipient = new Map();
let conversationSummariesByRecipient = new Map();
let conversationSummariesById = new Map();
let unreadPollTimer = null;
let unreadRequestInFlight = false;
let unreadRefreshRequested = false;
let unreadPollFailures = 0;
let unreadStateVersion = 0;
let readCheckTimer = null;
let oldestLoadedMessageId = null;
let hasOlderMessages = false;
let isLoadingOlderMessages = false;
let isDeletingConversations = false;
let notificationDestinationOpened = false;
let groupMembersRequestVersion = 0;
let isCreatingGroup = false;

function hideElement(element) {
    element?.classList.add('d-none');
};

function showElement(element) {
    element?.classList.remove('d-none');
};

function getConversationLists() {
    return [
        dispatchConversations,
        adminConversations,
        groupConversations,
        driverConversations
    ].filter(Boolean);
};

function getConversationButtons() {
    return getConversationLists().flatMap(list => Array.from(list.querySelectorAll('button[data-recipient-id], button[data-conversation-id]')));
};

function getConversationContact(recipientId) {
    const id = Number(recipientId);

    return conversationSummariesByRecipient.get(id)?.recipient ?? messengerContacts.find(contact => Number(contact.userId) === id) ?? (Number(activeContact?.userId) === id ? activeContact : null);
};

function getContactConversationId(contact) {
    const conversationId = contact?.conversationId ?? conversationSummariesByRecipient.get(Number(contact?.userId)) ?.conversation_id ?? (activeContact?.role !== 'group' && Number(activeContact?.userId) === Number(contact?.userId) ? activeConversationId : null);

    const id = Number(conversationId);

    return Number.isSafeInteger(id) && id > 0 ? id : null;
};

function getButtonConversationId(button) {
    const storedId = Number(button.dataset.conversationId);

    if (Number.isSafeInteger(storedId) && storedId > 0) {
        return storedId;
    }

    return getContactConversationId({
        userId: button.dataset.recipientId
    });
};

function getButtonConversationSummary(button) {
    const conversationId = getButtonConversationId(button);

    return conversationSummariesById.get(conversationId) ??
        conversationSummariesByRecipient.get(
            Number(button.dataset.recipientId)
        );
};

function getMessengerConversationDescription(contact) {
    if (contact.role === 'group') {
        return `${contact.memberCount} members`;
    }

    if (contact.role === 'admin') {
        return 'Administration conversation';
    }

    return contact.role === 'dispatch' ? 'Dispatch communications' : 'Driver conversation';
};

function createConversationButton(contact) {
    const isGroup = contact.role === 'group';
    const conversationId = getContactConversationId(contact);

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'list-group-item list-group-item-action';
    button.classList.toggle('dispatch-thread', contact.role === 'dispatch');

    button.dataset.contactRole = contact.role;
    button.dataset.displayName = contact.displayName;

    if (isGroup) {
        button.dataset.conversationId = String(conversationId);
    } else {
        button.dataset.recipientId = String(contact.userId);
    }

    const layout = document.createElement('div');
    layout.className = 'd-flex justify-content-between align-items-center';

    const contactDetails = document.createElement('div');
    contactDetails.className = 'me-2 overflow-hidden';

    const displayName = document.createElement('div');
    displayName.className = contact.role === 'driver' ? 'fw-semibold' : 'fw-bold';
    displayName.dataset.conversationName = 'true';
    displayName.textContent = contact.displayName;

    const description = document.createElement('small');
    description.className = 'text-body-secondary d-block text-truncate';
    description.dataset.conversationPreview = 'true';

    const timestamp = document.createElement('small');
    timestamp.className = 'text-body-secondary d-block mt-1 d-none';
    timestamp.dataset.conversationTimestamp = 'true';

    contactDetails.append(displayName, description, timestamp);

    const selectionMarker = document.createElement('span');
    selectionMarker.className = 'badge text-bg-primary d-none';
    selectionMarker.dataset.selectionMarker = 'true';
    selectionMarker.textContent = 'Selected';

    const badge = document.createElement('span');
    badge.className = 'badge rounded-pill text-bg-danger d-none';
    badge.dataset.unreadBadge = 'true';

    const icon = document.createElement('i');
    icon.className = 'fa-solid fa-chevron-right text-body-secondary';
    icon.setAttribute('aria-hidden', 'true');

    const indicators = document.createElement('div');
    indicators.className = 'd-flex align-items-center gap-2 flex-shrink-0';
    indicators.append(selectionMarker, badge, icon);

    layout.append(contactDetails, indicators);
    button.appendChild(layout);

    button.addEventListener('click', () => {
        // Resolve groups again so their current title/member count is used.
        const currentSummary = isGroup ? conversationSummariesById.get(conversationId) : null;

        const target = isGroup && currentSummary ? groupSummaryToContact(currentSummary) : contact;

        if (isSelectingConversations) {
            toggleConversationSelection(target);
            return;
        }

        if (isGroup) {
            openMessengerGroupConversation(target);
        } else {
            openMessengerConversation(target);
        }
    });

    updateContactPreview(button);

    return button;
};

function renderConversations(contacts) {
    const dispatchFragment = document.createDocumentFragment();
    const adminFragment = document.createDocumentFragment();
    const driverFragment = document.createDocumentFragment();

    contacts.forEach(contact => {
        const button = createConversationButton(contact);

        if (contact.role === 'admin') {
            adminFragment.appendChild(button);
        } else if (contact.role === 'dispatch') {
            dispatchFragment.appendChild(button);
        } else if (contact.role === 'driver') {
            driverFragment.appendChild(button);
        }
    });

    adminConversations?.replaceChildren(adminFragment);
    dispatchConversations?.replaceChildren(dispatchFragment);
    driverConversations?.replaceChildren(driverFragment);

    conversationsEmpty?.classList.toggle('d-none', (driverConversations?.children.length ?? 0) !== 0);

    updateConversationRows();
    updateConversationSelectionControls();
};

function groupSummaryToContact(summary) {
    return {
        conversationId: Number(summary.conversation_id),
        displayName: summary.display_name || summary.title || 'Group conversation',
        memberCount: Number(summary.member_count),
        role: 'group'
    };
};

function renderGroupConversations() {
    if (!groupConversations) {
        return;
    }

    const summaries = Array.from(conversationSummariesById.values()).filter(summary => summary.conversation_type === 'group');
    const wantedIds = new Set(summaries.map(summary => Number(summary.conversation_id)));

    const existingButtons = new Map(Array.from(groupConversations.querySelectorAll('button[data-conversation-id]')).map(button => [
            Number(button.dataset.conversationId),
            button
        ])
    );

    existingButtons.forEach((button, conversationId) => {
        if (!wantedIds.has(conversationId)) {
            button.remove();
        }
    });

    summaries.forEach(summary => {
        const conversationId = Number(summary.conversation_id);
        const contact = groupSummaryToContact(summary);

        let button = existingButtons.get(conversationId);

        if (!button) {
            button = createConversationButton(contact);
            groupConversations.appendChild(button);
        }

        button.dataset.displayName = contact.displayName;

        const name = button.querySelector('[data-conversation-name]');

        if (name) {
            name.textContent = contact.displayName;
        }

        updateContactPreview(button);
    });

    groupsEmpty?.classList.toggle('d-none', summaries.length !== 0);

    updateConversationRows();
    updateUnreadBadges();
    updateConversationSelectionControls();
};

function refreshConversationSidebar(forceRender = false) {
    const searchValue = conversationSearch?.value.trim().toLowerCase() ?? '';
    const contactsById = new Map();

    conversationSummariesByRecipient.forEach(summary => {
        if (summary.recipient) {
            contactsById.set(Number(summary.recipient.userId), summary.recipient);
        }
    });

    // Retain a newly selected empty driver/dispatch conversation.
    const selectedId = openingConversationKey ?? activeContact?.userId;
    const selectedContact = getConversationContact(selectedId);

    if (selectedContact) {
        contactsById.set(Number(selectedContact.userId), selectedContact);
    }

    const visibleConversationContacts = Array.from(contactsById.values()).filter(contact => {
        const recipientId = Number(contact.userId);
        const summary = conversationSummariesByRecipient.get(recipientId);

        const isSelected = recipientId === Number(selectedId);
        const hasMessages = summary?.latest_message_id != null;

        if (!hasMessages && !isSelected) {
            return false;
        }

        // The existing search is specifically for Driver Conversations.
        return contact.role !== 'driver' || contact.displayName.toLowerCase().includes(searchValue);
    });

    const existingButtons = getConversationButtons().filter(button => button.hasAttribute('data-recipient-id'));
    const existingIds = new Set(existingButtons.map(button => Number(button.dataset.recipientId)));

    const membershipChanged = existingIds.size !== visibleConversationContacts.length || visibleConversationContacts.some(contact => !existingIds.has(Number(contact.userId)));

    if (forceRender || membershipChanged) {
        renderConversations(visibleConversationContacts);
    } else {
        updateConversationRows();
    }

    renderGroupConversations();
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
            if (isSendingMessage || isDeletingConversations) {
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
    if (!contactPicker || isSendingMessage || isDeletingConversations) {
        return;
    }

    if (pickerSearch) {
        pickerSearch.value = '';
    }

    finishConversationSelection();
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

        updateMessengerComposer();

        await openRequestedMessengerConversation();
    } catch (error) {
        console.error('[MESSENGER] Failed loading contacts:', error);
        showContactsError(error?.message || 'Messenger contacts could not be loaded.');
    }
};

async function openRequestedMessengerConversation() {
    if (notificationDestinationOpened || isSendingMessage || isDeletingConversations || openingConversationKey !== null) {
        return;
    }

    const params = new URLSearchParams(window.location.search);
    const requestedConversation = params.get('conversation');

    if (requestedConversation !== null) {
        const conversationId = Number(requestedConversation);
        if (!Number.isSafeInteger(conversationId) || conversationId < 1) {
            return;
        }
        /*
         * Wait until the authenticated summaries include this
         * conversation. The unread refresh calls this function again.
         */
        const summary = conversationSummariesById.get(conversationId);
        if (!summary) {
            return;
        }

        if (summary.conversation_type === 'group') {
            notificationDestinationOpened = true;
            await openMessengerGroupConversation(groupSummaryToContact(summary));
        } else if (summary.conversation_type === 'direct' && summary.recipient) {
            notificationDestinationOpened = true;
            await openMessengerConversation(summary.recipient);
        }

        return;
    }

    // Support existing direct-message notification links.
    const requestedRecipient = params.get('recipient');
    if (requestedRecipient === null) {
        return;
    }

    const recipientId = Number(requestedRecipient);
    if (!Number.isSafeInteger(recipientId) || recipientId < 1) {
        return;
    }

    const contact = getConversationContact(recipientId);
    if (!contact) {
        return;
    }

    notificationDestinationOpened = true;
    await openMessengerConversation(contact);
};

function updateSelectedContact() {
    const selectedRecipientId = openingConversationKey ?? activeContact?.userId;

    const buttons = getConversationButtons();

    buttons.forEach(button => {
        const isSelected = button.dataset.recipientId === String(selectedRecipientId);

        button.classList.toggle('active', isSelected);
        button.setAttribute('aria-pressed', String(isSelected));
    });
    updateConversationSelectionControls();
};

function renderMessengerMessages(messages, contact) {
    const fragment = document.createDocumentFragment();

    messages.forEach(message => {
        const row = document.createElement('div');
        row.dataset.messageId = String(message.message_id);
        row.className = message.is_own ? 'message-row message-row-sent mb-3 text-end' : 'message-row message-row-received mb-3';

        const meta = document.createElement('div');
        meta.className = 'message-meta small text-body-secondary mb-1';

        const senderName = message.is_own ? 'You' : message.sender?.displayName ?? (contact.role === 'group' ? `Member ${message.sender_id}` : contact.displayName);
        meta.textContent = `${senderName} · ${dtHelper(message.created_at, 'datetime')}`;

        const bubble = document.createElement('div');
        const senderRole = message.sender?.role ?? contact.role;
        bubble.className = message.is_own ? 'message-bubble sent-bubble d-inline-block' : senderRole === 'dispatch' ? 'message-bubble dispatch-bubble' : 'message-bubble received-bubble';

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

    oldestLoadedMessageId = conversationMessages.length > 0 ? conversationMessages[0].message_id : null;
    hasOlderMessages = messageData.has_older_messages === true;

    hideElement(olderError);
    updateOlderMessageControls();

    renderMessengerMessages(conversationMessages, contact);

    if (conversationDescription) {
        conversationDescription.textContent = getMessengerConversationDescription(contact);
    }

    if (messageArea) {
        messageArea.scrollTop = messageArea.scrollHeight;
    }
};

function updateMessengerComposer() {
    const canCompose = activeConversationId !== null && activeContact !== null && openingConversationKey === null && !isSendingMessage && !isLoadingOlderMessages && !isDeletingConversations;

    if (messageInput) {
        messageInput.disabled = !canCompose;
    }

    if (sendButton) {
        sendButton.disabled = !canCompose || !messageInput?.value.trim();
        sendButton.textContent = isSendingMessage ? 'Sending...' : 'Send';
    }

    if (newMessageButton) {
        newMessageButton.disabled = !messengerContacts.length || isSendingMessage || isDeletingConversations;
    }

    if (deleteConversationButton) {
        deleteConversationButton.disabled = activeConversationId === null || openingConversationKey !== null || isSendingMessage || isLoadingOlderMessages || isDeletingConversations;

        deleteConversationButton.textContent = isDeletingConversations ? 'Deleting...' : 'Delete conversation';
    }

    if (viewMembersButton) {
        const isGroup = activeContact?.role === 'group';
        viewMembersButton.classList.toggle('d-none', !isGroup);

        viewMembersButton.disabled = !isGroup || activeConversationId === null || openingConversationKey !== null || isDeletingConversations;
    }

    if (newGroupButton) {
        newGroupButton.disabled = messengerContacts.length < 2 || isSendingMessage || isDeletingConversations || isCreatingGroup || openingConversationKey !== null;
    }

    updateOlderMessageControls();
    updateConversationSelectionControls();
};

function stopMessengerPolling() {
    if (messagePollTimer !== null) {
        clearTimeout(messagePollTimer);
        messagePollTimer = null;
    }
};

function canPollMessenger() {
    return (!document.hidden && navigator.onLine && !isDeletingConversations && openingConversationKey === null && activeConversationId !== null && activeContact !== null);
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
            conversationDescription.textContent = getMessengerConversationDescription(contact);
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
    getConversationButtons().forEach(button => {
        const summary = getButtonConversationSummary(button);
        const count = Number(summary?.unread_count) || 0;
        const badge = button.querySelector('[data-unread-badge]');

        if (!badge) {
            return;
        }

        badge.textContent = String(count);
        badge.setAttribute('aria-label', `${count} unread messages`);
        badge.classList.toggle('d-none', count === 0);
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
        const summariesByRecipient = new Map();
        const summariesById = new Map();

        response.unreadData.conversations.forEach(summary => {
            const conversationId = Number(summary.conversation_id);

            if (!Number.isSafeInteger(conversationId) || conversationId < 1) {
                throw new Error('Invalid Messenger conversation ID.');
            }

            // Includes direct conversations and groups.
            summariesById.set(conversationId, summary);

            // Recipient-based lookups apply only to direct conversations.
            if (summary.conversation_type === 'direct' && summary.recipient_id != null) {
                const recipientId = Number(summary.recipient_id);

                if (!Number.isSafeInteger(recipientId) || recipientId < 1) {
                    throw new Error('Invalid Messenger recipient ID.');
                }

                summariesByRecipient.set(recipientId, summary);
                counts.set(recipientId, (counts.get(recipientId) ?? 0) + Number(summary.unread_count));
            }
        });

        unreadCountsByRecipient = counts;
        conversationSummariesByRecipient = summariesByRecipient;
        conversationSummariesById = summariesById;
        refreshConversationSidebar();
        unreadPollFailures = 0;
        updateUnreadBadges();
        await openRequestedMessengerConversation();
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
    if (document.hidden || !navigator.onLine || openingConversationKey !== null || activeConversationId === null || latestRetrievedMessageId < 1 || !messageArea) {
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
    if (isDeletingConversations || !newestRetrievedMessagesAreVisible()) {
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
    const summary = getButtonConversationSummary(button);
    const role = button.dataset.contactRole;

    const preview = button.querySelector('[data-conversation-preview]');
    const timestamp = button.querySelector('[data-conversation-timestamp]');
    const hasMessage = summary?.latest_message_id != null;

    if (preview) {
        if (hasMessage) {
            preview.textContent = `${summary.latest_is_own ? 'You: ' : ''}` + String(summary.latest_message_body ?? '').replace(/\s+/g, ' ').trim();
        } else {
            preview.textContent = role === 'group' ? `${Number(summary?.member_count) || 0} members` : role === 'admin' ? 'Administration conversation' : role === 'dispatch' ? 'Dispatch communications' : 'Start or open conversation';
        }
    }

    if (timestamp) {
        timestamp.textContent = hasMessage ? dtHelper(summary.latest_message_created_at, 'datetime') : '';
        timestamp.classList.toggle('d-none', !hasMessage);
    }
};

function updateConversationRows() {
    getConversationLists().forEach(list => {
        if (!list) {
            return;
        }

        const buttons = Array.from(list.querySelectorAll('button[data-recipient-id], button[data-conversation-id]'));
        buttons.forEach(updateContactPreview);

        const sortedButtons = [...buttons].sort((a, b) => {
            const aSummary = getButtonConversationSummary(a);
            const bSummary = getButtonConversationSummary(b);
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
            const aName = a.dataset.displayName ?? '';
            const bName = b.dataset.displayName ?? '';

            return aName.localeCompare(bName, 'en', {
                sensitivity: 'base',
                numeric: true
            }) || (getButtonConversationId(a) ?? 0) - (getButtonConversationId(b) ?? 0);
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

function updateOlderMessageControls() {
    olderControls?.classList.toggle('d-none', !hasOlderMessages);

    if (loadOlderButton) {
        loadOlderButton.disabled = isLoadingOlderMessages || isSendingMessage || isDeletingConversations || openingConversationKey !== null || activeConversationId === null;

        loadOlderButton.textContent = isLoadingOlderMessages ? 'Loading...' : 'Load older messages';
    }
};

async function loadOlderMessengerMessages() {
    if (isLoadingOlderMessages || isSendingMessage || !hasOlderMessages || isDeletingConversations || oldestLoadedMessageId === null || openingConversationKey !== null || activeConversationId === null || activeContact === null) {
        return;
    }

    const conversationId = activeConversationId;
    const contact = activeContact;
    const requestVersion = conversationRequestVersion;
    const beforeMessageId = oldestLoadedMessageId;

    isLoadingOlderMessages = true;
    hideElement(olderError);
    updateMessengerComposer();

    try {
        const response = await proDriverRequest(`/messenger-retrieve-messages?conversation_id=${encodeURIComponent(conversationId)}&before_message_id=${beforeMessageId}`, {
            method: 'GET',
            cache: 'no-store',
            headers: {
                'X-CSRF-Token': driverToken
            }
        });

        if (requestVersion !== conversationRequestVersion || conversationId !== activeConversationId) {
            return;
        }

        const messageData = response?.messageData;

        if (response?.status !== 'success' || Number(messageData?.conversation_id) !== conversationId || !Array.isArray(messageData?.messages) || typeof messageData.has_older_messages !== 'boolean' || messageData.messages.some(message => !Number.isSafeInteger(message.message_id) || message.message_id < 1 || message.message_id >= beforeMessageId)) {
            throw new Error(response?.message || 'Older messages could not be loaded.');
        }

        /*
         * Capture the current reading position after the request finishes,
         * so scrolling during the request is respected.
         */
        const areaBounds = messageArea?.getBoundingClientRect();
        const anchor = areaBounds ? Array.from(messageList?.children ?? []).find(row => row.getBoundingClientRect().bottom > areaBounds.top) : null;

        const anchorId = anchor?.dataset.messageId;
        const anchorTop = anchor?.getBoundingClientRect().top;
        const previousScrollTop = messageArea?.scrollTop ?? 0;
        const knownIds = new Set(conversationMessages.map(message => message.message_id));
        const olderMessages = messageData.messages.filter(message => !knownIds.has(message.message_id));

        conversationMessages.push(...olderMessages);
        conversationMessages.sort((a, b) => a.message_id - b.message_id);

        if (conversationMessages.length > 0) {
            oldestLoadedMessageId = conversationMessages[0].message_id;
        }

        hasOlderMessages = messageData.has_older_messages;
        renderMessengerMessages(conversationMessages, contact);
        updateOlderMessageControls();

        if (messageArea) {
            const restoredAnchor = anchorId ? messageList?.querySelector(`[data-message-id="${anchorId}"]`) : null;
            if (restoredAnchor && anchorTop !== undefined) {
                messageArea.scrollTop += restoredAnchor.getBoundingClientRect().top - anchorTop;
            } else {
                messageArea.scrollTop = previousScrollTop;
            }
        }
        // Keep latestRetrievedMessageId unchanged.
    } catch (error) {
        if (requestVersion !== conversationRequestVersion) {
            return;
        }

        console.error('[MESSENGER] Older history failed:', error);

        if (olderError) {
            olderError.textContent = error?.message || 'Older messages could not be loaded.';
            showElement(olderError);
        }
    } finally {
        if (requestVersion === conversationRequestVersion) {
            isLoadingOlderMessages = false;
            updateMessengerComposer();
        }
    }
};

async function openMessengerConversation(contact) {
    if (isSendingMessage || isDeletingConversations) {
        return;
    }

    if (openingConversationKey === contact.userId) {
        return;
    }

    if (openingConversationKey === null && activeContact?.userId === contact.userId && activeConversationId !== null) {
        return;
    }

    stopMessengerPolling();
    latestRetrievedMessageId = 0;
    messagePollFailures = 0;

    const requestVersion = ++conversationRequestVersion;

    openingConversationKey = contact.userId;
    activeConversationId = null;
    activeContact = null;
    conversationMessages = [];
    oldestLoadedMessageId = null;
    hasOlderMessages = false;
    isLoadingOlderMessages = false;

    hideElement(olderError);
    updateOlderMessageControls();
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
            openingConversationKey = null;

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

async function openMessengerGroupConversation(contact) {
    if (isSendingMessage || isDeletingConversations) {
        return;
    }

    const conversationId = Number(contact.conversationId);

    if (!Number.isSafeInteger(conversationId) || conversationId < 1) {
        return;
    }

    const openingKey = `group:${conversationId}`;

    if (openingConversationKey === openingKey || (openingConversationKey === null && activeConversationId === conversationId)) {
        return;
    }

    stopMessengerPolling();

    const requestVersion = ++conversationRequestVersion;

    openingConversationKey = openingKey;
    activeConversationId = null;
    activeContact = null;
    conversationMessages = [];
    latestRetrievedMessageId = 0;
    messagePollFailures = 0;
    oldestLoadedMessageId = null;
    hasOlderMessages = false;
    isLoadingOlderMessages = false;

    hideElement(olderError);
    hideElement(conversationError);
    hideElement(conversationPlaceholder);
    showElement(conversationLoading);

    messageList?.replaceChildren();
    messageArea?.setAttribute('aria-busy', 'true');

    if (conversationName) {
        conversationName.textContent = contact.displayName;
    }

    if (conversationDescription) {
        conversationDescription.textContent = 'Opening group...';
    }

    updateMessengerComposer();

    try {
        if (!driverToken) {
            throw new Error('Your session token is missing. Please reload the page.');
        }

        /*
         * Existing groups open by conversation ID.
         * The retrieval endpoint checks membership on the server.
         */
        await loadMessengerHistory(conversationId, contact, requestVersion);

        if (requestVersion !== conversationRequestVersion) {
            return;
        }

        activeConversationId = conversationId;
        activeContact = contact;
    } catch (error) {
        if (requestVersion !== conversationRequestVersion) {
            return;
        }

        console.error('[MESSENGER] Failed opening group:', error);

        if (conversationError) {
            conversationError.textContent = error?.message || 'The group could not be opened.';
            showElement(conversationError);
        }

        if (conversationDescription) {
            conversationDescription.textContent = 'Select the group again to retry.';
        }
    } finally {
        if (requestVersion === conversationRequestVersion) {
            openingConversationKey = null;

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

    if (isSendingMessage || isLoadingOlderMessages || isDeletingConversations || openingConversationKey !== null || activeConversationId === null || activeContact === null) {
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
        dispatch: 0,
        admin: 0,
        group: 0
    };

    conversationSummariesById.forEach(summary => {
        const section = summary.conversation_type === 'group' ? 'group' : summary.recipient?.role;

        if (Object.hasOwn(totals, section)) {
            totals[section] += Number(summary.unread_count) || 0;
        }
    });

    const badges = [
        ['#messenger-driver-unread-total', totals.driver],
        ['#messenger-dispatch-unread-total', totals.dispatch],
        ['#messenger-admin-unread-total', totals.admin],
        ['#messenger-group-unread-total', totals.group]
    ];

    badges.forEach(([selector, count]) => {
        const badge = document.querySelector(selector);

        if (!badge) {
            return;
        }

        badge.textContent = String(count);
        badge.setAttribute('aria-label', `${count} unread messages`);
        badge.classList.toggle('d-none', count === 0);
    });
};

function clearActiveMessengerConversation() {
    stopMessengerPolling();

    // Invalidate responses belonging to the previous conversation.
    conversationRequestVersion++;

    activeConversationId = null;
    activeContact = null;
    openingConversationKey = null;

    conversationMessages = [];
    latestRetrievedMessageId = 0;
    oldestLoadedMessageId = null;
    hasOlderMessages = false;
    isLoadingOlderMessages = false;
    pendingSend = null;

    messageList?.replaceChildren();

    if (messageInput) {
        messageInput.value = '';
    }

    if (conversationName) {
        conversationName.textContent = 'Select a contact';
    }

    if (conversationDescription) {
        conversationDescription.textContent = 'Choose someone from the conversation list or New Message.';
    }

    if (conversationPlaceholder) {
        conversationPlaceholder.textContent = 'Select a contact to open a conversation.';
    }

    showElement(conversationPlaceholder);
    hideElement(conversationLoading);
    hideElement(conversationError);
    hideElement(olderError);

    messageArea?.setAttribute('aria-busy', 'false');
    updateOlderMessageControls();
};

function confirmDeleteConversations(conversationIds) {
    if (isDeletingConversations || isSendingMessage || isLoadingOlderMessages || openingConversationKey !== null) {
        return;
    }

    // Capture the exact selection being confirmed.
    const selectedIds = [...new Set(conversationIds)];

    if (selectedIds.length === 0) {
        return;
    }

    const confirmModalEl = document.querySelector('#confirm-modal');
    const confirmModalBtn = document.querySelector('#confirm-modal-confirm');
    const cancelModalBtn = document.querySelector('#confirm-modal-cancel');

    if (!confirmModalEl || !confirmModalBtn || !cancelModalBtn) {
        console.error('[MESSENGER] Confirmation modal elements are missing.');
        return;
    }

    const confirmModal = bootstrap.Modal.getOrCreateInstance(confirmModalEl);

    const message = selectedIds.length === 1 ? 'Delete this conversation and clear its history from your view?' : `Delete these ${selectedIds.length} conversations and clear their history from your view?`;

    confirmDeletion.confirm(message + ' Starting again will show only new messages.' + ' Other participants keep their copies.', selectedIds.length === 1 ? 'Delete conversation' : 'Delete selected', 'Cancel');

    confirmModalBtn.replaceWith(confirmModalBtn.cloneNode(true));
    cancelModalBtn.replaceWith(cancelModalBtn.cloneNode(true));

    const newConfirmBtn = document.querySelector('#confirm-modal-confirm');
    const newCancelBtn = document.querySelector('#confirm-modal-cancel');

    newConfirmBtn.disabled = false;
    newCancelBtn.disabled = false;

    newConfirmBtn.addEventListener('click', () => {
        newConfirmBtn.disabled = true;
        confirmModal.hide();
        deleteMessengerConversations(selectedIds);
    }, { once: true });

    newCancelBtn.addEventListener('click', () => {
        confirmModal.hide();
    }, { once: true });

    confirmModal.show();
};

function confirmDeleteActiveConversation() {
    if (activeConversationId !== null) {
        confirmDeleteConversations([activeConversationId]);
    }
};

function confirmDeleteSelectedConversations() {
    confirmDeleteConversations([...selectedConversationIds]);
};

async function deleteMessengerConversations(conversationIds) {
    if (conversationIds.length === 0 || openingConversationKey !== null || isSendingMessage || isLoadingOlderMessages || isDeletingConversations) {
        return;
    }

    isDeletingConversations = true;

    stopMessengerPolling();
    stopUnreadPolling();

    conversationRequestVersion++;
    unreadStateVersion++;

    hideElement(deleteError);
    updateMessengerComposer();

    try {
        const response = await proDriverRequest('/messenger-delete-conversations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': driverToken
            },
            body: JSON.stringify({
                conversation_ids: conversationIds
            })
        });

        const deleted = response?.deletionData?.deleted_conversations;
        const deletedIds = new Set(Array.isArray(deleted) ? deleted.map(item => Number(item.conversation_id)) : []);

        if (response?.status !== 'success' || !Array.isArray(deleted) || !conversationIds.every(id => deletedIds.has(id))) {
            throw new Error(response?.message || 'The conversations could not be deleted.');
        }

        unreadStateVersion++;

        for (const [recipientId, summary] of conversationSummariesByRecipient) {
            if (deletedIds.has(Number(summary.conversation_id))) {
                conversationSummariesByRecipient.delete(recipientId);
                unreadCountsByRecipient.delete(recipientId);
            }
        }

        deletedIds.forEach(id => {
            confirmedReadPositions.delete(id);
            conversationSummariesById.delete(id);
            selectedConversationIds.delete(id);
        });

        if (deletedIds.has(activeConversationId)) {
            clearActiveMessengerConversation();
        }

        finishConversationSelection();
        refreshConversationSidebar(true);
        updateUnreadBadges();
        updateSelectedContact();

    } catch (error) {
        console.error('[MESSENGER] Conversation deletion failed:', error);

        if (deleteError) {
            deleteError.textContent = error?.message || 'The conversations could not be deleted.';
            showElement(deleteError);
        }
    } finally {
        isDeletingConversations = false;

        updateMessengerComposer();
        scheduleMessengerPoll();
        scheduleUnreadPoll(0);
    }
};

function updateConversationSelectionControls() {
    bulkControls?.classList.toggle('d-none', !isSelectingConversations);
    const isBusy = isDeletingConversations || isSendingMessage || isLoadingOlderMessages || openingConversationKey !== null;

    if (selectConversationsButton) {
        selectConversationsButton.setAttribute('aria-pressed', String(isSelectingConversations));

        selectConversationsButton.disabled = isBusy;
        selectConversationsButton.textContent = isSelectingConversations ? 'Selection mode' : 'Select conversations';
    }

    if (selectionCount) {
        selectionCount.textContent = `${selectedConversationIds.size} selected`;
    }

    if (deleteSelectedButton) {
        deleteSelectedButton.disabled = selectedConversationIds.size === 0 || isBusy;
        deleteSelectedButton.textContent = isDeletingConversations ? 'Deleting...' : 'Delete selected';
    }

    if (selectionCancelButton) {
        selectionCancelButton.disabled = isDeletingConversations;
    }

    const selectedRecipientId = openingConversationKey ?? activeContact?.userId;

    getConversationLists().forEach(list => {
        list.querySelectorAll('button[data-recipient-id], button[data-conversation-id]').forEach(button => {
            const conversationId = getButtonConversationId(button);
            const selected = conversationId !== null && selectedConversationIds.has(conversationId);
            const isOpeningDirect = openingConversationKey !== null && button.dataset.recipientId === String(selectedRecipientId);
            const isActive = openingConversationKey === null && conversationId !== null && conversationId === activeConversationId;
            const pressed = isSelectingConversations ? selected : isOpeningDirect || isActive;

            button.classList.toggle('active', pressed);
            button.setAttribute('aria-pressed', String(pressed));

            button.querySelector('[data-selection-marker]')?.classList.toggle('d-none', !isSelectingConversations || !selected);
        });
    });
};

function finishConversationSelection() {
    isSelectingConversations = false;
    selectedConversationIds.clear();
    updateConversationSelectionControls();
};

function toggleConversationSelection(contact) {
    if (isDeletingConversations || isSendingMessage || isLoadingOlderMessages || openingConversationKey !== null) {
        return;
    }

    const conversationId = getContactConversationId(contact);

    if (conversationId === null) {
        return;
    }

    if (selectedConversationIds.has(conversationId)) {
        selectedConversationIds.delete(conversationId);
    } else {
        if (selectedConversationIds.size >= 100) {
            if (deleteError) {
                deleteError.textContent = 'Select no more than 100 conversations at a time.';
                showElement(deleteError);
            }

            return;
        }

        selectedConversationIds.add(conversationId);
    }

    updateConversationSelectionControls();
};

async function openGroupMembers() {
    if (!groupMembersDialog || activeContact?.role !== 'group' || activeConversationId === null || openingConversationKey !== null || isDeletingConversations) {
        return;
    }

    const conversationId = activeConversationId;
    const requestVersion = ++groupMembersRequestVersion;
    const groupName = activeContact.displayName;

    groupMembersList?.replaceChildren();
    hideElement(groupMembersError);
    showElement(groupMembersLoading);
    groupMembersList?.setAttribute('aria-busy', 'true');

    if (groupMembersTitle) {
        groupMembersTitle.textContent = `${groupName} — Members`;
    }

    if (!groupMembersDialog.open) {
        groupMembersDialog.showModal();
    }

    try {
        const response = await proDriverRequest(`/messenger-group-members?conversation_id=${encodeURIComponent(conversationId)}`, {
                method: 'GET',
                cache: 'no-store',
                headers: {
                    'X-CSRF-Token': driverToken
                }
            }
        );

        if (requestVersion !== groupMembersRequestVersion || !groupMembersDialog.open || conversationId !== activeConversationId) {
            return;
        }

        const memberData = response?.memberData;

        if (response?.status !== 'success' || Number(memberData?.conversation_id) !== conversationId || !Array.isArray(memberData?.members)) {
            throw new Error(response?.message || 'Group members could not be loaded.');
        }

        const fragment = document.createDocumentFragment();

        memberData.members.forEach(member => {
            const row = document.createElement('li');
            row.className = 'list-group-item d-flex justify-content-between align-items-center gap-3';

            const details = document.createElement('div');
            details.className = 'overflow-hidden';

            const name = document.createElement('div');
            name.className = 'fw-semibold text-break';
            name.textContent = `${member.displayName}${member.is_self ? ' (You)' : ''}`;

            const role = document.createElement('small');
            role.className = 'text-body-secondary';
            role.textContent = member.role === 'dispatch' ? 'Dispatch' : member.role === 'admin' ? 'Administration' : 'Driver';

            details.append(name, role);
            row.appendChild(details);

            if (member.is_creator) {
                const badge = document.createElement('span');
                badge.className = 'badge text-bg-secondary flex-shrink-0';
                badge.textContent = 'Creator';

                row.appendChild(badge);
            }

            fragment.appendChild(row);
        });

        groupMembersList?.replaceChildren(fragment);
    } catch (error) {
        if (requestVersion !== groupMembersRequestVersion || !groupMembersDialog.open || conversationId !== activeConversationId) {
            return;
        }

        console.error('[MESSENGER] Group members failed:', error);

        if (groupMembersError) {
            groupMembersError.textContent = error?.message || 'Group members could not be loaded.';
            showElement(groupMembersError);
        }
    } finally {
        if (requestVersion === groupMembersRequestVersion) {
            hideElement(groupMembersLoading);
            groupMembersList?.setAttribute('aria-busy', 'false');
        }
    }
};

function updateGroupCreationControls() {
    const validTitle = Validation.validateMessage(groupTitleInput?.value ?? '', 'group-title');
    const count = selectedGroupRecipientIds.size;

    if (groupSelectionCount) {
        groupSelectionCount.textContent = `${count} selected`;
    }

    if (groupCreateSubmit) {
        groupCreateSubmit.disabled = isCreatingGroup || !validTitle || count < 2 || count > 49;
        groupCreateSubmit.textContent = isCreatingGroup ? 'Creating...' : 'Create group';
        groupCreateSubmit.setAttribute('aria-busy', String(isCreatingGroup));
    }

    if (groupTitleInput) {
        groupTitleInput.disabled = isCreatingGroup;
    }

    if (groupSearch) {
        groupSearch.disabled = isCreatingGroup;
    }

    if (groupContactFields) {
        groupContactFields.disabled = isCreatingGroup;
    }

    if (groupPickerClose) {
        groupPickerClose.disabled = isCreatingGroup;
    }

    if (groupCreateCancel) {
        groupCreateCancel.disabled = isCreatingGroup;
    }

    groupPickerContacts?.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.disabled = isCreatingGroup || (count >= 49 && !selectedGroupRecipientIds.has(Number(checkbox.value)));
    });
};

function renderGroupContactPicker() {
    const searchValue = groupSearch?.value.trim().toLowerCase() ?? '';

    const contacts = messengerContacts.filter(contact => ['driver', 'dispatch'].includes(contact.role) && contact.displayName.toLowerCase().includes(searchValue));

    const fragment = document.createDocumentFragment();

    contacts.forEach(contact => {
        const recipientId = Number(contact.userId);

        const label = document.createElement('label');
        label.className = 'list-group-item list-group-item-action d-flex align-items-center gap-3';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'form-check-input flex-shrink-0 m-0';
        checkbox.value = String(recipientId);
        checkbox.checked = selectedGroupRecipientIds.has(recipientId);

        const details = document.createElement('span');
        details.className = 'overflow-hidden';

        const name = document.createElement('span');
        name.className = 'fw-semibold d-block text-break';
        name.textContent = contact.displayName;

        const role = document.createElement('small');
        role.className = 'text-body-secondary d-block';
        role.textContent = contact.role === 'dispatch' ? 'Dispatch' : 'Driver';

        details.append(name, role);
        label.append(checkbox, details);

        checkbox.addEventListener('change', () => {
            if (isCreatingGroup) {
                checkbox.checked = selectedGroupRecipientIds.has(recipientId);
                return;
            }

            if (checkbox.checked) {
                if (selectedGroupRecipientIds.size >= 49) {
                    checkbox.checked = false;
                    return;
                }

                selectedGroupRecipientIds.add(recipientId);
            } else {
                selectedGroupRecipientIds.delete(recipientId);
            }

            hideElement(groupCreateError);
            updateGroupCreationControls();
        });

        fragment.appendChild(label);
    });

    groupPickerContacts?.replaceChildren(fragment);
    groupPickerEmpty?.classList.toggle('d-none', contacts.length !== 0);

    updateGroupCreationControls();
};

function openGroupPicker() {
    if (!groupPicker || isCreatingGroup || isSendingMessage || isDeletingConversations || openingConversationKey !== null) {
        return;
    }

    finishConversationSelection();

    selectedGroupRecipientIds.clear();
    groupForm?.reset();

    groupTitleInput?.classList.remove('is-invalid');
    groupTitleInput?.removeAttribute('aria-invalid');
    hideElement(groupCreateError);

    renderGroupContactPicker();

    if (!groupPicker.open) {
        groupPicker.showModal();
    }

    groupTitleInput?.focus();
};

function closeGroupPicker() {
    if (!isCreatingGroup) {
        groupPicker?.close();
    }
};

async function createMessengerGroup(event) {
    event.preventDefault();

    if (isCreatingGroup) {
        return;
    }

    hideElement(groupCreateError);

    const title = groupTitleInput?.value.trim() ?? '';
    const validTitle = Validation.validateMessage(title, 'group-title');

    groupTitleInput?.classList.toggle('is-invalid', !validTitle);
    groupTitleInput?.setAttribute('aria-invalid', String(!validTitle));

    if (!validTitle) {
        groupTitleInput?.focus();
        return;
    }

    if (selectedGroupRecipientIds.size < 2 || selectedGroupRecipientIds.size > 49) {
        if (groupCreateError) {
            groupCreateError.textContent = 'Choose between 2 and 49 other contacts.';
            showElement(groupCreateError);
        }

        return;
    }

    let createdContact = null;

    isCreatingGroup = true;
    updateGroupCreationControls();
    updateMessengerComposer();

    try {
        if (!driverToken) {
            throw new Error('Your session token is missing. Please reload the page.');
        }

        const response = await proDriverRequest('/messenger-create-group', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': driverToken
            },
            body: JSON.stringify({
                title,
                recipient_ids: [...selectedGroupRecipientIds]
            })
        });

        const conversation = response?.conversation;
        const conversationId = Number(conversation?.conversation_id);

        if (response?.status !== 'success' || conversation?.conversation_type !== 'group' || !Number.isSafeInteger(conversationId) || conversationId < 1) {
            throw new Error(response?.message || 'The group could not be created.');
        }

        /*
         * Invalidate an older summary request so it cannot remove
         * this newly created group from local state.
         */
        unreadStateVersion++;

        const summary = {
            ...conversation,
            conversation_id: conversationId,
            display_name: conversation.title,
            member_count: Number(conversation.member_count),
            recipient_id: null,
            recipient: null,
            latest_message_id: null,
            latest_sender_id: null,
            latest_message_body: null,
            latest_message_created_at: null,
            latest_is_own: false,
            unread_count: 0
        };

        conversationSummariesById.set(conversationId, summary);
        createdContact = groupSummaryToContact(summary);

        refreshConversationSidebar();

        // Make the new group visible if its section was collapsed.
        const section = groupConversations?.closest('details');

        if (section) {
            section.open = true;
        }

        groupPicker.close();
    } catch (error) {
        console.error('[MESSENGER] Group creation failed:', error);

        if (groupCreateError) {
            groupCreateError.textContent = error?.message || 'The group could not be created.';
            showElement(groupCreateError);
        }
    } finally {
        isCreatingGroup = false;
        updateGroupCreationControls();
        updateMessengerComposer();
    }

    if (createdContact) {
        await openMessengerGroupConversation(createdContact);
        refreshMessengerUnread();

        if (activeConversationId === createdContact.conversationId && !messageInput?.disabled) {
            messageInput?.focus();
        }
    }
};

newGroupButton?.addEventListener('click', openGroupPicker);

groupPickerClose?.addEventListener('click', closeGroupPicker);

groupCreateCancel?.addEventListener('click', closeGroupPicker);

groupSearch?.addEventListener('input', renderGroupContactPicker);

groupTitleInput?.addEventListener('input', () => {
    groupTitleInput.classList.remove('is-invalid');
    groupTitleInput.removeAttribute('aria-invalid');

    hideElement(groupCreateError);
    updateGroupCreationControls();
});

groupForm?.addEventListener('submit', createMessengerGroup);

groupPicker?.addEventListener('cancel', event => {
    // Keep the dialog open while the creation request is running.
    if (isCreatingGroup) {
        event.preventDefault();
    }
});

newMessageButton?.addEventListener('click', openContactPicker);

deleteConversationButton?.addEventListener('click', confirmDeleteActiveConversation);

selectConversationsButton?.addEventListener('click', () => {
    if (isDeletingConversations || isSendingMessage || isLoadingOlderMessages || openingConversationKey !== null) {
        return;
    }

    isSelectingConversations = !isSelectingConversations;
    selectedConversationIds.clear();

    hideElement(deleteError);
    updateConversationSelectionControls();
});

selectionCancelButton?.addEventListener('click', () => {
    if (!isDeletingConversations) {
        finishConversationSelection();
    }
});

viewMembersButton?.addEventListener('click', openGroupMembers);

groupMembersClose?.addEventListener('click', () => {
    groupMembersDialog?.close();
});

groupMembersDialog?.addEventListener('close', () => {
    // Ignore any response still loading from the closed dialog.
    groupMembersRequestVersion++;

    hideElement(groupMembersLoading);
    groupMembersList?.setAttribute('aria-busy', 'false');
});

deleteSelectedButton?.addEventListener('click', confirmDeleteSelectedConversations);

pickerClose?.addEventListener('click', () => {
    contactPicker?.close();
});

loadOlderButton?.addEventListener('click', loadOlderMessengerMessages);

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
updateConversationSelectionControls();
scheduleUnreadPoll(0);
