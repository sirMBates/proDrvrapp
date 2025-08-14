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
			unset($_SESSION['first_name']);
			return $msg;
		}
		return null; // Return null if no message is set
	}
}
?>