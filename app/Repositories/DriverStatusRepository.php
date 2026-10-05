<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\DriverStatus;
use App\Validation\Validator;
use InvalidArgumentException;
use Core\Database;
use PDO;

class DriverStatusRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
    }

    public function createStatus(int $driverId, string $status, ?string $operationalDate = null): int {
        $sql = "INSERT INTO driver_status (driver_id, status, operational_date)
                VALUES (:driver_id, :status, :operational_date)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':driver_id' => $driverId,
            ':status' => $status,
            ':operational_date' => $operationalDate
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function findLatestByDriverId(int $driverId): ?array {
        $sql = "SELECT status_id, driver_id, status, operational_date, status_timestamp
                FROM driver_status
                WHERE driver_id = :driver_id
                ORDER BY status_timestamp DESC, status_id DESC
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':driver_id' => $driverId
        ]);

        $driverStatus = $stmt->fetch();

        return $driverStatus !== false ? $driverStatus : null;
    }

    public function findRecentByDriverId(int $driverId, int $limit = 20): array {
        $sql = "SELECT status_id, driver_id, status, operational_date, status_timestamp
                FROM driver_status
                WHERE driver_id = :driver_id
                ORDER BY status_timestamp DESC, status_id DESC
                LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':driver_id', $driverId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findAvailableAssignmentStatuses(int $driverId, string $verificationAnchor): array {
        $verificationAnchor = trim($verificationAnchor);

        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        $anchorDateTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $verificationAnchor);
        $dateErrors = \DateTimeImmutable::getLastErrors();

        if ($anchorDateTime === false || $anchorDateTime->format('Y-m-d H:i:s') !== $verificationAnchor || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
            throw new InvalidArgumentException('Invalid assignment status verification anchor.');
        }

        $sql = "SELECT ds.status_id, ds.driver_id, ds.status, ds.operational_date, ds.status_timestamp
                FROM driver_status AS ds
                WHERE ds.driver_id = :driver_id
                AND ds.status_timestamp >= :verification_anchor
                AND ds.status_timestamp <= CURRENT_TIMESTAMP
                AND ds.status IN (:arrived_at_location, :on_assignment)
                AND NOT EXISTS (
                    SELECT 1
                    FROM work_orders AS wo
                    WHERE wo.arrived_location_status_id = ds.status_id
                        OR wo.on_assignment_status_id = ds.status_id)
                ORDER BY
                    ds.status_timestamp ASC,
                    ds.status_id ASC";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':driver_id' => $driverId,
            ':verification_anchor' => $verificationAnchor,
            ':arrived_at_location' => DriverStatus::ARRIVED_AT_LOCATION->value,
            ':on_assignment' => DriverStatus::ON_ASSIGNMENT->value
        ]);

        return $stmt->fetchAll();
    }

    public function findEndOfShiftForPeriod(int $driverId, string $periodStart, string $periodEnd): array {
        $periodStart = trim($periodStart);
        $periodEnd = trim($periodEnd);

        if ($driverId < 1) {
            throw new InvalidArgumentException('Invalid driver ID.');
        }

        if (!Validator::date($periodStart) || !Validator::date($periodEnd)) {
            throw new InvalidArgumentException('Invalid End-of-Shift period.');
        }

        if ($periodStart > $periodEnd) {
            throw new InvalidArgumentException('The status period start cannot be after its end.');
        }

        $sql = "SELECT status_id, driver_id, status, operational_date, status_timestamp
                FROM driver_status
                WHERE driver_id = :driver_id
                AND status = :status
                AND operational_date >= :period_start
                AND operational_date <= :period_end
                ORDER BY operational_date ASC, status_timestamp ASC, status_id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':driver_id' => $driverId,
            ':status' => DriverStatus::END_OF_SHIFT->value,
            ':period_start' => $periodStart,
            ':period_end' => $periodEnd
        ]);

        return $stmt->fetchAll();
    }
}

?>