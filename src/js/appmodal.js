export const buildModal = {
    _message: null,

    get customMessage () {
        return this.message;
    },
    
    set customMessage(message) {
        return this.message = message;
    },

    warning(message, btnText) {
        const warningModal = document.querySelector('#warn-modal');
        const warnModalBody = warningModal.childNodes[1].childNodes[1];
        const warnHeaderIcon = warnModalBody.childNodes[1].childNodes[0];
        const warnHeaderLabel = warnHeaderIcon.parentNode.childNodes[2];
        const warnMessage = warnHeaderLabel.parentNode.nextElementSibling.childNodes[1];
        const warnModalBtn = warnMessage.parentNode.nextElementSibling.childNodes[1];   
        warnModalBody.classList.add('bg-warning-subtle');
        warnHeaderIcon.classList.add('fa-solid', 'fa-triangle-exclamation', 'text-warning');
        warnHeaderLabel.textContent = 'WARNING';
        warnMessage.innerHTML = `<strong>${message}</strong>`;
        warnModalBtn.textContent = btnText; 
    },
    
    danger(message, btnText) {
        const dangerModal = document.querySelector('#danger-modal');
        const dangerModalBody = dangerModal.childNodes[1].childNodes[1];
        const dangerHeadIcon = dangerModalBody.childNodes[1].childNodes[0];
        const dangerHeadLabel = dangerHeadIcon.parentNode.childNodes[2];
        const dangerMessage = dangerHeadLabel.parentNode.nextElementSibling.childNodes[1];
        const dangerModalBtn = dangerMessage.parentNode.nextElementSibling.childNodes[1];
        dangerModalBody.classList.add('bg-danger-subtle');
        dangerHeadIcon.classList.add('fa-solid', 'fa-radiation', 'text-danger');
        dangerHeadLabel.textContent = 'DANGER';
        dangerMessage.innerHTML = `<strong>${message}</strong>`;
        dangerModalBtn.textContent = btnText; 
    },
    
    info(message, btnText) {
        const infoModal = document.querySelector('#info-modal');
        const infoModalBody = infoModal.childNodes[1].childNodes[1];
        const infoHeadIcon = infoModalBody.childNodes[1].childNodes[0];
        const infoHeadLabel = infoHeadIcon.parentNode.childNodes[2];
        const infoMessage = infoHeadLabel.parentNode.nextElementSibling.childNodes[1];
        const infoModalBtn = infoMessage.parentNode.nextElementSibling.childNodes[1];
        infoModalBody.classList.add('bg-info-subtle');
        infoHeadIcon.classList.add('fa-solid', 'fa-circle-info', 'text-info');
        infoHeadLabel.textContent = 'INFO';
        infoMessage.innerHTML = `<strong>${message}</strong>`;
        infoModalBtn.textContent = btnText; 
    },

    success(message, btnText) {
        const successModal = document.querySelector('#success-modal');
        const successModalBody = successModal.childNodes[1].childNodes[1];
        const successHeadIcon = successModalBody.childNodes[1].childNodes[0];
        const successHeadLabel = successHead.Icon.parentNode.childNodes[2];
        const successMessage = successHeadLabel.parentNode.nextElementSibling.childNodes[1];
        const successModalBtn = successMessage.parentNode.nextElementSibling.childNodes[1];
        successModalBody.classList.add('bg-success-subtle');
        successHeadIcon.classList.add('fa-solid', 'fa-thumbs-up', 'text-success');
        successHeadLabel.textContent = 'SUCCESS';
        successMessage.innerHTML = `<strong>${message}</strong>`;
        successModalBtn.textContent = btnText; 
    },

    confirm(message, btnText, btn2Text) {
        const confirmModal = document.querySelector('#confirm-modal');
        const confirmModalBody = confirmModal.childNodes[1].childNodes[1];
        const confirmHeadIcon = confirmModalBody.childNodes[1].childNodes[0];
        const confirmHeadLabel = confirmHeadIcon.parentNode.childNodes[2];
        const confirmMessage = confirmHeadLabel.parentNode.nextElementSibling.childNodes[1];
        const confirmBtn = confirmMessage.parentNode.nextElementSibling.childNodes[1];
        const unConfirmBtn = confirmBtn.nextElementSibling;
        confirmModalBody.classList.add('bg-primary-subtle');
        confirmHeadIcon.classList.add('fa-solid', 'fa-circle-check', 'text-primary');
        confirmHeadLabel.textContent = 'CONFIRM';
        confirmMessage.innerHTML = `<strong>${message}</strong>`;
        confirmBtn.textContent = btnText;
        unConfirmBtn.textContent = btn2Text;
    },

    custom(message, bkgdClr, textClr, faClass, iconClr, cusLabelTxt, btnClr, btnText) {
        const customModal = document.querySelector('#custom-modal');
        const customModalBody = customModal.childNodes[1].childNodes[1];
        const customHeadIcon = customModalBody.childNodes[1].childNodes[0];
        const customHeadLabel = customHeadIcon.parentNode.childNodes[2];
        const customMessage = customHeadLabel.parentNode.nextElementSibling.childNodes[1];
        const customModalBtn = customMessage.parentNode.nextElementSibling.childNodes[1];
        customModalBody.classList.add(bkgdClr, textClr);
        customHeadIcon.classList.add('fa-solid', faClass, iconClr);
        customHeadLabel.textContent = cusLabelTxt;
        customMessage.innerHTML = `<strong>${message}</strong>`;
        customModalBtn.classList.add(btnClr);
        customModalBtn.textContent = btnText; 
    }
};