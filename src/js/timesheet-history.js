import { proDriverRequest, showFlashAlert, viewableDateTimeHelper } from './helpers.js';

const loadingState = document.querySelector('#timesheet-history-loading');
const errorState = document.querySelector('#timesheet-history-error');
const emptyState = document.querySelector('#timesheet-history-empty');
const historyContent = document.querySelector('#timesheet-history-content');
const periodSelect = document.querySelector('#timesheet-history-period');
const selectedPeriod = document.querySelector('#timesheet-history-selected-period');
const submissionId = document.querySelector('#timesheet-history-submission-id');
const assignmentCount = document.querySelector('#timesheet-history-assignment-count');
const totalHours = document.querySelector('#timesheet-history-total-hours');
const submittedAt = document.querySelector('#timesheet-history-submitted-at');
const viewLink = document.querySelector('#timesheet-history-view');
const downloadLink = document.querySelector('#timesheet-history-download');
const printButton = document.querySelector('#timesheet-history-print');
const drvrToken = document.querySelector('#drvrToken')?.value ?? '';
let submissions = [];

function hideElement(element) {
    element?.classList.add('d-none');
};

function showElement(element) {
    element?.classList.remove('d-none');
};

function formatPeriod(startDate, endDate) {
    const start = viewableDateTimeHelper(startDate, 'date');
    const end = viewableDateTimeHelper(endDate, 'date');
    return `${start} – ${end}`;
};

function buildPdfUrl(timesheetSubmissionId, disposition = 'inline') {
    const params = new URLSearchParams({
        submission_id: String(timesheetSubmissionId),
        disposition
    });

    return `/timesheet-pdf?${params.toString()}`;
};

function setPdfActionsAvailable(available) {
    if (viewLink) {
        viewLink.classList.toggle('disabled', !available);
        viewLink.setAttribute('aria-disabled', String(!available));
        viewLink.tabIndex = available ? 0 : -1;
    }

    if (downloadLink) {
        downloadLink.classList.toggle('disabled', !available);
        downloadLink.setAttribute('aria-disabled', String(!available));
        downloadLink.tabIndex = available ? 0 : -1;
    }

    if (printButton) {
        printButton.disabled = !available;
    }
};

function renderSubmission(submission) {
    if (!submission) {
        return;
    }

    const periodLabel = formatPeriod(submission.period_start, submission.period_end);

    if (selectedPeriod) {
        selectedPeriod.textContent = periodLabel;
    }

    if (submissionId) {
        submissionId.textContent = `#${submission.submission_id}`;
    }

    if (assignmentCount) {
        assignmentCount.textContent = String(submission.assignment_count);
    }

    if (totalHours) {
        totalHours.textContent = String(submission.total_hours);
    }

    if (submittedAt) {
        submittedAt.textContent = viewableDateTimeHelper(submission.submitted_at, 'datetime');
    }

    const pdfAvailable = submission.pdf_available === true;

    setPdfActionsAvailable(pdfAvailable);

    if (!pdfAvailable) {
        viewLink?.removeAttribute('href');
        downloadLink?.removeAttribute('href');
        return;
    }

    const inlineUrl = buildPdfUrl(submission.submission_id, 'inline');
    const downloadUrl = buildPdfUrl(submission.submission_id, 'download');

    if (viewLink) {
        viewLink.href = inlineUrl;
    }

    if (downloadLink) {
        downloadLink.href = downloadUrl;
    }

    if (printButton) {
        printButton.onclick = () => {
            const printWindow = window.open(inlineUrl, '_blank', 'noopener,noreferrer');

            if (!printWindow) {
                showFlashAlert('warning', 'Please allow pop-ups to open the Timesheet PDF for printing.');
            }
        };
    }
};

function renderPeriodOptions() {
    if (!periodSelect) {
        return;
    }

    const fragment = document.createDocumentFragment();

    submissions.forEach((submission) => {
        const option = document.createElement('option');
        option.value = String(submission.submission_id);
        option.textContent = formatPeriod(submission.period_start, submission.period_end);

        fragment.appendChild(option);
    });

    periodSelect.replaceChildren(fragment);
};

function showHistoryError(message) {
    hideElement(loadingState);
    hideElement(emptyState);
    hideElement(historyContent);

    if (errorState) {
        errorState.textContent = message || 'Timesheet history could not be loaded.';
        showElement(errorState);
    }
};

function showEmptyHistory() {
    hideElement(loadingState);
    hideElement(errorState);
    hideElement(historyContent);
    showElement(emptyState);
};

async function loadTimesheetHistory() {
    try {
        const response = await proDriverRequest('/get-timesheet-history', {
                method: 'GET',
                cache: 'no-store',
                headers: {
                    'X-CSRF-Token': drvrToken
                }
            }
        );

        if (!response || response.status !== 'success' || !response.historyData) {
            throw new Error(response?.message || 'Invalid Timesheet history response.');
        }

        submissions = Array.isArray(response.historyData.submissions) ? response.historyData.submissions : [];

        if (!submissions.length) {
            showEmptyHistory();
            return;
        }

        renderPeriodOptions();
        renderSubmission(submissions[0]);

        hideElement(loadingState);
        hideElement(errorState);
        hideElement(emptyState);
        showElement(historyContent);
    } catch (error) {
        console.error('[TIMESHEET HISTORY] Failed loading history:', error);
        showHistoryError(error?.message || 'Timesheet history could not be loaded.');
    }
};

periodSelect?.addEventListener('change', () => {
    const selectedSubmissionId = Number(periodSelect.value);
    const submission = submissions.find((record) => record.submission_id === selectedSubmissionId);

    renderSubmission(submission);
});

setPdfActionsAvailable(false);
loadTimesheetHistory();