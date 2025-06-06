<?php

namespace core;

class Flash {
    public function checkForKey() {
        if (isset($_GET[$key])) {
			return $key;
		}
    }
	
	public function msgType() {
		$type = checkForKey();
		if ($type === "danger") {
			$class = 'alert alert-danger alert-dismissible';
		} elseif ($type === "success") {
			$class = 'alert alert-success alert-dismissible';
		} elseif ($type === "warning") {
			$class = 'alert alert-warning alert-dismissible';
		} elseif ($type === "info") {
			$class = 'alert alert-info alert-dismissible';
		}
	}
	
	public function iconType() {
		$type = checkForKey();
		if ($type === "danger") {
			$class = 'me-2 fa-solid fa-circle-radiation';
		} elseif ($type === "success") {
			$class = 'me-2 fa-solid fa-thumbs-up';
		} elseif ($type === "warning") {
			$class = 'me-2 fa-solid fa-triangle-radiation';
		} elseif ($type === "info") {
			$class = 'me-2 fa-solid fa-circle-info';
		}
	}
	
	public static function setMsg($key, $msg) {
		$param = checkForKey();
		if ($param === "danger") {
			$_SESSION['flash'][$key] = $msg;
		} elseif ($param === "success") {
			$_SESSION['flash'][$key] = $msg;
		} elseif ($param === "warning") {
			$_SESSION['flash'][$key] = $msg;
		} elseif ($param === "info") {
			$_SESSION['flash'][$key] = $msg;
		}
	}

	Public static function getMsg($key) {
		if (isset($_SESSION['flash'][$key])) {
			$msg = $_SESSION['flash'][$key];
			unset($_SESSION['flash'][$key]); // Remove after displaying
			return $msg;
		}
		return null; // Return null if no message is set
	}
}
?>