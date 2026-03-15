<?php

namespace core;

class Flash {
	public static function setMsg($key, $msg, $options = []) {
		$_SESSION['flash'][$key] = [ // Store message in session
			'message' => $msg,
			'options' => $options
		];
	}

	public static function getMsg($key) {
		if (isset($_SESSION['flash'][$key])) {
			$flash = $_SESSION['flash'][$key]; // Retrieve message from session
            unset($_SESSION['flash'][$key]); // Remove after displaying
			return $flash;
		}
		return null; // Return null if no message is set
	}
}
?>