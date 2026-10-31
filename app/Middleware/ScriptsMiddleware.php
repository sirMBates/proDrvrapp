<?php

declare(strict_types=1);

namespace App\Middleware;

class ScriptsMiddleware {
    public static function resolve(string $uri): array {
        // If router flagged an error page, load nothing
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
            return self::authScripts($uri);
        }

        // Public/customer-facing pages
        $publicPages = [
            '/signature'
        ];

        if (in_array($uri, $publicPages, true)) {
            return self::publicScripts($uri);
        }

        // Authenticated Driver application
        $scripts = [
            '/dist/js/shared/clock.js',
            '/dist/js/driver/main.js'
        ];

        return array_merge($scripts, self::driverScripts($uri));
    }

    private static function authScripts(string $uri): array {
        switch ($uri) {
            case '/signup':
                return ['/dist/js/auth/signup.js'];

            case '/register':
                return ['/dist/js/auth/register-onboarding.js'];

            case '/signin':
                return ['/dist/js/auth/signin.js'];

            case '/forget':
                return ['/dist/js/auth/forget.js'];

            case '/reset-password':
                return ['/dist/js/auth/reset.js'];

            default:
                return [];
        }
    }

    private static function publicScripts(string $uri): array {
        switch ($uri) {
            case '/signature':
                return ['/dist/js/public/signature-access.js'];

            default:
                return [];
        }
    }

    private static function driverScripts(string $uri): array {
        switch ($uri) {
            case '/':
                return ['/dist/js/driver/dashboard.js'];

            case '/counter':
                return ['/dist/js/driver/ticker.js'];

            case '/preferences':
                return ['/dist/js/driver/preferences.js'];

            case '/assignments':
                $scripts = ['/dist/js/driver/assignment.js'];

                if (!empty($_SESSION['signature_required'])) {
                    $scripts[] = '/dist/js/driver/sign.js';
                }

                return $scripts;

            case '/message-center':
                return ['/dist/js/driver/messages.js'];

            case '/timesheet':
                return ['/dist/js/driver/timesheet.js'];

            case '/timesheet-review':
                return ['/dist/js/driver/timesheet-review.js'];

            case '/submitted-timesheets':
                return ['/dist/js/driver/timesheet-history.js'];

            case '/profile':
                return ['/dist/js/driver/profile.js'];

            default:
                return [];
        }
    }
}

?>