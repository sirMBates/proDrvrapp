<?php

namespace core;

class Flash {
	public function setMsg($key, $msg) {
		$_SESSION['flash'][$key] = $msg; // Store message in session
	}

	public function getMsg($key) {
		if (isset($_SESSION['flash'][$key])) {
			$msg = $_SESSION['flash'][$key];
			unset($_SESSION['flash'][$key]); // Remove after displaying
			return $msg;
		}
		return null; // Return null if no message is set
	}

    public static function displayType($type) {
        $qKeys = array_keys($type);
        $getValues = array_values($qKeys);
        //print_r($getValues);
        $typeDisplay = $getValues[1];
        switch($typeDisplay) {
            case 'success':
                echo 'success';
                break;
            case 'danger':
                echo 'danger';
                break;
            case 'warning':
                echo 'warning';
                break;
            case 'info':
                echo 'info';
                break;
            default:
                echo 'error';
                break;
        }
    }

    public static function alertType($type) {
		$qKeys = array_keys($type);
        $getValues = array_values($qKeys);
        //print_r($getValues);
        $typeDisplay = $getValues[1]; // Get the first value from the array
        switch($typeDisplay) {
            case 'success':
                echo 'success';
                break;
            case 'danger':
                echo 'danger';
                break;
            case 'warning':
                echo 'warning';
                break;
            case 'info':
                echo 'info';
                break;
            default:
                echo 'dark';
                break;
    	}
	}

	public function displayMsg($query) {
        //$msg = $this->messageTypeAndDisplay();
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
	
	public static function iconType($type) {
        $qKeys = array_keys($type);
        $getValues = array_values($qKeys);
        //print_r($getValues);
        $typeDisplay = $getValues[1]; // Get the first value from the array
		switch($typeDisplay) {
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