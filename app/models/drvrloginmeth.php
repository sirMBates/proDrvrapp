<?php
use core\Flash;

class Login extends ConnectDatabase {
    protected function getDriver($username, $password) {
        $alert = new Flash();
        $sql = "SELECT password FROM driver
                WHERE username = :username OR email = :email";
        $stmt = $this->connect()->prepare($sql);
        
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $password);
        $stmt->execute();        

        if (!$stmt || $stmt->rowCount() === 0) {
            $alert::setMsg('error', 'User not found. Please check your username or email.');
            header("Location: /signin?error=not+found"); // noRegisteredUseraccount
            exit();
        }

        $hashedPsw = $stmt->fetchAll();
        $checkPsw = password_verify($password, $hashedPsw[0]["password"]);
        
        if ($checkPsw === false) {
            $alert::setMsg('danger', 'Incorrect password. Please try again.');
            header("Location: /signin?danger=invalid"); // wrongPassword
            exit();
        } elseif ($checkPsw === true) {
            $sql2 = "SELECT * FROM driver
                    WHERE username = ? OR email = ? and PASSWORD = ?";
            $stmt = $this->connect()->prepare($sql2);
            $stmt->bindParam(1, $username);
            $stmt->bindParam(2, $email);
            $stmt->bindParam(3, $password);
            $stmt->execute();

            if (!$stmt || $stmt->rowCount() === 0) {
                $alert::setMsg('error', 'User not found. Please check your username or email.');
                header("Location: /signin?error=not+found"); // noRegisteredUseraccount
                exit();
            }

            $driver = $stmt->fetchAll();
            //session_start();
            $_SESSION['driver_id'] = $driver[0]['driverid'];
            $_SESSION['username'] = $driver[0]['username'];
            $_SESSION['first_name'] = $driver[0]['firstName'];
            $_SESSION['last_name'] = $driver[0]['lastName'];
            $currentDate = date('md');
            $drvrDate = date('md', strtotime($driver[0]['birthdate']));
            if ($currentDate === $drvrDate) {
                $_SESSION['birth_date'] = $driver[0]['birthdate'];
            }
            $_SESSION['mobile_number'] = $driver[0]['mobileNumber'];

            $stmt = null;
        }

        $stmt = null;
    }
}
?>