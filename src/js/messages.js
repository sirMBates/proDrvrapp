import { proDriverRequest } from './helpers.js';

const contactsLoading = document.querySelector('#messenger-contacts-loading');
const contactsError = document.querySelector('#messenger-contacts-error');
const contactsEmpty = document.querySelector('#messenger-contacts-empty');
const dispatchContacts = document.querySelector('#messenger-dispatch-contacts');
const driverContacts = document.querySelector('#messenger-driver-contacts');
const contactSearch = document.querySelector('#messenger-contact-search');
const driverToken = document.querySelector('#drvrToken')?.value;

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

contactSearch?.addEventListener('input', filterDriverContacts);

loadMessengerContacts();