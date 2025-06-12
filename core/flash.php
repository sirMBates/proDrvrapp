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

    /*public function checkForKey() {
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
	}*/
}
?>