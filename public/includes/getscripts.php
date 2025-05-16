<?php
$url = parse_url($_SERVER['REQUEST_URI'])['path'];
function getClockandMainOrNav($value) {
    switch($value) {
        case '/': 
            return;
            break;

        case '/register.php': 
            return;
            break;

        case '/signin.php': 
            return;
            break;

        case '/printable.php': 
            echo "<script type='module' src='js/printablenavbar.js'></script>\n\n";
            break;
        
        default:
            echo "<script type='module' src='js/clock.js'></script>\n\n";
            echo "<script type='module' src='js/main.js'></script>\n\n";
            break;
        }
};

function pageScripts($value) {
        switch($value) {
            case "/":
                echo "<script type='module' src='js/signup.js'></script>\n";
                break;

            case "/register.php":
                echo "<script type='module' src='js/messagevalidation.js'></script>\n\n";
                echo "<script type='module' src='js/inputfeedback.js'></script>\n";
                break;

            case "/signin.php":
                echo "<script type='module' src='js/signin.js'></script>\n";
                break;

            case "/sendmail.php":
                echo "<script type='module' src='js/messagevalidation.js'></script>";
                break;

            case "/home.php":
                echo "<script src='https://cdn.jsdelivr.net/npm/js-confetti@latest/dist/js-confetti.browser.js'></script>\n\n";
                echo "<script type='module' src='js/home.js'></script>\n";
                break;

            case "/joborder.php":
                //echo "<!--<script src='brinley/libs/flashcanvas.js'></script>-->\n\n";
                //echo "<script src='brinley/libs/jSignature.min.js'></script>\n\n";
                //echo "<script type='module' src='js/sign.js'></script>\n\n";
                echo "<!--<script type='module' src='js/orderpage.js'></script>-->\n";
                break;

            case "/dprofile.php":
                echo "<script type='module' src='js/profilehandler.js'></script>\n";
                break;

            case "/payroll.php":
                echo "<script type='module' src='js/payroll.js'></script>\n";
                break;
            
            case "/printable.php":
                echo "<!-- This is used to convert page to pdf -->\n";
                echo "<script src='https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js' integrity='sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==' crossorigin='anonymous' referrerpolicy='no-referrer'></script>\n\n";
                echo "<script type='module' src='js/payroll.js'></script>\n";
                break;

            default:
                return;
                break;                
    }
};
getClockandMainOrNav($url);
pageScripts($url);
?>