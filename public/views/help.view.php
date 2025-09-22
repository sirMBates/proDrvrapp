<?php
require "partials/head.php";
require "partials/nav.php";
require "partials/banner.php";
?>

<main class="container-fluid my-2">
    <div class="card">
        <input id="drvrToken" type="hidden" class="form-control" name="drvrtoken" value="<?= htmlspecialchars($_SESSION['drvr_token'], ENT_QUOTES);?>">
        <img id="card-img" src="../../dist/images-videos/drvrarea1.jpg" alt="n/a" class="card-img-top">
        <div class="card-body">
            <h5 class="card-title"><u><b class="text-uppercase">faq</b>s</u></h5>
            <p class="card-text">Before you reach out to help desk, you should take a look at these topics regarding your matter.</p>
        </div>
        <div id="faqcon" class="accordion accordion-flush">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-one" aria-expanded="false" aria-controls="question-one">
                        Can I update/change my personal information?
                    </button>
                </h2>
                <div id="question-one" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                    <div class="accordion-body">Yes 👍, you can update your email, mobile number and password. In your profile page, just click on the icon next to the input you want to update. Anything else besides those choices will have to go through your company administrator ( management ).</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-two" aria-expanded="false" aria-controls="question-two">
                        Can I update/change my password?
                    </button>
                </h2>
                <div id="question-two" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                    <div class="accordion-body">Yes 👍, you can update your password. In your profile page, just click on the icon next to password and enter your new password. You may have to check your email to confirm the change.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-three" aria-expanded="false" aria-controls="question-three">
                        How will I be notified about job/work assignment(s)?
                    </button>
                </h2>
                <div id="question-three" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                    <div class="accordion-body">Once your assignment(s) 💵 have been posted, you'll receive an email and/or notification regarding your assignment(s). Once received, you <u>must</u> confirm your assignment(s). If your company administrator ( management ) allows, you may also cancel ( reject ) an assignment. When you confirm your assignment, dispatch will receive an update that you're confirmed.</div>
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
                    <div class="accordion-body">Once you finish your assignment and made the necessary changes, you may then click the <b><u>Complete Dispatch Order</b></u> button. Dispatch ( management ) will then receive the completed assignment.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-seven" aria-expanded="false" aria-controls="question-seven">
                        Can I print out my job/work assignment?
                    </button>
                </h2>
                <div id="question-seven" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                    <div class="accordion-body">Why would you want to? That kinda defeats the purpose of the app, right? The prodriver app your using is a companion app for you ( the driver ). There's really no need for paperwork at this point. But, if you would like a print out, you can ask your office administrator ( management ). Direct printing of any job/work orders are subject to your company's discretion.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-eight" aria-expanded="false" aria-controls="question-eight">
                        Is dispatch notified of all status changes?
                    </button>
                </h2>
                <div id="question-eight" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                    <div class="accordion-body">Dispatch can view your status<i class="px-1 fa-solid fa-square-poll-vertical"></i> at any moment. The only time dispatch gets an alert is when you click the <b class="text-capitalize text-danger">emergency</b> button.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-nine" aria-expanded="false" aria-controls="question-nine">
                        How do I confirm that my status was changed/updated?
                    </button>
                </h2>
                <div id="question-nine" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                    <div class="accordion-body">If you look at the top of the page inside the banner section at the top right, under the date/time display, you'll see some text. That is your <b class="text-info">S</b>tatus <b class="text-info">M</b>essage <b class="text-info">D</b>isplay (SMD). There, each time you change status<i class="px-1 fa-solid fa-square-poll-vertical"></i>, you'll see the status confirmed. Also, on your home page, it will confirm in your dashboard under status. In your profile page it will also confirm in the status input field at the bottom.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-ten" aria-expanded="false" aria-controls="question-ten">
                        Does dispatch get notified of my status when I click the emergency button?
                    </button>
                </h2>
                <div id="question-ten" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                    <div class="accordion-body">Right away 💯! Once you click the emergency button, the app will flash red and send an alert to your company's dispatch. Until dispatch checks with you, your app will stay in emergency mode. Dispatch must clear the emergency status<i class="px-1 fa-solid fa-square-poll-vertical"></i> and once cleared, the app will return to normal mode. <u>Please, under no circumstances, click the emergency button if it is not a real <b class="text-danger text-capitalize">emergency</b></u>!</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-eleven" aria-expanded="false" aria-controls="question-eleven">
                        How can I change my status when i'm not on the home screen?
                    </button>
                </h2>
                <div id="question-eleven" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                    <div class="accordion-body">In the driver's menu. When not on the home screen, you'll see a button that says switch status<i class="px-1 fa-solid fa-square-poll-vertical"></i>. This button is available for every page except the home page ( for obvious reasons ).</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-twelve" aria-expanded="false" aria-controls="question-twelve">
                        Does dispatch ( management ) know when i'm done with my shift? Besides just completing all work orders?
                    </button>
                </h2>
                <div id="question-twelve" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                    <div class="accordion-body">Once you've completed your work assignments for the duration of your shift, there's a button, ( <b class="text-capitalize">end shift</b> ). <u><b>You must</b> click this button</u> only if your done with all of your assignments for the shift. This way, dispatch ( management )<i class="px-1 fa-solid fa-building"></i> has confirmation that your done with all your assignments and the app can reset your status<i class="px-1 fa-solid fa-square-poll-vertical"></i>.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-thirteen" aria-expanded="false" aria-controls="question-thirteen">
                        If I click the emergency button, will the local authorities be notified?
                    </button>
                </h2>
                <div id="question-thirteen" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                    <div class="accordion-body"><u>Please, only click the emergency button if you're in an actual <b class="text-danger text-capitalize">emergency</b></u>. The alert only goes out to your company's dispatch ( management )<i class="px-1 fa-solid fa-building"></i>. So for now, no 👎.</div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-fourteen" aria-expanded="false" aria-controls="question-fourteen">
                        Can I send a message to the dispatch ( management ) through the app?
                    </button>
                </h2>
                <div id="question-fourteen" class="accordion-collapse collapse" data-bs-parent="#faqcon">
                    <div class="accordion-body">In the driver menu, if you click on office<i class="px-1 fa-solid fa-building"></i>, A list of registered ( authorized ) numbers will be listed according to your company's administration ( management ) set up. There you can click on which ever number you so choose and your device should show the dialer. If not, your device may give you other options to choose from.</div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php
require "partials/footer.php";
?>