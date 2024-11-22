<?php
if (isset($_POST['loginAcct'])) {
    // Getting the info from the form using POST method from the name attribute.
    $username = $_POST['username'];
    $password = $_POST['password'];
    // Instantiate the sign in user controller class. ↓
    include_once "../models/database.php";
    include_once "../models/drvrloginmeth.php";
    include_once "../controllers/drvrlogin.php";
    $signin = new Logincontr($username, $password);
    // Running error handlers and user signin.
    $signin->loginDriver();
    // Going to home page.
    header("Location: ../../public/views/home.php?error=none&status=official");
    exit();
}

?>