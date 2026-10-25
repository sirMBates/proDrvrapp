<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;

class PreferenceService {
    private const DEFAULTS = [
        'theme' => 'auto',
        'show_page_guidance' => true,
        'status_dock_expanded' => true,
    ];

    private UserRepository $userRepository;

    public function __construct(?UserRepository $userRepository = null) {
        $this->userRepository = $userRepository ?? new UserRepository();
    }

    public function getDefaults(): array {
        return self::DEFAULTS;
    }

    public function getPreferences(int $userId): array {
        if ($userId < 1) {
            throw new \InvalidArgumentException('Invalid user ID.');
        }

        $storedPreferences = $this->userRepository->findPreferencesByUserId($userId);

        if ($storedPreferences === null || $storedPreferences === '') {
            return self::DEFAULTS;
        }

        try {
            $decodedPreferences = json_decode($storedPreferences, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Stored user preferences contain invalid JSON.', 0, $e);
        }

        if (!is_array($decodedPreferences)) {
            throw new \RuntimeException('Stored user preferences must contain a JSON object.');
        }

        return array_replace(self::DEFAULTS, $decodedPreferences);
    }

    public function updatePreference(int $userId, string $preference, mixed $value): array {
        if ($userId < 1) {
            throw new \InvalidArgumentException('Invalid user ID.');
        }

        if (!array_key_exists($preference, self::DEFAULTS)) {
            throw new \InvalidArgumentException('Unknown preference.');
        }

        switch ($preference) {
            case 'theme':
                if (!is_string($value) || !in_array($value, ['auto', 'light', 'dark'], true)) {
                    throw new \InvalidArgumentException('Invalid theme preference.');
                }
                break;

            case 'show_page_guidance':
            case 'status_dock_expanded':
                if (!is_bool($value)) {
                    throw new \InvalidArgumentException('Preference value must be a boolean.');
                }
                break;
        }

        $preferences = $this->getPreferences($userId);
        $preferences[$preference] = $value;

        try {
            $encodedPreferences = json_encode($preferences, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Unable to encode user preferences.', 0, $e);
        }

        $updated = $this->userRepository->updatePreferences($userId, $encodedPreferences);
        if (!$updated) {
            throw new \RuntimeException('Unable to update user preferences.');
        }

        return $preferences;
    }
}

?>