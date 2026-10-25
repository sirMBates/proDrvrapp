import { buildModal } from './appmodal';

const PAGE_GUIDANCE_MESSAGES = {
    '/': {
        text: 'Welcome to your dashboard. From here, you can update your status( which will automatically notify dispatch ) and get brief details about your job(s) ( if any assignments assigned to you ) for the day. If and when you need to update your status from another page, click the icon in the navigation bar and search for switch status. That is where you\'ll also find other options as well.',
        button: 'Ok'
    },

    '/preferences': {
        text: 'Customize how ProDriver works for you. Preferences let you adjust personal app settings such as appearance, dashboard options and available notification preferences. Settings required by your company or for ProDriver operations may be displayed here but cannot be changed.',
        button: 'Ok'
    },

    '/assignments': {
        text: 'This is your active assignment workspace. Confirmed assignments scheduled for today, along with any earlier assignments that are still in progress, will appear here. Once Garage Report Time is reached, the available assignment actions will unlock so you can update job information, collect required signatures and complete the assignment.',
        button: 'Ok'
    },

    '/profile': {
        text: 'Here on your account info profile, this is where you can view your personal information. You can only update your email, mobile number and password. If you would like to update any of the 3, click the button next to the field you would like to update.',
        button: 'Ok'
    },

    '/timesheet': {
        text: 'This is your timesheet (cha\`ching\`💰). This sheet will hold a record of each job/order you\'ve done for the week. Once the week is over, a new sheet will be made available for you to utilize. If your payroll dept requests, you may send this sheet to them as is, print it out or download a copy for yourself.',
        button: 'Ok'
    }
};

export class PageGuidance {
    constructor(currentView) {
        this.currentView = currentView;

        this.infoButton = document.querySelector('#notifyinfo');
        this.infoModal = document.querySelector('#info-modal');
        this.infoOkButton = document.querySelector('#info-ok');

        this.modalInstance = null;
        this.enabled = true;

        this.handleInfoClick = this.handleInfoClick.bind(this);
        this.handleOkClick = this.handleOkClick.bind(this);
    }

    init() {
        const guidance = PAGE_GUIDANCE_MESSAGES[this.currentView];

        if (!guidance || !this.infoButton || !this.infoModal || !this.infoOkButton) {
            return;
        }

        this.modalInstance = new bootstrap.Modal(this.infoModal, {
            backdrop: 'static',
            keyboard: false
        });

        this.infoButton.addEventListener('click', this.handleInfoClick);
        this.infoOkButton.addEventListener('click', this.handleOkClick);
    }

    setEnabled(enabled) {
        if (typeof enabled !== 'boolean') {
            throw new TypeError('Page Guidance enabled state must be a boolean.');
        }

        this.enabled = enabled;

        if (this.infoButton) {
            this.infoButton.classList.toggle('d-none', !enabled);
        }
    }

    isEnabled() {
        return this.enabled;
    }

    handleInfoClick() {
        if (!this.enabled) {
            return;
        }

        const guidance = PAGE_GUIDANCE_MESSAGES[this.currentView];

        if (!guidance || !this.modalInstance) {
            return;
        }

        buildModal.info(guidance.text, guidance.button);
        this.modalInstance.show();
    }

    handleOkClick() {
        this.modalInstance?.hide();
    }

    destroy() {
        this.infoButton?.removeEventListener('click', this.handleInfoClick);
        this.infoOkButton?.removeEventListener('click', this.handleOkClick);

        this.modalInstance?.dispose();
        this.modalInstance = null;
    }
};
