<?php
require "partials/head.php";
require "partials/nav.php";
require "partials/banner.php";
include "partials/info-modal.php";
?>

<div class="card mb-3" style="width: 35rem;">
    <input id="drvrToken" type="hidden" class="form-control" name="drvrtoken" value="<?= htmlspecialchars($_SESSION['drvr_token'], ENT_QUOTES);?>">
    <img src="../../dist/images-videos/drvrarea1.jpg" alt="n/a" class="card-img-top">
    <div class="card-body">
        <h5 class="card-title"><u><b class="text-uppercase">faq</b>s</u></h5>
        <p class="card-text">Before you reach out to help desk, you should take a look at these topics regarding your matter.</p>
    </div>
    <div id="faqcon" class="accordion accordion-flush">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-one" aria-expanded="false" aria-controls="question-one">
                    Can I update/change personal information?
                </button>
            </h2>
            <div id="question-one" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                <div class="accordion-body">Yes, you can update your email, mobile number and password. In your profile section ( profile page ), just click on the icon next to the input you want to update. Anything else besides those choices will have to go through your company adminstrator.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-two" aria-expanded="false" aria-controls="question-two">
                    Can I update/change my password?
                </button>
            </h2>
            <div id="question-two" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                <div class="accordion-body">Yes, you can update your password. In your profile section ( profile page ), Just click on the icon next to password and enter your new password. You may have to check your email to confirm the change.</div>
            </div>
        </div>
    </div>
</div>
<?php
require "partials/footer.php";
?>