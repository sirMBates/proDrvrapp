<?php

namespace core;

class Flash {
	public static function setMsg($key, $msg) {
		$_SESSION['flash'][$key] = $msg; // Store message in session
	}

	public static function getMsg($key) {
		if (isset($_SESSION['flash'][$key])) {
			$msg = $_SESSION['flash'][$key]; // Retrieve message from session
            unset($_SESSION['flash'][$key]); // Remove after displaying
			return $msg;
		}
		return null; // Return null if no message is set
	}

    public static function displayType($type) {
        $qKeys = array_keys($type);
        $getValues = array_values($qKeys);
        $typeDisplay = $getValues[1];
        //print_r($qKeys);
        echo $typeDisplay;
    }

    public static function getMsgType() {
        echo "<script>
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);

        </script>";
    }

    public static function displayMsg() {
        if (isset($_GET['success']) && $_GET['success'] === 'acct-created') {
            return 'Account created successfully! Please enter additional information to complete your profile.';
        } else if (isset($_GET['success']) && $_GET['success'] === 'acct-updated') {
            return 'Account updated successfully! Please log in to continue.';
        } else if (isset($_GET['warning']) && $_GET['warning'] === 'invalid-password') {
            return 'invalid-password. Please try again.';
        } else if (isset($_GET['warning']) && $_GET['warning'] === 'invalid-username') {
            return 'invalid-username. Please try again.';
        } else if (isset($_GET['warning']) && $_GET['warning'] === 'empty-input') {
            return 'Please fill in all required fields.';
        } else if (isset($_GET['danger']) && $_GET['danger'] === 'email-exists') {
            return 'Email already exists! Please use a different email address.';
        } else if (isset($_GET['danger']) && $_GET['danger'] === 'username-exists') {
            return 'Username already exists! Please choose a different username.';
        } else if (isset($_GET['info']) && $_GET['info'] === 'profile-updated') {
            return 'Driver profile updated successfully!';
        } else {
            return 'An unexpected error occurred. Please try again.';
        }
    }

	/*public function displayMsg($query) {
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
    }*/
	
	public static function iconType($type) {
        $qKeys = array_keys($type);
        $getValues = array_values($qKeys);
        //print_r($getValues);
        $typeDisplay = $getValues[1]; // Get the first value from the array
		switch($typeDisplay) {
			case 'success':
				echo ' fa-thumbs-up';
				break;
			case 'danger':
				echo ' fa-circle-radiation';
				break;
		 	case 'warning':
				echo ' fa-triangle-radiation';
				break;
			case 'info':
				echo ' fa-circle-info';
				break;
			default:
				echo ' fa-thumbs-down';
				break;
		}
	}
}
?>