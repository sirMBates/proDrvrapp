// This file contains validation logic for various input types using regular expressions.
const namePattern = /^[a-zA-Z]{1,}$/;
const statusPattern = /^(?!.*-.*-)[a-zA-Z0-9 \-]+$/;
const numberPattern = /^[0-9]{1,}$/;
const phoneNumberPattern = /^\d{10}$/
const datePattern = /^\d{4}[\-\/](0?[1-9]|1[012])[\-\/](0?[1-9]|[12][0-9]|3[01])$/;
const usernamePattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).\S{4,}$/;
const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,5}$/;
const pswordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#%&_]).\S{7,}$/;
//const photoPattern = /^[a-zA-Z0-9./_-]+\.(jpg|jpeg|png|gif)$/i;
const textPattern = /^[A-Za-z0-9\s.,!?'"()\-@#%$&_+=:;\/\n\r\t\p{Emoji}]{20,300}$/u;
/**
 * Validation class to validate various types of input using regular expressions.
 */
export class Validation {
    /**
     * Validates various types of input using regular expressions.
     * @param {string} input - The input string to validate.
     * @param {string} type - The type of validation to perform.
     * @returns {boolean} - Returns true if the input is valid, false otherwise.
     */
    static validate(input, type) {
        switch (type) {
            case 'text':
                if (input && (this.isPasswordField(input))) {
                    return this.validatePassword(input);
                }
                return this.validateName(input);
            case 'number':
                return this.validateNumber(input);
            case 'tel':
                return this.validatePhoneNumber(input);
            case 'date':
                return this.validateDate(input);
            case 'email':
                return this.validateEmail(input);
            case 'password':
                return this.validatePassword(input);
            case 'file':
                return this.validatePhoto(input);
            default:
                throw new Error('Invalid validation type');
        }
    }

    static validateOnlyUsername(input, type) {
        switch(type) {
            case 'text':
                return this.validateUsername(input);
            default:
                throw new Error('Invalid validation type');
        }
    }

    static validateStatus(input, type) {
        switch(type) {
            case 'text':
                return this.validateDrvrStatus(input);
            default:
                throw new Error('Invalid validation type');
        }
    }

    static validateMessage(input, type) {
        switch(type) {
            case 'textarea':
                return this.validateText(input);
            default:
                throw new Error('Invalid validation type');
        }
    }

    static validateName(input) {
        return namePattern.test(input);
    }

    static validateNumber(input) {
        return numberPattern.test(input);
    }

    static validatePhoneNumber(input) {
        return phoneNumberPattern.test(input);
    }

    static validateDate(input) {
        return datePattern.test(input);
    }

    static validateUsername(input) {
        return usernamePattern.test(input);
    }

    static validateEmail(input) {
        return emailPattern.test(input);
    }

    static validatePassword(input) {
        return pswordPattern.test(input);
    }

    static validatePhoto(input) {
        // The 'input' parameter is a file object in this case
        const file = input;
        const allowedTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif'];

        // Check if file type is allowed
        if (!allowedTypes.includes(file.type)) {
            return false;
        }

        // Optionally, you can also check for file size, e.g., 5MB limit
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            return false;
        }
        return true; // Valid image file
    }

    static validateDrvrStatus(input) {
        return statusPattern.test(input);
    }

    static validateText(input) {
        return textPattern.test(input);
    }

    static isPasswordField(inputValue) {
        return pswordPattern.test(inputValue);
    }
}