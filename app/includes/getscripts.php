<?php
$url = parse_url($_SERVER['REQUEST_URI'])['path'];
function getClockandMainOrNav($value) {
    switch($value) {
        case '/signup': 
            return;
            break;

        case '/register': 
            return;
            break;

        case '/signin': 
            return;
            break;

        case '/forget':
            return;
            break;

        case '/compreset':
            return;
            break;

        case '/printable': 
            echo "<script type='module' src='../dist/js/printablenavbar.js'></script>\n\n";
            break;
        
        default:
            echo "<script type='module' src='../dist/js/clock.js'></script>\n";
            echo "<script type='module' src='../dist/js/app.js'></script>\n";
            break;
    }
};

function pageScripts($value) {
        switch($value) {
            case "/signup":
                echo "<script type='module' src='../dist/js/signup.js'></script>\n";
                break;

            case "/register":
                echo "<script type='module' src='../dist/js/regisprofile.js'></script>\n";
                break;

            case "/signin":
                echo "<script type='module' src='../dist/js/signin.js'></script>\n";
                break;

            case "/forget":
                echo "<script type='module' src='../dist/js/forget.js'></script>\n";
                break;

            case "/compreset":
                echo "<script type='module' src='../dist/js/reset.js'></script>\n";
                break;

            case "/":
                echo "<script src='https://cdn.jsdelivr.net/npm/js-confetti@latest/dist/js-confetti.browser.js'></script>\n\n";
                echo "<script type='module' src='../dist/js/home.js'></script>\n";
                break;

            case "/contact":
                echo "<script type='module' src='../dist/js/messagevalidation.js'></script>";
                break;

            case "/orders":
                if (isset($_SESSION['signatureRequired']) && $_SESSION['signatureRequired'] === true) {
                    echo "<script src='brinley/libs/flashcanvas.js'></script>\n";
                    echo "<script src='brinley/libs/jSignature.min.js'></script>\n";
                    echo "<script type='module' src='../dist/js/sign.js'></script>\n";
                }
                echo "<script type='module' src='../dist/js/orderpage.js'></script>\n";
                break;

            case "/profile":
                echo "<script type='module' src='../dist/js/profilehandler.js'></script>\n";
                break;

            case "/timesheet":
                echo "<script type='module' src='../dist/js/tsheet.js'></script>\n";
                break;
            
            case "/printable":
                echo "<!-- This is used to convert page to pdf -->\n";
                echo "<script src='https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js' integrity='sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==' crossorigin='anonymous' referrerpolicy='no-referrer'></script>\n\n";
                echo "<script type='module' src='../dist/js/payroll.js'></script>\n";
                break;

            default:
                return;
                break;                
    }
};
getClockandMainOrNav($url);
pageScripts($url);
?>