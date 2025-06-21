<?php

namespace core;

class Errormsg {
    public static function displayType($type) {
        switch($type) {
            case: 'danger':
                return 'danger';
                break;
            case: 'success':
                return 'success';
                break;
            case: 'warning':
                return 'warning';
                break;
            case: 'info':
                return 'info';
                break;
            default:
                return '';
        }
    }

    public static function displayMsg($msg) {
        switch($msg) {
            case 'acct-created':
                return 'Account created successfully! Please enter additional information to complete your profile.';
                break;
            case 'acct-updated':
                return 'Account updated successfully!';
                break;
            case 'acct-deleted':
                return 'Account deleted successfully!';
                break;
            case 'invalid-password':
                return 'Invalid password. Please try again.';
                break;
            default:
                return 'An unexpected error occurred. Please try again.';
        }
    }
}