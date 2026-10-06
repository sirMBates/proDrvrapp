import { proDriverRequest, viewableDateTimeHelper } from './helpers.js';
import { buildModal } from './appmodal.js';

const periodHeading = document.querySelector('#timesheet-review-period');
const statusBadge = document.querySelector('#timesheet-review-status');
const loadingState = document.querySelector('#timesheet-review-loading');
const errorState = document.querySelector('#timesheet-review-error');
const reviewContent = document.querySelector('#timesheet-review-content');
const desktopEntries = document.querySelector('#timesheet-review-entries');
const mobileEntries = document.querySelector('#timesheet-review-mobile-entries');
const assignmentCount = document.querySelector('#timesheet-review-assignment-count');
const totalHours = document.querySelector('#timesheet-review-total-hours');
const submitButton = document.querySelector('#submit-timesheet');
const TIMESHEET_COLUMN_COUNT = 13;
const submitForm = document.querySelector('#submit-timesheet-form');
const submitPeriodStart = document.querySelector('#submit-period-start');
const submitPeriodEnd = document.querySelector('#submit-period-end');

function hideElement(element) {
    element?.classList.add('d-none');
};

function showElement(element) {
    element?.classList.remove('d-none');
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

function formatPayPeriod(startDate, endDate) {
    const start = viewableDateTimeHelper(startDate, 'date');
    const end = viewableDateTimeHelper(endDate, 'date');

    return `${start} – ${end}`;
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
            row.appendChild(createTableCell(formatBooleanAnswer(entry.tolls_used)));
            row.appendChild(createTableCell(formatBooleanAnswer(entry.tip)));
            row.appendChild(createTableCell(formatMoney(entry.job_pay)));
            fragment.appendChild(row);
        });
    });

    desktopEntries.replaceChildren(fragment);
};

function appendMobileDetail(list, label, value, options = {}) {
    const term = document.createElement('dt');
    term.className = 'col-5';
    term.textContent = label;

    const description = document.createElement('dd');
    description.className = 'col-7 text-end';

    description.textContent = value === null || value === undefined || value === '' ? '—' : String(value);

    if (options.preserveLines) {
        description.style.whiteSpace = 'pre-line';
    }

    list.append(term, description);
};

function createMobileAssignment(entry, day, accordionId, isLastEntry) {
    const entryId = String(entry.timesheet_id);
    const headingId = `review-mobile-heading-${entryId}`;
    const collapseId = `review-mobile-collapse-${entryId}`;

    const item = document.createElement('article');
    item.className = 'accordion-item';

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

    const lockedBadge = document.createElement('span');
    lockedBadge.className = 'badge text-bg-secondary align-self-center me-2';
    lockedBadge.textContent = 'Locked';

    button.append(buttonContent, lockedBadge);
    heading.appendChild(button);

    const collapse = document.createElement('div');
    collapse.id = collapseId;
    collapse.className = 'accordion-collapse collapse';

    collapse.dataset.bsParent = `#${accordionId}`;
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
    appendMobileDetail(details, 'Tolls', formatBooleanAnswer(entry.tolls_used));
    appendMobileDetail(details, 'Tip', formatBooleanAnswer(entry.tip));
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

function renderMobileEntries(days) {
    if (!mobileEntries) {
        return;
    }

    const fragment = document.createDocumentFragment();

    days.forEach(day => {
        const entries = Array.isArray(day.entries) ? day.entries : [];

        const dateKey = String(day.assignment_date).replaceAll('-', '');

        const accordionId = `review-mobile-day-${dateKey}`;
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
        accordion.id = accordionId;
        accordion.className = 'accordion';

        entries.forEach((entry, index) => {
            accordion.appendChild(createMobileAssignment(entry, day, accordionId, index === entries.length - 1));
        });

        daySection.append(dayHeader, accordion);
        fragment.appendChild(daySection);
    });

    mobileEntries.replaceChildren(fragment);
};

function renderReview(reviewData) {
    const days = Array.isArray(reviewData.days) ? reviewData.days : [];

    if (periodHeading) {
        periodHeading.textContent = formatPayPeriod(reviewData.period_start, reviewData.period_end);
    }

    if (statusBadge) {
        statusBadge.textContent = 'Ready to Submit';
        statusBadge.className = 'badge text-bg-warning';
    }

    if (assignmentCount) {
        assignmentCount.textContent = String(reviewData.assignment_count);
    }

    if (totalHours) {
        totalHours.textContent = reviewData.period_total_hours ?? '0.00';
    }

    renderDesktopEntries(days);
    renderMobileEntries(days);

    hideElement(loadingState);
    showElement(reviewContent);

    if (submitPeriodStart) {
        submitPeriodStart.value = reviewData.period_start ?? '';
    }

    if (submitPeriodEnd) {
        submitPeriodEnd.value = reviewData.period_end ?? '';
    }

    const submissionReady = Boolean(submitForm && reviewData.assignment_count > 0 && submitPeriodStart?.value && submitPeriodEnd?.value);

    if (submissionReady) {
        submitButton?.removeAttribute('disabled');
    } else {
        submitButton?.setAttribute('disabled', '');
    }
};

function showReviewError(message) {
    hideElement(loadingState);
    hideElement(reviewContent);

    if (errorState) {
        errorState.textContent = message || 'The Timesheet review could not be loaded.';
        showElement(errorState);
    }

    if (statusBadge) {
        statusBadge.textContent = 'Unavailable';
        statusBadge.className = 'badge text-bg-danger';
    }

    submitButton?.setAttribute('disabled', '');
};

async function loadTimesheetReview() {
    try {
        const response = await proDriverRequest('/get-timesheet-review', {
                method: 'GET',
                cache: 'no-store'
        });

        if (!response || response.status !== 'success' || !response.reviewData) {
            throw new Error(response?.message || 'Invalid Timesheet review response.');
        }

        renderReview(response.reviewData);
    } catch (error) {
        console.error('[TIMESHEET REVIEW] Failed loading review:', error);
        showReviewError(error?.message || 'The Timesheet review could not be loaded.');
    }
};

let finalSubmissionConfirmed = false;

submitForm?.addEventListener('submit', (event) => {
    if (finalSubmissionConfirmed) {
        finalSubmissionConfirmed = false;
        return;
    }

    event.preventDefault();

    if (submitButton?.disabled) {
        return;
    }

    const confirmModalElement = document.querySelector('#confirm-modal');
    const confirmButton = document.querySelector('#confirm-modal-confirm');
    const cancelButton = document.querySelector('#confirm-modal-cancel');

    if (!confirmModalElement || !confirmButton || !cancelButton) {
        console.error('[TIMESHEET REVIEW] Confirmation modal is unavailable.');
        return;
    }

    buildModal.confirm('Submit this Timesheet to payroll? Once submitted, it cannot be changed.', 'Submit Timesheet', 'Return to Review');

    const confirmModal = bootstrap.Modal.getOrCreateInstance(confirmModalElement);

    confirmButton.onclick = () => {
        finalSubmissionConfirmed = true;

        submitButton.disabled = true;
        submitButton.textContent = 'Submitting…';

        confirmModal.hide();
        submitForm.requestSubmit();
    };

    cancelButton.onclick = () => {
        confirmModal.hide();
    };

    confirmModal.show();
});

submitButton?.setAttribute('disabled', '');

loadTimesheetReview();