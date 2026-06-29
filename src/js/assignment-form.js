import { Validation } from "./validation.js";
import { setFieldError, setFieldValid } from "./helpers.js";
export function normalizeDecimalValue(value) {
    const num = parseFloat(value);

    if (Number.isNaN(num)) {
        return '';
    }
    return num.toFixed(2);
}

export function validateEditableElement(el, type, field) {
    const value = (el?.value ?? el?.textContent ?? '').trim();

    if (!Validation.validate(value, type)) {
        setFieldError(el, `Invalid ${field.replaceAll('_', ' ')}.`);
        return false;
    }

    setFieldValid(el);
    return true;
};

export function validateAssignmentTextarea(el) {
    const value = (el?.value ?? '').trim();

    if (!Validation.validateMessage(value, 'assignment-textarea')) {
        setFieldError(el, 'Please remove invalid characters.');
        return false;
    }

    setFieldValid(el);
    return true;
};

export function validateCrossFieldRules() {
    const errors = [];

    const totalHrsCell = document.querySelector('[data-field="total_hrs"]');
    const drivingTimeCell = document.querySelector('[data-field="driving_time"]');
    const endTimeCell = document.querySelector('[data-field="actual_end_time"]');
    const dropTimeCell = document.querySelector('[data-field="actual_drop_time"]');

    const totalInput = totalHrsCell?.querySelector('input');
    const driveInput = drivingTimeCell?.querySelector('input');
    const endInput = endTimeCell?.querySelector('input');
    const dropInput = dropTimeCell?.querySelector('input');

    const totalVal = parseFloat((totalInput?.value ?? totalHrsCell?.textContent ?? '').trim());
    const driveVal = parseFloat((driveInput?.value ?? drivingTimeCell?.textContent ?? '').trim());

    if (!Number.isNaN(totalVal) && !Number.isNaN(driveVal) && driveVal > totalVal) {
        setFieldError(driveInput || drivingTimeCell, 'Driving time cannot exceed total hours.');
        setFieldError(totalInput || totalHrsCell, 'Total hours must be at least driving time.');
        errors.push(driveInput || drivingTimeCell);
    }

    const endVal = (endInput?.value ?? endTimeCell?.textContent ?? '').trim();
    const dropVal = (dropInput?.value ?? dropTimeCell?.textContent ?? '').trim();

    if (endVal && dropVal) {
        const normalizedEndVal = endVal.replace(' ', 'T').slice(0, 16);
        const endDate = new Date(normalizedEndVal);

        if (!Number.isNaN(endDate.getTime())) {
            const endDateOnly = normalizedEndVal.slice(0, 10);
            const normalizedDropVal = dropVal.slice(0, 5);
            const dropDate = new Date(`${endDateOnly}T${normalizedDropVal}`);

            if (!Number.isNaN(dropDate.getTime()) && endDate < dropDate) {
                setFieldError(endInput || endTimeCell, 'Actual end time cannot be earlier than drop time.');
                setFieldError(dropInput || dropTimeCell, 'Drop time must be before end time.');
                errors.push(endInput || endTimeCell);
            }
        }
    }

    return errors;
};

export function validateCurrentAssignmentFields(options) {
    const {
        showFlashAlert,
        focusFirstInvalid
    } = options;
    const errors = [];
    const editableCells = document.querySelectorAll('.editable-data');

    for (const cell of editableCells) {
        const field = cell.dataset.field || '';
        const type = cell.dataset.type || '';
        const inputEl = cell.querySelector('input');
        const target = inputEl || cell;

        let value = inputEl ? inputEl.value.trim() : ((type === 'datetime' || type === 'datetime-local') ? (cell.dataset.raw || cell.textContent.trim()) : cell.textContent.trim());

        if (type === 'time' && value) {
            value = value.slice(0, 5);
        }

        if ((type === 'datetime' || type === 'datetime-local') && value) {
            value = value.replace(' ', 'T').slice(0, 16);
        }

        if (type === 'decimal' && value) {
            value = normalizeDecimalValue(value);
        }

        if (!Validation.validate(value, type)) {
            setFieldError(target, `Invalid ${field.replaceAll('_', ' ')}.`);
            errors.push(target);
            continue;
        }
        setFieldValid(target);
    }

    for (const id of ['pickup_details', 'destination_details', 'shared_job_note']) {
        const el = document.getElementById(id);
        if (!el) continue;
        if (!validateAssignmentTextarea(el)) {
            errors.push(el);
        }
    }

    const crossFieldErrors = validateCrossFieldRules();
    errors.push(...crossFieldErrors);

    if (errors.length) {
        showFlashAlert('error', 'Please correct the highlighted fields.');
        focusFirstInvalid(errors);
        return false;
    }

    return true;
};

export function appendHiddenFields(form, fields) {
    Object.entries(fields).forEach(([name, value]) => {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'name';
        hidden.value = value ?? '';
        hidden.classList.add('temp-hidden');
        form.appendChild(hidden);
    })
};

export function toInputDateTime(value) {
    if (!value) return '';

    return value.replace(' ', 'T').slice(0, 16);
};

export function toRawDateTime(value) {
    if (!value) return '';

    return value.replace('T', ' ').slice(0, 16) + ':00';
};

export function toDisplayDateTime(value, formatter) {
    if (!value) return '';

    return formatter(value, 'datetime');
};