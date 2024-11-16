<?php
if (!empty($_GET["status"] === 'complete') {
    header('refresh:5');
})
if (isset($_POST['loginAcct'])) {
    // Getting the info from the form using POST method from the name attribute.
    $username = $_POST['username'];
    $password = $_POST['password'];
    

    // Instantiate the add user controller class. ↓
    include_once "../models/database.php";
    include_once "../models/drvrloginmeth.php";
    include_once "../controllers/drvrlogin.php";
    $signin = new Logincontr($username, $password);
    // Running error handlers and user signup.
    $signin->loginDriver();
    // Going to back to front page.
    header("Location: ../../public/views/home.php?error=none&status=official");
}

?>