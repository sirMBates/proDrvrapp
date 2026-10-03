<?php

declare(strict_types=1);

namespace App\Middleware;

class ScriptsMiddleware {
    public static function resolve(string $uri): array {
        // If router flagged an error page, load nothing
        if (!empty($_SERVER['IS_ERROR_PAGE'])) {
            return [];
        }

        // Public pages (no clock/main)
        $publicPages = [
            '/signup',
            '/register',
            '/signin',
            '/forget',
            '/reset-password',
            '/signature'
        ];

        // If public page → only page-specific scripts
        if (in_array($uri, $publicPages)) {
            return self::pageScripts($uri);
        }

        // Otherwise → inside the app
        $scripts = [
            '/dist/js/clock.js',
            '/dist/js/main.js'
        ];

        // Add page-specific scripts
        return array_merge($scripts, self::pageScripts($uri));
    }

    private static function pageScripts(string $uri): array {
        switch ($uri) {
            case '/signup':
                return ['/dist/js/signup.js'];

            case '/register':
                return ['/dist/js/registerprofilehandler.js'];

            case '/signin':
                return ['/dist/js/signin.js'];

            case '/forget':
                return ['/dist/js/forget.js'];

            case '/reset-password':
                return ['/dist/js/reset.js'];

            case '/':
                return ['/dist/js/home.js'];

            case '/contact':
                return ['/dist/js/contacthandler.js'];

            case '/counter':
                return ['/dist/js/ticker.js'];

            case '/assignments':
                $scripts = ['/dist/js/assignment.js'];
                if (!empty($_SESSION['signature_required'])) {
                    $scripts[] = '/dist/js/sign.js';
                }
                return $scripts;

            case '/message-center':
                return ['/dist/js/messages.js'];

            case '/timesheet':
                return ['/dist/js/timesheet.js'];
            
            case '/timesheet-review':
                return ['/dist/js/timesheet-review.js'];

            case '/profile':
                return ['/dist/js/profilehandler.js'];

            case '/signature':
                return ['/dist/js/signature-access.js'];

            default:
                return [];
        }
    }
}