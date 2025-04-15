<?php
echo "\n\n<script src='/prodrvrapp/public/bootstrap/js/bootstrap.bundle.js'></script>\n\n";
echo "<!-- Load JQuery Color CDN(Content Delivery Network) -->\n";
echo "<script src='https://code.jquery.com/color/jquery.color-3.0.0.min.js' integrity='sha256-KfnxwOV3FhXN7A/28TCtqslo5fRS23cxO5XcxVO5we8=' crossorigin='anonymous'></script>\n\n";

$url = $_SERVER['REQUEST_URI'];
function getClockandMainOrNav () {
    global $url;
    if ($url === '/prodrvrapp/public/views/printable.php') {
        echo "<script type='module' src='/prodrvrapp/public/assets/js/printablenavbar.js'></script>\n\n";
    }
    if ($url === '/prodrvrapp/public/views/drvrsignup.php') {
        return ;
    }
    if ($url === '/prodrvrapp/public/views/drvrinfo.php') {
        return ;
    }
    if ($url === '/prodrvrapp/public/views/drvrsignin.php') {
        return ;
    } 
    else {
        echo "<script type='module' src='/prodrvrapp/public/assets/js/clock.js'></script>\n\n";
        echo "<script type='module' src='/prodrvrapp/public/assets/js/main.js'></script>\n\n";
    }
};

function pageScripts () {
    global $url;
        switch($url) {
            case "/prodrvrapp/public/views/drvrsignup.php":
                echo "<script type='module' src='/prodrvrapp/public/assets/js/signup.js'></script>\n";
                break;

            case "/prodrvrapp/public/views/drvrinfo.php":
                echo "<script type='module' src='/prodrvrapp/public/assets/js/messagevalidation.js'></script>\n\n";
                echo "<script type='module' src='/prodrvrapp/public/assets/js/inputfeedback.js'></script>\n";
                break;

            case "/prodrvrapp/public/views/drvrsignin.php":
                echo "<script type='module' src='/prodrvrapp/public/assets/js/signin.js'></script>\n";
                break;

            case "/prodrvapp/public/mail/sendmail.php":
                echo "<script type='module' src='/prodrvrapp/public/assets/js/messagevalidation.js'></script>";
                break;

            case "/prodrvrapp/public/views/home.php":
                echo "<script src='https://cdn.jsdelivr.net/npm/js-confetti@latest/dist/js-confetti.browser.js'></script>\n\n";
                echo "<script type='module' src='/prodrvrapp/public/assets/js/home.js'></script>\n";
                break;

            case "/prodrvrapp/public/views/joborder.php":
                //echo "<!--<script src='../brinley/libs/flashcanvas.js'></script>-->\n\n";
                //echo "<script src='../brinley/libs/jSignature.min.js'></script>\n\n";
                //echo "<script type='module' src='../assets/js/sign.js'></script>\n\n";
                echo "<!--<script type='module' src='/prodrvrapp/public/assets/js/orderpage.js'></script>-->\n";
                break;

            case "/prodrvrapp/public/views/dprofile.php":
                echo "<script type='module' src='/prodrvrapp/public/assets/js/profilehandler.js'></script>\n";
                break;

            case "/prodrvrapp/public/views/payroll.php":
                echo "<script type='module' src='/prodrvrapp/public/assets/js/payroll.js'></script>\n";
                break;
            
            case "/prodrvrapp/public/views/printable.php":
                echo "<!-- This is used to convert page to pdf -->\n";
                echo "<script src='https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js' integrity='sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==' crossorigin='anonymous' referrerpolicy='no-referrer'></script>\n\n";
                echo "<script type='module' src='/prodrvrapp/public/assets/js/payroll.js'></script>\n";
                break;

            default:
                return;
                break;                
    }
};
getClockandMainOrNav();
pageScripts();
?>