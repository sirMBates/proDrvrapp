<?php

namespace core;

class Flalert {
    public static function displayType($type) {
        switch($type) {
            case 'danger':
                return 'danger';
                break;
            case 'success':
                return 'success';
                break;
            case 'warning':
                return 'warning';
                break;
            case 'info':
                return 'info';
                break;
            default:
                return '';
        }
    }

    public static function messageType($string) {
        //$qString = $_SERVER['QUERY_STRING'];
        parse_str($string, $queryParams);
        foreach ($queryParams as $key => $value) {
            return [
                'kParam' => htmlspecialchars($key),
                'kValue' => htmlspecialchars($value)
                //echo "Parameter: ".htmlspecialchars($key).", Value: $value<br>";
            ];
        }
    
        $qValue = $queryParams['kValue'];

        switch($qValue[1]) {
            case 'acct-created':
                return 'acct-created';
                break;
            case 'acct-updated':
                return 'acct-updated';
                break;
            case 'invalid-password':
                return 'invalid-password';
                break;
            case 'invalid-username':
                return 'invalid-username';
                break;
            case 'email-exists':
                return 'email-exists';
                break;
            case 'username-exists':
                return 'username-exists';
                break;
            case 'profile-updated':
                return 'profile-updated';
                break;
            default:
                return 'unexpected-error';
                break;
        }
    }

    public static function displayMsg($msg) {
        switch($msg) {
            case 'acct-created':
                return 'Account created successfully! Please enter additional information to complete your profile.';
                break;
            case 'acct-updated':
                return 'Account updated successfully! Please log in to continue.';
                break;
            case 'invalid-password':
                return 'Invalid password. Please try again.';
                break;
            case 'invalid-username':
                return 'Invalid username. Please try again.';
                break;
            case 'email-exists':
                return 'Email already exists. Please use a different email address.';
                break;
            case 'username-exists':
                return 'Username already exists. Please choose a different username.';
                break;
            case 'profile-updated':
                return 'Driver profile updated successfully!';
                break;
            default:
                return 'An unexpected error occurred. Please try again.';
                break;
        }
    }
}