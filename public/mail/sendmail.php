<?php
    $title = "Pro Driver - Send Email";
    require_once "../partials/header.php";
?>

<body class="d-flex flex-column min-vh-100 overflow-x-hidden noprint">
<?php
    include_once "../partials/navbar.php";
    include_once "../partials/topper.php";
?>
    <main class="container-fluid">
        <div class="card my-3" style="width: 100%;">
            <div class="card-header bg-besttrailsclr text-btd-white-floral">
                <h4 class="text-capitalize text-center">send email</h4>
            </div>
            <div class="card-body">
            <form id="email-form" class="needs-validation" name="emailform" action="" method="" novalidate>
                <div class="form-floating mb-3 position-relative">
                    <input type="email" class="form-control" id="drvr-email" name="sendingemailaddy" placeholder="Your email address" required>
                    <label for="drvr-email">Sender</label>
                </div>
                <div class="form-floating mb-3 position-relative">
                    <input type="email" class="form-control" id="dev-email" name="receivingemailaddy" placeholder="Admin email" required>
                    <label for="dev-email">Recipient</label>
                </div>
                <div class="form-floating position-relative">
                    <textarea id="body-msg" class="form-control" name="message" placeholder="Add your message here!" style="height: 200px;" required></textarea>
                    <label for="body-msg">Your message</label>
                </div>
            </div>
            <div class="card-footer text-capitalize text-center">
                <button type="button" name="sendmsg" class="btn btn-lg btn-primary">Send</button>
            </div>
            </form>
        </div>
    </main>

<?php
    include_once "../partials/footer.php";
    require_once "../partials/getscripts.php";
?>
</body>
</html>