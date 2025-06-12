const flashAlert = document.querySelector('#flash-alert');
console.log(flashAlert.childNodes)
//let alertType;
//let alertMessage;
class alertState {
    constructor(type, message) {
        this.type = type;
        this.message = message;
    }

    setAlert(type, message) {
        if (flashAlert) {
            flashAlert.className = `alert alert-${type}`;
            flashAlert.textContent = message;
        }
    }
}