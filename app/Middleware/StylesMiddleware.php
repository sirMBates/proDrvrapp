<?php

declare(strict_types=1);

namespace App\Middleware;

class StylesMiddleware {
    public static function resolve(string $uri, ?string $role = null): array {
        // If router flagged an error page, load no page/role-specific styles.
        if (!empty($_SERVER['IS_ERROR_PAGE'])) {
            return [];
        }

        // Authentication/account pages
        $authPages = [
            '/signup',
            '/register',
            '/signin',
            '/forget',
            '/reset-password'
        ];

        if (in_array($uri, $authPages, true)) {
            return self::authStyles($uri);
        }

        // Public/customer-facing pages
        $publicPages = [
            '/signature'
        ];

        if (in_array($uri, $publicPages, true)) {
            return self::publicStyles($uri);
        }

        // Authenticated application styles are role-specific.
        return match ($role) {
            'driver' => self::driverStyles($uri),
            'dispatch' => self::dispatchStyles($uri),
            'admin' => self::adminStyles($uri),
            default => []
        };
    }

    private static function authStyles(string $uri): array {
        return match ($uri) {
            '/signup',
            '/signin',
            '/forget',
            '/reset-password'
                => ['/dist/styles/auth/auth.css'],

            '/register'
                => ['/dist/styles/auth/register.css'],

            default => []
        };
    }

    private static function publicStyles(string $uri): array {
        return match ($uri) {
            '/signature' => [],
            default => []
        };
    }

    private static function driverStyles(string $uri): array {
        $styles = [
            '/dist/styles/shared/application-shell.css',
            '/dist/styles/driver/status-dock.css'
        ];

        $pageStyles = match ($uri) {
            '/' => [
                '/dist/styles/driver/status-history.css',
                '/dist/styles/driver/dashboard.css'
            ],

            '/assignments' => [
                '/dist/styles/driver/assignments.css'
            ],

            '/message-center' => [
                '/dist/styles/shared/messages.css'
            ],

            '/timesheet',
            '/timesheet-review' => [
                '/dist/styles/driver/timesheet.css'
            ],

            '/profile' => [
                '/dist/styles/driver/profile.css'
            ],

            default => []
        };

        return array_merge($styles, $pageStyles);
    }

    private static function dispatchStyles(string $uri): array {
        $styles = [
            '/dist/styles/shared/application-shell.css'
        ];

        $pageStyles = match ($uri) {
            '/' => [
                '/dist/styles/dispatch/console.css'
            ],

            '/message-center' => [
                '/dist/styles/shared/messages.css'
            ],

            default => []
        };

        return array_merge($styles, $pageStyles);
    }

    private static function adminStyles(string $uri): array {
        $styles = [
            '/dist/styles/shared/application-shell.css'
        ];

        $pageStyles = match ($uri) {
            '/message-center' => [
                '/dist/styles/shared/messages.css'
            ],

            default => []
        };

        return array_merge($styles, $pageStyles);
    }
}

?>
