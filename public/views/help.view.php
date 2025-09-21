<?php
require "partials/head.php";
require "partials/nav.php";
require "partials/banner.php";
?>

<div class="card my-3" style="width: 30rem;">
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
                <div class="accordion-body">Yes 👍, you can update your email, mobile number and password. In your profile section ( profile page ), just click on the icon next to the input you want to update. Anything else besides those choices will have to go through your company administrator.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-two" aria-expanded="false" aria-controls="question-two">
                    Can I update/change my password?
                </button>
            </h2>
            <div id="question-two" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                <div class="accordion-body">Yes 👍, you can update your password. In your profile section ( profile page ), Just click on the icon next to password and enter your new password. You may have to check your email to confirm the change.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-three" aria-expanded="false" aria-controls="question-three">
                    How will I be notified about job/work assignment(s)?
                </button>
            </h2>
            <div id="question-three" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                <div class="accordion-body">Once your assignment(s) 💲 have been posted, You'll receive an email and/or notification regarding your assignment(s). Once received, you <u>must</u> confirm your assignment(s). If your company administrator allows, you may also cancel ( reject ) an assignment. When you confirm your assignment, dispatch will receive an update that you're confirmed.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-four" aria-expanded="false" aria-controls="question-four">
                    How do I get my job/work assignment(s)?
                </button>
            </h2>
            <div id="question-four" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                <div class="accordion-body">Click on the <b>Job Order</b> tab and then view your assignments from there. There you'll be able to confirm, cancel/unconfirm, edit and complete ( submit ) your assignments.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-five" aria-expanded="false" aria-controls="question-five">
                    Can I update the information on my job/work assignment(s)?
                </button>
            </h2>
            <div id="question-five" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                <div class="accordion-body">Absolutely 👍, yes! Once your viewing your assignment, click the edit button and make your necessary updates ( changes ) accordingly. Once done you can submit your changes.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-six" aria-expanded="false" aria-controls="question-six">
                    How do I submit my job/work assignment?
                </button>
            </h2>
            <div id="question-six" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                <div class="accordion-body">Once you finish your assignment and made the necessary changes, you may then click the <b><u>Complete Dispatch Order</b></u> button. Dispatch will then receive the completed assignment.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-seven" aria-expanded="false" aria-controls="question-seven">
                    Can I print out my job/work assignment?
                </button>
            </h2>
            <div id="question-seven" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                <div class="accordion-body">Well, if that was the case, I guess there wouldn't be a need for the app, right? The prodriver app your using is a companion app for you ( the driver ). There's really no need for paperwork at this point. But, if you would like a print out, you can ask your office administrator. Direct printing of any job/work orders are subject to your companies discretion.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-eight" aria-expanded="false" aria-controls="question-eight">
                    Is dispatch notified of all status changes?
                </button>
            </h2>
            <div id="question-eight" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                <div class="accordion-body">Dispatch can view your status at any moment. The only time when dispatch gets an alert is, when you click the emergency button.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-nine" aria-expanded="false" aria-controls="question-nine">
                    How do I confirm that my status was changed/updated?
                </button>
            </h2>
            <div id="question-nine" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                <div class="accordion-body">If you look at the top of the page inside the banner section at the top right, under the date/time display, you'll see text. That is your <b class="text-info">S</b>tatus <b class="text-info">M</b>essage <b class="text-info">D</b>isplay (SMD). There, each time you change status, you'll see the status confirmed. Also, on your home page, it will confirm in your dashboard under status. In your profile page it will also confirm in the status input field at the bottom.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-ten" aria-expanded="false" aria-controls="question-ten">
                    Does dispatch get notified of my status when I click the emergency button?
                </button>
            </h2>
            <div id="question-ten" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                <div class="accordion-body">Right away! Once you click the emergency button, the app will flash red and send an alert to dispatch. Until dispatch checks with you, your app will stay in emergency mode. Dispatch must clear the emergency status and once cleared, the app will return to normal mode. Please, under no circumstances, click the emergency button if it is not a real <b class="text-danger">EMERGENCY</b>!</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-eleven" aria-expanded="false" aria-controls="question-eleven">
                    How can I change my status when i'm not on the home screen?
                </button>
            </h2>
            <div id="question-eleven" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                <div class="accordion-body">In the driver's menu. If you check the driver's menu when not on the home screen, you'll see a button for switch status. This button is available for every page except the home page ( for obvious reasons ).</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-twelve" aria-expanded="false" aria-controls="question-twelve">
                    If I click the emergency button, will the local authorities be notified?
                </button>
            </h2>
            <div id="question-twelve" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                <div class="accordion-body">Please, only click the emergency button if you're in an actual emergency. As of date, The only alert goes out to your dispatch. Eventually maybe, the button will alert the authorities. For now, no.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-thirteen" aria-expanded="false" aria-controls="question-thirteen">
                    Can I send a message to the office through the app?
                </button>
            </h2>
            <div id="question-thirteen" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                <div class="accordion-body">In the driver menu, if you click on office, A list of registered ( authorized ) numbers will be listed according to your companies administration set up. There you can click on which ever number(s) and your device primary dialer will take over. Your device may give you options to choose from.</div>
            </div>
        </div>
    </div>
</div>
<?php
require "partials/footer.php";
?>