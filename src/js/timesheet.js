import { fetchDrvr, showFlashAlert, viewableDateTimeHelper } from './helpers.js';
import { buildModal } from './appmodal.js';

const timesheetPeriod = document.querySelector('#timesheet-period');
const timesheetStatus = document.querySelector('#timesheet-status');
const timesheetNotice = document.querySelector('#timesheet-notice');
const loadingState = document.querySelector('#timesheet-loading');
const errorState = document.querySelector('#timesheet-error');
const emptyState = document.querySelector('#timesheet-empty');
const timesheetContent = document.querySelector('#timesheet-content');
const timesheetSummary = document.querySelector('#timesheet-summary');
const desktopEntries = document.querySelector('#timesheet-entries');
const mobileEntries = document.querySelector('#timesheet-mobile-entries');
const assignmentCount = document.querySelector('#timesheet-assignment-count');
const totalHours = document.querySelector('#timesheet-total-hours');
const previousButton = document.querySelector('#previous-timesheet');
const nextButton = document.querySelector('#next-timesheet');
const saveButton = document.querySelector('#save-timesheet');
const reviewButton = document.querySelector('#review-timesheet');
const drvrTokenInput = document.querySelector('#drvrToken');
const reviewPeriodStartInput = document.querySelector('#review-period-start');
const reviewPeriodEndInput = document.querySelector('#review-period-end');
const TIMESHEET_COLUMN_COUNT = 13;
let currentTimesheet = null;
let timesheetSaveInProgress = false;
let activePeriodView = 'landing';

function hideElement(element) {
    element?.classList.add('d-none');
};

function showElement(element) {
    element?.classList.remove('d-none');
};

function resetViewStates() {
    hideElement(errorState);
    hideElement(emptyState);
    hideElement(timesheetContent);
    hideElement(timesheetSummary);
    hideElement(timesheetNotice);

    if (reviewPeriodStartInput) {
        reviewPeriodStartInput.value = '';
    }

    if (reviewPeriodEndInput) {
        reviewPeriodEndInput.value = '';
    }

    if (errorState) {
        errorState.textContent = '';
    }

    if (desktopEntries) {
        desktopEntries.replaceChildren();
    }

    if (mobileEntries) {
        mobileEntries.replaceChildren();
    }

    previousButton?.setAttribute('disabled', '');
    nextButton?.setAttribute('disabled', '');
    saveButton?.setAttribute('disabled', '');
    reviewButton?.setAttribute('disabled', '');
};

function showLoadingState() {
    resetViewStates();
    showElement(loadingState);

    if (timesheetStatus) {
        timesheetStatus.textContent = 'Loading';
        timesheetStatus.className = 'badge text-bg-secondary';
    }
};

function showErrorState(message) {
    hideElement(loadingState);
    hideElement(emptyState);
    hideElement(timesheetContent);
    hideElement(timesheetSummary);
    hideElement(timesheetNotice);

    if (errorState) {
        errorState.textContent = message || 'The Timesheet could not be loaded.';
        showElement(errorState);
    }

    if (timesheetStatus) {
        timesheetStatus.textContent = 'Unavailable';
        timesheetStatus.className = 'badge text-bg-danger';
    }
};

function showEmptyState() {
    hideElement(loadingState);
    hideElement(errorState);
    hideElement(timesheetContent);
    hideElement(timesheetSummary);

    showElement(emptyState);
};

function formatPayPeriod(startDate, endDate) {
    const start = viewableDateTimeHelper(startDate, 'date');
    const end = viewableDateTimeHelper(endDate, 'date');
    return `${start} – ${end}`;
};

function renderPeriodHeader(timesheetData) {
    if (timesheetPeriod) {
        timesheetPeriod.textContent = formatPayPeriod(timesheetData.period_start, timesheetData.period_end);
    }

    if (!timesheetStatus) {
        return;
    }

    if (timesheetData.period_state === 'outstanding') {
        timesheetStatus.textContent = 'Outstanding — Submit Required';
        timesheetStatus.className = 'badge text-bg-danger';

        if (timesheetNotice) {
            timesheetNotice.className = 'alert alert-danger mt-3 mb-0';
            timesheetNotice.textContent = 'Your previous pay-period Timesheet remains open. ' + 'Complete all remaining assignments, then save and ' + 'submit it to payroll.';
            showElement(timesheetNotice);
        }

        return;
    }

    if (timesheetData.submission_available) {
        timesheetStatus.textContent = 'Ready for Review';
        timesheetStatus.className = 'badge text-bg-warning';

        if (timesheetNotice) {
            timesheetNotice.className = 'alert alert-warning mt-3 mb-0';
            timesheetNotice.textContent = 'This pay period is available for review and submission.';
            showElement(timesheetNotice);
        }

        return;
    }

    timesheetStatus.textContent = 'Open';
    timesheetStatus.className = 'badge text-bg-success';
    hideElement(timesheetNotice);
};

function renderSummary(timesheetData) {
    if (assignmentCount) {
        assignmentCount.textContent = String(timesheetData.assignment_count);
    }

    /*
     * The period-wide total will be connected after the
     * assignment-day renderer is added.
     */
    if (totalHours) {
        totalHours.textContent = timesheetData.period_total_hours ?? '0.00';
    }
};

function createTableCell(value, className = '') {
    const cell = document.createElement('td');

    cell.textContent = value === null || value === undefined || value === '' ? '—' : String(value);

    if (className) {
        cell.className = className;
    }

    return cell;
};

function createOrderCell(entry) {
    const cell = document.createElement('td');

    const orderId = document.createElement('div');
    orderId.className = 'fw-semibold';
    orderId.textContent = String(entry.order_id);

    const assignmentControl = document.createElement('small');
    assignmentControl.className = 'text-body-secondary';
    assignmentControl.textContent = entry.assignment_control || '—';

    cell.append(orderId, assignmentControl);

    return cell;
};

function createDestinationCell(entry) {
    const cell = document.createElement('td');

    const origin = document.createElement('div');
    const originLabel = document.createElement('span');

    originLabel.className = 'fw-semibold';
    originLabel.textContent = 'From: ';

    origin.append(originLabel, document.createTextNode(entry.origin || '—'));
    const destination = document.createElement('div');
    const destinationLabel = document.createElement('span');

    destinationLabel.className = 'fw-semibold';
    destinationLabel.textContent = 'To: ';
    destination.append(destinationLabel, document.createTextNode(entry.destination || '—'));

    cell.append(origin, destination);

    return cell;
};

function formatBooleanAnswer(value) {
    if (value === true) {
        return 'Yes';
    }

    if (value === false) {
        return 'No';
    }

    return 'Not answered';
};

function findTimesheetEntry(timesheetId) {
    const days = Array.isArray(currentTimesheet?.days) ? currentTimesheet.days : [];

    for (const day of days) {
        const entries = Array.isArray(day.entries) ? day.entries : [];
        const entry = entries.find(item => Number(item.timesheet_id) === Number(timesheetId));

        if (entry) {
            return entry;
        }
    }

    return null;
};

function synchronizeAnswerControls(timesheetId, field, value) {
    const controls = document.querySelectorAll('.timesheet-answer-control');
    const controlValue = value === true ? '1' : value === false ? '0' : '';

    controls.forEach(control => {
        if (Number(control.dataset.timesheetId) === Number(timesheetId) && control.dataset.field === field) {
            control.value = controlValue;
        }
    });
};

function createAnswerControl(entry, field, label, surface) {
    if (entry.locked) {
        const answer = document.createElement('span');
        answer.textContent = formatBooleanAnswer(entry[field]);
        return answer;
    }

    const select = document.createElement('select');

    select.id = `timesheet-${surface}-${field}-${entry.timesheet_id}`;
    select.className = 'form-select form-select-sm timesheet-answer-control';
    select.dataset.timesheetId = String(entry.timesheet_id);
    select.dataset.field = field;
    select.setAttribute('aria-label', `${label} for order ${entry.order_id}`);

    const options = [
        ['', 'Not answered'],
        ['1', 'Yes'],
        ['0', 'No']
    ];

    options.forEach(([value, text]) => {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = text;
        select.appendChild(option);
    });

    select.value = entry[field] === true ? '1' : entry[field] === false ? '0' : '';

    select.addEventListener('change', () => {
        const selectedValue = select.value === '' ? null : select.value === '1';
        const storedEntry = findTimesheetEntry(entry.timesheet_id);
        if (!storedEntry || storedEntry.locked) {
            return;
        }

        storedEntry[field] = selectedValue;
        synchronizeAnswerControls(entry.timesheet_id, field, selectedValue);
    });

    return select;
};

function createAnswerTableCell(entry, field, label) {
    const cell = document.createElement('td');
    cell.className = 'timesheet-answer-cell';
    cell.appendChild(createAnswerControl(entry, field, label, 'desktop'));

    return cell;
};

function formatMoney(value) {
    if (value === null || value === undefined || value === '') {
        return 'Pending';
    }

    const amount = Number(value);

    if (!Number.isFinite(amount)) {
        return 'Pending';
    }

    return amount.toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD'
    });
};

function renderDesktopEntries(days) {
    if (!desktopEntries) {
        return;
    }

    const fragment = document.createDocumentFragment();

    days.forEach(day => {
        const entries = Array.isArray(day.entries) ? day.entries : [];
        const dayHeadingRow = document.createElement('tr');
        dayHeadingRow.className = 'table-secondary';

        const dayHeading = document.createElement('th');
        dayHeading.colSpan = TIMESHEET_COLUMN_COUNT;
        dayHeading.scope = 'rowgroup';
        dayHeading.className = 'fw-semibold';
        dayHeading.textContent = viewableDateTimeHelper(day.assignment_date, 'date');

        dayHeadingRow.appendChild(dayHeading);
        fragment.appendChild(dayHeadingRow);

        entries.forEach((entry, index) => {
            const row = document.createElement('tr');
            const isLastEntry = index === entries.length - 1;

            row.dataset.timesheetId = String(entry.timesheet_id);
            row.appendChild(createOrderCell(entry));
            row.appendChild(createDestinationCell(entry));
            row.appendChild(createTableCell(entry.vehicle_id));

            row.appendChild(createTableCell(viewableDateTimeHelper(entry.garage_report_at, 'datetime')));
            row.appendChild(createTableCell(viewableDateTimeHelper(entry.spot_time, 'time')));
            row.appendChild(createTableCell(viewableDateTimeHelper(entry.actual_drop_time, 'time')));
            row.appendChild(createTableCell(entry.job_details, 'text-wrap'));
            row.appendChild(createTableCell(isLastEntry && day.end_of_duty ? viewableDateTimeHelper(day.end_of_duty, 'datetime') : '—'));

            row.appendChild(createTableCell(entry.total_job_time));
            row.appendChild(createTableCell(isLastEntry ? day.total_shift_hours : '—'));
            row.appendChild(createAnswerTableCell(entry, 'tolls_used', 'Tolls'));
            row.appendChild(createAnswerTableCell(entry, 'tip', 'Tip'));
            row.appendChild(createTableCell(formatMoney(entry.job_pay)));
            fragment.appendChild(row);
        });
    });

    desktopEntries.replaceChildren(fragment);
};

function appendMobileDetail(list, label, value, options = {}) {
    const term = document.createElement('dt');
    term.className = 'col-5';

    const description = document.createElement('dd');
    description.className = 'col-7 text-end';

    term.textContent = label;

    description.textContent = value === null || value === undefined || value === '' ? '—' : String(value);

    if (options.preserveLines) {
        description.style.whiteSpace = 'pre-line';
    }

    list.append(term, description);
};

function appendMobileControl(list, label, control) {
    const term = document.createElement('dt');
    term.className = 'col-5';
    term.textContent = label;

    const description = document.createElement('dd');
    description.className = 'col-7';

    description.appendChild(control);

    list.append(term, description);
};

function createMobileAssignment(entry, day, dayAccordionId, isLastEntry) {
    const entryId = String(entry.timesheet_id);
    const headingId = `timesheet-mobile-heading-${entryId}`;
    const collapseId = `timesheet-mobile-collapse-${entryId}`;
    const item = document.createElement('article');

    item.className = 'accordion-item';
    item.dataset.timesheetId = entryId;

    const heading = document.createElement('h4');
    heading.className = 'accordion-header';
    heading.id = headingId;

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'accordion-button collapsed';
    button.dataset.bsToggle = 'collapse';
    button.dataset.bsTarget = `#${collapseId}`;
    button.setAttribute('aria-expanded', 'false');
    button.setAttribute('aria-controls', collapseId);

    const buttonContent = document.createElement('span');
    buttonContent.className = 'd-flex flex-column flex-grow-1 pe-3';

    const orderLine = document.createElement('span');
    orderLine.className = 'fw-semibold';
    orderLine.textContent = `Order #${entry.order_id}`;

    const destinationLine = document.createElement('small');
    destinationLine.className = 'text-body-secondary';

    destinationLine.textContent = `${entry.origin || '—'} → ` + `${entry.destination || '—'}`;

    const hoursLine = document.createElement('small');
    hoursLine.className = 'mt-1';

    hoursLine.textContent = `${entry.total_job_time} total hours`;

    buttonContent.append(orderLine, destinationLine, hoursLine);

    if (entry.locked) {
        const lockedBadge = document.createElement('span');
        lockedBadge.className = 'badge text-bg-secondary align-self-center me-2';
        lockedBadge.textContent = 'Locked';
        button.append(buttonContent, lockedBadge);
    } else {
        button.appendChild(buttonContent);
    }

    heading.appendChild(button);

    const collapse = document.createElement('div');
    collapse.id = collapseId;
    collapse.className = 'accordion-collapse collapse';
    collapse.dataset.bsParent = `#${dayAccordionId}`;
    collapse.setAttribute('aria-labelledby', headingId);

    const body = document.createElement('div');
    body.className = 'accordion-body';

    const details = document.createElement('dl');
    details.className = 'row mb-0';

    appendMobileDetail(details, 'Assignment Control', entry.assignment_control);
    appendMobileDetail(details, 'Vehicle / Bus #', entry.vehicle_id);
    appendMobileDetail(details, 'Garage Report Date/Time', viewableDateTimeHelper(entry.garage_report_at, 'datetime'));

    appendMobileDetail(details, 'Spot Time', viewableDateTimeHelper(entry.spot_time, 'time'));
    appendMobileDetail(details, 'Drop Time', viewableDateTimeHelper(entry.actual_drop_time, 'time'));
    appendMobileDetail(details, 'Job Details', entry.job_details, { preserveLines: true });

    appendMobileDetail(details, 'Total Hours', entry.total_job_time);
    appendMobileControl(details, 'Tolls', createAnswerControl(entry, 'tolls_used', 'Tolls', 'mobile'));
    appendMobileControl(details, 'Tip', createAnswerControl(entry, 'tip', 'Tip', 'mobile'));
    appendMobileDetail(details, 'Job Amount Paid', formatMoney(entry.job_pay));

    if (isLastEntry) {
        appendMobileDetail(details, 'End of Duty', day.end_of_duty ? viewableDateTimeHelper(day.end_of_duty, 'datetime') : '—');
        appendMobileDetail(details, 'Total Shift Hours', day.total_shift_hours);
    }

    body.appendChild(details);
    collapse.appendChild(body);
    item.append(heading, collapse);

    return item;
};

function updateSaveButtonState() {
    const days = Array.isArray(currentTimesheet?.days) ? currentTimesheet.days : [];

    const hasUnlockedEntries = days.some(day => {
        const entries = Array.isArray(day.entries) ? day.entries : [];
        return entries.some(entry => !entry.locked);
    });

    if (hasUnlockedEntries && !timesheetSaveInProgress) {
        saveButton?.removeAttribute('disabled');
    } else {
        saveButton?.setAttribute('disabled', '');
    }
};

function updateReviewButtonState() {
    const days = Array.isArray(currentTimesheet?.days) ? currentTimesheet.days : [];
    const entries = days.flatMap(day => Array.isArray(day.entries) ? day.entries : []);
    const allEntriesLocked = entries.length > 0 && entries.every(entry => entry.locked);
    const noneSubmitted = entries.every(entry => entry.submission_id === null);
    const reviewAvailable = currentTimesheet?.submission_available === true && allEntriesLocked && noneSubmitted;

    if (reviewAvailable) {
        reviewButton?.removeAttribute('disabled');
    } else {
        reviewButton?.setAttribute('disabled', '');
    }
};

function updatePeriodNavigation(timesheetData) {
    const periodState = timesheetData?.period_state;
    const hasOutstandingPrevious = timesheetData?.has_outstanding_previous === true;

    if (periodState === 'outstanding') {
        previousButton?.setAttribute('disabled', '');
        nextButton?.removeAttribute('disabled');
        return;
    }

    if (periodState === 'current') {
        nextButton?.setAttribute('disabled', '');

        if (hasOutstandingPrevious) {
            previousButton?.removeAttribute('disabled');
        } else {
            previousButton?.setAttribute('disabled', '');
        }

        return;
    }

    previousButton?.setAttribute('disabled', '');
    nextButton?.setAttribute('disabled', '');
};

function getUnlockedTimesheetEntries() {
    const days = Array.isArray(currentTimesheet?.days) ? currentTimesheet.days : [];

    return days.flatMap(day => {
        const entries = Array.isArray(day.entries) ? day.entries : [];

        return entries.filter(entry => !entry.locked).map(entry => ({
            timesheet_id: Number(entry.timesheet_id),
            tolls_used: entry.tolls_used === true ? 1 : entry.tolls_used === false ? 0 : null,
            tip: entry.tip === true ? 1 : entry.tip === false ? 0 : null
        }));
    });
};

function setSaveButtonLoading(isLoading) {
    if (!saveButton) {
        return;
    }

    saveButton.replaceChildren();

    if (isLoading) {
        const spinner = document.createElement('span');
        spinner.className = 'spinner-border spinner-border-sm me-2';
        spinner.setAttribute('aria-hidden', 'true');

        const label = document.createElement('span');
        label.textContent = 'Saving & Locking…';

        saveButton.append(spinner, label);
        saveButton.setAttribute('disabled', '');
        saveButton.setAttribute('aria-busy', 'true');

        return;
    }

    saveButton.textContent = 'Save & Lock';
    saveButton.removeAttribute('aria-busy');
};

async function performSaveAndLock(entries, csrfToken) {
    timesheetSaveInProgress = true;
    setSaveButtonLoading(true);

    try {
        const response = await fetchDrvr('/save-timesheet', {
            method: 'POST',
            cache: 'no-store',
            headers: {
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({
                entries
            })
        });

        if (!response || response.status !== 'success') {
            throw new Error(response?.message || 'The Timesheet entries could not be saved.');
        }

        showFlashAlert('success', response.message || 'Timesheet entries saved and locked.');

        /*
         * Reload the authoritative database state so locked
         * selections are rendered as read-only text.
         */
        await loadCurrentTimesheet();
    } catch (error) {
        console.error('[TIMESHEET] Save and lock failed:', error);
        showFlashAlert('error', error?.message || 'The Timesheet entries could not be saved.');
    } finally {
        timesheetSaveInProgress = false;
        setSaveButtonLoading(false);
        updateSaveButtonState();
        updateReviewButtonState();
    }
};

function showSaveAndLockConfirmation(entries, csrfToken) {
    const confirmModalEl = document.querySelector('#confirm-modal');
    const confirmModalBtn = document.querySelector('#confirm-modal-confirm');
    const cancelModalBtn = document.querySelector('#confirm-modal-cancel');

    if (!confirmModalEl || !confirmModalBtn || !cancelModalBtn) {
        showFlashAlert('error', 'The confirmation window is unavailable.');

        return;
    }

    const confirmModal = new bootstrap.Modal(confirmModalEl);

    buildModal.confirm('Save and permanently lock these Timesheet entries?' + '<br><br>' + 'Toll and tip selections cannot be changed after locking. ' + 'Unanswered selections will remain recorded as not answered.', 'Save & Lock', 'Cancel');

    /*
     * Remove listeners left by any earlier use of this shared
     * confirmation modal.
     */
    confirmModalBtn.replaceWith(confirmModalBtn.cloneNode(true));
    cancelModalBtn.replaceWith(cancelModalBtn.cloneNode(true));

    const newConfirmBtn = document.querySelector('#confirm-modal-confirm');
    const newCancelBtn = document.querySelector('#confirm-modal-cancel');

    newConfirmBtn.addEventListener('click', async () => {
        newConfirmBtn.setAttribute('disabled', '');
        bootstrap.Modal.getInstance(confirmModalEl)?.hide();

        await performSaveAndLock(entries, csrfToken);
    }, { once: true });

    newCancelBtn.addEventListener('click', () => {
        bootstrap.Modal.getInstance(confirmModalEl)?.hide();
    }, { once: true });

    confirmModal.show();
};

function saveAndLockTimesheet() {
    if (timesheetSaveInProgress) {
        return;
    }

    const entries = getUnlockedTimesheetEntries();
    if (!entries.length) {
        showFlashAlert('warning', 'There are no unlocked Timesheet entries to save.');
        return;
    }

    const csrfToken = drvrTokenInput?.value?.trim() ?? '';
    if (!csrfToken) {
        showFlashAlert('error', 'The security token is unavailable. Please reload the page.');
        return;
    }

    showSaveAndLockConfirmation(entries, csrfToken);
};

function renderMobileEntries(days) {
    if (!mobileEntries) {
        return;
    }

    const fragment = document.createDocumentFragment();

    days.forEach(day => {
        const entries = Array.isArray(day.entries) ? day.entries : [];
        const dateKey = String(day.assignment_date).replaceAll('-', '');
        const dayAccordionId = `timesheet-mobile-day-${dateKey}`;
        const daySection = document.createElement('section');
        daySection.className = 'mb-4';

        const dayHeader = document.createElement('div');
        dayHeader.className = 'd-flex flex-column flex-sm-row ' + 'justify-content-between gap-1 mb-2';

        const dayTitle = document.createElement('h3');
        dayTitle.className = 'h5 mb-0';
        dayTitle.textContent = viewableDateTimeHelper(day.assignment_date, 'date');

        const daySummary = document.createElement('small');
        daySummary.className = 'text-body-secondary';

        const endOfDuty = day.end_of_duty ? viewableDateTimeHelper(day.end_of_duty, 'time') : 'Pending';
        daySummary.textContent = `Shift: ${day.total_shift_hours} hrs` + ` · End: ${endOfDuty}`;
        dayHeader.append(dayTitle, daySummary);

        const accordion = document.createElement('div');
        accordion.id = dayAccordionId;
        accordion.className = 'accordion';

        entries.forEach((entry, index) => {
            accordion.appendChild(createMobileAssignment(entry, day, dayAccordionId, index === entries.length - 1));
        });

        daySection.append(dayHeader, accordion);
        fragment.appendChild(daySection);
    });

    mobileEntries.replaceChildren(fragment);
};

function renderTimesheet(timesheetData) {
    hideElement(loadingState);

    renderPeriodHeader(timesheetData);
    renderSummary(timesheetData);
    updatePeriodNavigation(timesheetData);

    const days = Array.isArray(timesheetData.days) ? timesheetData.days : [];

    if (timesheetData.assignment_count === 0 || days.length === 0) {
        showEmptyState();
        return;
    }

    if (reviewPeriodStartInput) {
        reviewPeriodStartInput.value = timesheetData.period_start ?? '';
    }

    if (reviewPeriodEndInput) {
        reviewPeriodEndInput.value = timesheetData.period_end ?? '';
    }

    renderDesktopEntries(days);
    renderMobileEntries(days);
    updateSaveButtonState();
    updateReviewButtonState();

    showElement(timesheetContent);
    showElement(timesheetSummary);
};

async function loadCurrentTimesheet(periodView = activePeriodView) {
    showLoadingState();
    const endpoint = periodView === 'landing' ? '/get-timesheet' : `/get-timesheet?period=${encodeURIComponent(periodView)}`;

    try {
        const response = await fetchDrvr(endpoint, {
            method: 'GET',
            cache: 'no-store'
        });

        if (!response || response.status !== 'success' || !response.timesheetData) {
            throw new Error(response?.message || 'Invalid Timesheet response.');
        }

        currentTimesheet = response.timesheetData;
        activePeriodView = currentTimesheet.period_state === 'outstanding' ? 'outstanding' : 'current';
        renderTimesheet(currentTimesheet);
    } catch (error) {
        console.error('[TIMESHEET] Failed loading Timesheet:', error);
        showErrorState(error?.message || 'The Timesheet could not be loaded.');
    }
};

previousButton?.addEventListener('click', () => {
    loadCurrentTimesheet('outstanding');
});

nextButton?.addEventListener('click', () => {
    loadCurrentTimesheet('current');
});

saveButton?.addEventListener('click', saveAndLockTimesheet);

loadCurrentTimesheet();
