<?php
    echo "\n\n<script src='../bootstrap/js/bootstrap.bundle.js'></script>\n\n";
    echo "<!-- Load JQuery Color CDN(Content Delivery Network) -->\n";
    echo "<script src='https://code.jquery.com/color/jquery.color-3.0.0.min.js' integrity='sha256-KfnxwOV3FhXN7A/28TCtqslo5fRS23cxO5XcxVO5we8=' crossorigin='anonymous'></script>\n\n";
    $url = $_SERVER['REQUEST_URI'];
    function getClockandMainOrNav () {
        global $url;
        if ($url === '/public/views/printable.php') {
            echo "<script type='module' src='../assets/js/printablenavbar.js'></script>\n\n";
        }  
        else {
            echo "<script type='module' src='../assets/js/clock.js'></script>\n\n";
            echo "<script type='module' src='../assets/js/main.js'></script>\n\n";
        }
    };
getClockandMainOrNav();
    function pageScripts () {
    global $url;
        switch($url) {
            case "/public/mail/sendmail.php":
                echo "<script type='module' src='../assets/js/messagevalidation.js'></script>";
                break;

            case "/public/views/home.php":
                echo "<script src='https://cdn.jsdelivr.net/npm/js-confetti@latest/dist/js-confetti.browser.js'></script>\n\n";
                echo "<script type='module' src='../assets/js/home.js'></script>\n";
                break;

            case '/public/views/joborder.php':
                //echo "<!--<script src='../brinley/libs/flashcanvas.js'></script>-->\n\n";
                //echo "<script src='../brinley/libs/jSignature.min.js'></script>\n\n";
                //echo "<script type='module' src='../assets/js/sign.js'></script>\n\n";
                echo "<!--<script type='module' src='../assets/js/orderpage.js'></script>-->\n";
                break;

            case '/public/views/dprofile.php':
                echo "<script type='module' src='../assets/js/profilehandler.js'></script>\n";
                break;

            case '/public/views/payroll.php':
                echo "<script type='module' src='../assets/js/payroll.js'></script>\n";
                break;
            
            case '/public/views/printable.php':
                echo "<!-- This is used to convert page to pdf -->\n";
                echo "<script src='https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js' integrity='sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==' crossorigin='anonymous' referrerpolicy='no-referrer'></script>\n\n";
                echo "<script type='module' src='../assets/js/payroll.js'></script>\n";
                break;

            default:
                return;
                break;                
    }
};
pageScripts();
?>