<?php
    $title = "Pro Driver - Reset Password";
    require_once "../includes/head.php";
?>

<body class="d-flex flex-column justify-content-center noprint vh-100">
    <main class="w-50 mb-3 align-self-center">
        <h1 class="text-center"><u>Reset your password here!</u></h1>
        <p class="h5 text-center">You will receive an email with further instructions on resetting your password.</p>
        <form id="resetpswd" action="../../app/controllers/reset-password.php" method="POST" class="needs-validation" novalidate>
            <div class="form-floating row mb-2">
                <input id="email-for-pswdreset" type="email" inputmode="email" class="form-control" placeholder="Please enter your email" required>
                <label for="email-for-pswdreset">Email address</label>
            </div>
            <div class="row mb-2">
                <button type="button" class="btn btn-primary" name="reset-pswd">Send email</button>
                <button type="button" class="btn btn-primary" name="cancel">Go back</button>
            </div>
        </form>
    </main> 
<?php
    include_once "../includes/footer.php";
    include_once "../includes/getscripts.php";
?>
</body>