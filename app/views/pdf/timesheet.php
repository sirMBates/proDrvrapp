<?php

declare(strict_types=1);

$timezoneName = (string) ($document['timezone'] ?? 'America/New_York');
$timezone = new DateTimeZone($timezoneName);

$escape = static function (mixed $value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

$formatDate = static function (mixed $value) use ($timezone): string {
    $value = trim((string) ($value ?? ''));

    if ($value === '') {
        return '—';
    }

    try {
        return (new DateTimeImmutable($value, $timezone))->format('m/d/Y');
    } catch (Throwable) {
        return '—';
    }
};

$formatDateTime = static function (mixed $value) use ($timezone): string {
    $value = trim((string) ($value ?? ''));

    if ($value === '') {
        return '—';
    }

    try {
        return (new DateTimeImmutable($value, $timezone))->format('m/d/Y g:i A');
    } catch (Throwable) {
        return '—';
    }
};

$formatTime = static function (mixed $value) use ($timezone): string {
    $value = trim((string) ($value ?? ''));

    if ($value === '') {
        return '—';
    }

    try {
        return (new DateTimeImmutable($value, $timezone))->format('g:i A');
    } catch (Throwable) {
        return '—';
    }
};

$formatYesNo = static function (mixed $value): string {
    if ($value === true || $value === 1 || $value === '1') {
        return 'Yes';
    }

    if ($value === false || $value === 0 || $value === '0') {
        return 'No';
    }

    return 'Not answered';
};

$formatMoney = static function (mixed $value): string {
    if ($value === null || $value === '') {
        return 'Pending';
    }

    return '$' . number_format((float) $value, 2);
};

$driver = $document['driver'];
$driverName = trim((string) $driver['first_name'] . ' ' . (string) $driver['last_name']);

$submissionId = (int) ($document['submission_id'] ?? 0);
$submittedAt = $document['submitted_at'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            margin: 0.35in;
        }

        body {
            color: #202124;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 8px;
            line-height: 1.3;
        }

        h1, h2, p {
            margin: 0;
        }

        .document-header {
            border-bottom: 3px solid #1d5283;
            margin-bottom: 12px;
            padding-bottom: 8px;
        }

        .document-title {
            color: #1d5283;
            font-size: 20px;
            text-transform: uppercase;
        }

        .document-subtitle {
            color: #555;
            font-size: 10px;
            margin-top: 2px;
        }

        .summary-table {
            border-collapse: collapse;
            margin-bottom: 12px;
            width: 100%;
        }

        .summary-table td {
            border: 1px solid #b7c1cb;
            padding: 6px;
            vertical-align: top;
            width: 25%;
        }

        .summary-label {
            color: #59636e;
            display: block;
            font-size: 7px;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .summary-value {
            font-size: 9px;
            font-weight: bold;
        }

        .day-section {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .day-heading {
            background: #1d5283;
            color: #fff;
            font-size: 10px;
            padding: 6px 8px;
        }

        .entry-table {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        .entry-table th,
        .entry-table td {
            border: 1px solid #9fa9b3;
            overflow-wrap: break-word;
            padding: 4px;
            text-align: center;
            vertical-align: top;
        }

        .entry-table th {
            background: #dcebf7;
            color: #172b3d;
            font-size: 6.5px;
            text-transform: uppercase;
        }

        .entry-table td {
            font-size: 6.5px;
        }

        .text-left {
            text-align: left !important;
        }

        .order-control {
            color: #666;
            display: block;
            font-size: 5.5px;
            margin-top: 2px;
        }

        .day-summary {
            background: #edf3f8;
            border: 1px solid #9fa9b3;
            border-top: 0;
            padding: 5px 7px;
            text-align: right;
        }

        .day-summary span {
            margin-left: 18px;
        }

        .footer-note {
            border-top: 1px solid #aeb7c0;
            color: #626b74;
            font-size: 7px;
            margin-top: 10px;
            padding-top: 6px;
        }
    </style>
</head>

<body>
    <header class="document-header">
        <h1 class="document-title">ProDriver Timesheet</h1>

        <p class="document-subtitle">
            Official submitted weekly assignment summary
        </p>
    </header>

    <table class="summary-table">
        <tr>
            <td>
                <span class="summary-label">Driver</span>
                <span class="summary-value">
                    <?= $escape($driverName) ?>
                </span>
            </td>

            <td>
                <span class="summary-label">Pay Period</span>
                <span class="summary-value">
                    <?= $escape($formatDate($document['period_start'])) ?>
                    –
                    <?= $escape($formatDate($document['period_end'])) ?>
                </span>
            </td>

            <td>
                <span class="summary-label">Assignments</span>
                <span class="summary-value">
                    <?= (int) $document['assignment_count'] ?>
                </span>
            </td>

            <td>
                <span class="summary-label">Period Total Hours</span>
                <span class="summary-value">
                    <?= $escape($document['period_total_hours']) ?>
                </span>
            </td>
        </tr>

        <?php if ($submissionId > 0 || $submittedAt !== null): ?>
            <tr>
                <td colspan="2">
                    <span class="summary-label">Submission Reference</span>
                    <span class="summary-value">
                        <?= $submissionId > 0 ? $submissionId : 'Pending' ?>
                    </span>
                </td>

                <td colspan="2">
                    <span class="summary-label">Submitted At</span>
                    <span class="summary-value">
                        <?= $escape($formatDateTime($submittedAt)) ?>
                    </span>
                </td>
            </tr>
        <?php endif; ?>
    </table>

    <?php foreach ($document['days'] as $day): ?>
        <section class="day-section">
            <h2 class="day-heading">
                <?= $escape($formatDate($day['assignment_date'])) ?>
            </h2>

            <table class="entry-table">
                <thead>
                    <tr>
                        <th style="width: 7%;">Order</th>
                        <th style="width: 13%;">From / To</th>
                        <th style="width: 6%;">Bus</th>
                        <th style="width: 10%;">Garage Report</th>
                        <th style="width: 7%;">Spot</th>
                        <th style="width: 9%;">Drop</th>
                        <th style="width: 9%;">End</th>
                        <th style="width: 16%;">Job Details</th>
                        <th style="width: 6%;">Hours</th>
                        <th style="width: 5%;">Tolls</th>
                        <th style="width: 5%;">Tip</th>
                        <th style="width: 7%;">Pay</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($day['entries'] as $entry): ?>
                        <tr>
                            <td>
                                <?= (int) $entry['order_id'] ?>

                                <span class="order-control">
                                    <?= $escape($entry['assignment_control']) ?>
                                </span>
                            </td>

                            <td class="text-left">
                                <strong>From:</strong>
                                <?= $escape($entry['origin'] ?: '—') ?>
                                <br>
                                <strong>To:</strong>
                                <?= $escape($entry['destination'] ?: '—') ?>
                            </td>

                            <td><?= $escape($entry['vehicle_id']) ?></td>

                            <td>
                                <?= $escape($formatDateTime($entry['garage_report_at'])) ?>
                            </td>

                            <td>
                                <?= $escape($formatTime($entry['spot_time'])) ?>
                            </td>

                            <td>
                                <?= $escape($formatDateTime($entry['actual_drop_time'])) ?>
                            </td>

                            <td>
                                <?= $escape($formatDateTime($entry['actual_end_time'])) ?>
                            </td>

                            <td class="text-left">
                                <?= nl2br($escape($entry['job_details'] ?: '—')) ?>
                            </td>

                            <td>
                                <?= $escape($entry['total_job_time']) ?>
                            </td>

                            <td>
                                <?= $escape($formatYesNo($entry['tolls_used'])) ?>
                            </td>

                            <td>
                                <?= $escape($formatYesNo($entry['tip'])) ?>
                            </td>

                            <td>
                                <?= $escape($formatMoney($entry['job_pay'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="day-summary">
                <span>
                    <strong>End of Duty:</strong>
                    <?= $escape($formatDateTime($day['end_of_duty'])) ?>
                </span>

                <span>
                    <strong>Total Shift Hours:</strong>
                    <?= $escape($day['total_shift_hours']) ?>
                </span>
            </div>
        </section>
    <?php endforeach; ?>

    <p class="footer-note">
        This document was generated from locked ProDriver assignment
        snapshots for the stated pay period.
    </p>
</body>
</html>