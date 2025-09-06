<?php
require "partials/head.php";
require "partials/nav.php";
require "partials/banner.php";
$alert = new core\Flash();
include "partials/flashmessage.php";
include "partials/info-modal.php";
?>
    <main class="container-fluid">
        <div class="card my-3" style="width: 100%;">
            <div class="card-header bg-besttrailsclr text-btd-white-floral">
                <h3 class="text-capitalize text-center"><button type="button" id="notifyinfo" class="z-3 btn btn-light" aria-label="Left Align" style="background: none; border: none;"><i class="fa-solid fa-circle-info fs-3 text-light"></i></button>send email</h3>
            </div>
            <div class="card-body">
            <form id="email-form" action="" method="POST" name="emailcontactform" class="needs-validation" novalidate>
                <div class="input-group">
                        <input id="drvrToken" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                </div>
                <div class="form-floating mb-3 position-relative">
                    <input id="drvr-email" type="email" class="form-control fs-4 rounded-2" name="sender" placeholder="Your email address" required>
                    <label for="drvr-email">Sender</label>
                </div>
                <div class="form-floating mb-3 position-relative">
                    <input id="dev-email" type="email" class="form-control fs-4 rounded-2" name="receiver" placeholder="Admin email" disabled>
                    <label for="dev-email">Recipient</label>
                </div>
                <div class="form-floating position-relative">
                    <textarea id="body-msg" class="form-control" name="message" placeholder="Add your message here!" style="height: 200px;" required></textarea>
                    <label for="body-msg">Your message</label>
                    <div id="charCounter">0 / 250</div>
                </div>
            </div>
            <div class="card-footer text-capitalize text-center">
                <button id="send-msg" type="button" name="sendmsg" class="btn btn-lg btn-primary">Send</button>
            </div>
            </form>
        </div>
    </main>

<?php
    require "partials/footer.php";
?>