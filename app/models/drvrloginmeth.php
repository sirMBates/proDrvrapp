<?php
use core\Flash;

class Login extends ConnectDatabase {
    protected function getDriver($username, $password) {
        $alert = new Flash();
        $stmt =$this->connect()->prepare("SELECT password FROM driver WHERE username = ? OR email = ?;");

        if (!$stmt->execute(array($username, $password))) {
            $stmt = null;
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /signin?error=try+again"); // unexpectedError
            exit();
        }

        if ($stmt->rowCount() === 0) {
            $stmt = null;
            $alert::setMsg('error', 'User not found. Please check your username or email.');
            header("Location: /signin?error=not+found"); // noRegisteredUseraccount
            exit();
        }

        $hashedPsw = $stmt->fetchAll();
        $checkPsw = password_verify($password, $hashedPsw[0]["password"]);
        
        if ($checkPsw === false) {
            $stmt = null;
            $alert::setMsg('danger', 'Incorrect password. Please try again.');
            header("Location: /signin?danger=invalid"); // wrongPassword
            exit();
        }

        elseif ($checkPsw === true) {
            $stmt =$this->connect()->prepare("SELECT * FROM driver WHERE username = ? OR email = ? AND password = ?;");

            if (!$stmt->execute(array($username, $username, $password))) {
                $stmt = null;
                $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
                header("Location: /signin?error=try+again"); // unexpectedError
                exit();
            }

            if ($stmt->rowCount() === 0) {
                $stmt = null;
                $alert::setMsg('error', 'User not found. Please check your username or email.');
                header("Location: /signin?error=not+found"); // noRegisteredUseraccount
                exit();
            }

            $driver = $stmt->fetchAll();
            session_start();
            $_SESSION['driver_id'] = $driver[0]['driverid'];
            $_SESSION['username'] = $driver[0]['username'];
            $_SESSION['first_name'] = $driver[0]['firstName'];
            $_SESSION['last_name'] = $driver[0]['lastName'];
            $_SESSION['birth_date'] = $driver[0]['birthdate'];
            $_SESSION['mobile_number'] = $driver[0]['mobileNumber'];

            $stmt = null;
        }

        $stmt = null;
    }
}
?>