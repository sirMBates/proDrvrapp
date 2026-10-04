<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

class PayPeriodService {
    public function getCurrentPeriod(int $weekEndsOn, string $timezone = 'America/New_York', ?DateTimeImmutable $now = null): array {
        if ($weekEndsOn < 1 || $weekEndsOn > 7) {
            throw new InvalidArgumentException('The pay-week ending day is invalid.');
        }

        $companyTimezone = new DateTimeZone($timezone);

        $currentDateTime = $now === null ? new DateTimeImmutable('now', $companyTimezone) : $now->setTimezone($companyTimezone);

        $currentDate = $currentDateTime->setTime(0, 0);
        $currentWeekday = (int) $currentDate->format('N');

        $daysUntilPeriodEnd = ($weekEndsOn - $currentWeekday + 7) % 7;

        $periodEnd = $currentDate->modify("+{$daysUntilPeriodEnd} days");

        $periodStart = $periodEnd->modify('-6 days');
        $submissionOpensAt = $periodEnd->setTime(0, 0);

        return [
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'submission_opens_at' => $submissionOpensAt->format('Y-m-d H:i:s'),
            'submission_available' => $currentDateTime >= $submissionOpensAt,
            'week_ends_on' => $weekEndsOn,
            'timezone' => $timezone
        ];
    }

    public function getPreviousPeriod(int $weekEndsOn, string $timezone = 'America/New_York', ?DateTimeImmutable $now = null): array {
        $currentPeriod = $this->getCurrentPeriod($weekEndsOn, $timezone, $now);
        $companyTimezone = new DateTimeZone($timezone);
        $currentPeriodStart = new DateTimeImmutable($currentPeriod['period_start'] . ' 00:00:00', $companyTimezone);
        /*
        * One second before the current period began belongs to the
        * immediately preceding authoritative pay period.
        */
        $previousPeriodReference = $currentPeriodStart->modify('-1 second');
        return $this->getCurrentPeriod($weekEndsOn, $timezone, $previousPeriodReference);
    }
}

?>