<?php

namespace core;

class Flash {
	public function setMsg($key, $type, $msg) {
		$_SESSION['flash'][$key][$type] = $msg; // Store message in session
	}

	public function getMsg($key, $type) {
		if (isset($_SESSION['flash'][$key][$type])) {
			$msg = $_SESSION['flash'][$key][$type];
			unset($_SESSION['flash'][$key][$type]); // Remove after displaying
			return $msg;
		}
		return null; // Return null if no message is set
	}

    public static function displayType($type) {
		$qKeys = array_keys($type);
        //echo $qKeys[1];
        switch($qKeys[1]) {
			case 'success':
                return 'success';
                break;
            case 'danger':
                return 'danger';
                break;
            case 'warning':
                return 'warning';
                break;
            case 'info':
                return 'info';
                break;
            default:
                return 'dark';
				break;
    	}
	}

	public static function messageType($string) {
		$queryArray = [];
        //$string = $_SERVER['QUERY_STRING'];
        parse_str($string, $queryParams);
        foreach ($queryParams as $key => $value) {
			$queryArray[$key] = $value;
		}
		$getQueryKey = $queryArray[$key];
		//echo $queryArray[$key];
		switch($getQueryKey) {
            case 'acct-created':
                echo 'acct-created';
                break;
            case 'acct-updated':
                echo 'acct-updated';
                break;
            case 'invalid-password':
                echo 'invalid-password';
                break;
            case 'invalid-username':
                echo 'invalid-username';
                break;
            case 'email-exists':
                echo 'email-exists';
                break;
            case 'username-exists':
                echo 'username-exists';
                break;
            case 'profile-updated':
                echo 'profile-updated';
                break;
            default:
                echo 'unexpected';
                break;
        }
	}

	public static function displayMsg($msg) {
        switch($msg) {
            case 'acct-created':
                echo 'Account created successfully! Please enter additional information to complete your profile.';
                break;
            case 'acct-updated':
                echo 'Account updated successfully! Please log in to continue.';
                break;
            case 'invalid-password':
                echo 'Invalid password. Please try again.';
                break;
            case 'invalid-username':
                echo 'Invalid username. Please try again.';
                break;
            case 'email-exists':
                echo 'Email already exists. Please use a different email address.';
                break;
            case 'username-exists':
                echo 'Username already exists. Please choose a different username.';
                break;
            case 'profile-updated':
                echo 'Driver profile updated successfully!';
                break;
			case 'empty-input':
				echo 'Please fill in all required fields.';
				break;
            default:
                echo 'An unexpected error occurred. Please try again.';
                break;
        }
    }
	
	public static function iconType($func) {
		switch($func) {
			case 'success':
				echo 'me-2 fa-solid fa-thumbs-up';
				break;
			case 'danger':
				echo 'me-2 fa-solid fa-circle-radiation';
				break;
		 	case 'warning':
				echo 'me-2 fa-solid fa-triangle-radiation';
				break;
			case 'info':
				echo 'me-2 fa-solid fa-circle-info';
				break;
			default:
				echo 'me-2 fa-solid fa-thumbs-down';
				break;
		}
	}
}
?>