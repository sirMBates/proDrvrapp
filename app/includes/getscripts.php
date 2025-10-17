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

        case '/completereset':
            return;
            break;
        
        default:
            echo "<script type='module' src='../dist/js/clock.js'></script>\n";
            echo "<script type='module' src='../dist/js/main.js'></script>\n";
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

            case "/completereset":
                echo "<script type='module' src='../dist/js/reset.js'></script>\n";
                break;

            case "/":
                echo "<script type='module' src='../dist/js/home.js'></script>\n";
                break;

            case "/contact":
                echo "<script type='module' src='../dist/js/contacthandler.js'></script>";
                break;

            case "/help":
                return;
                break;

            case "/assignments":
                if (isset($_SESSION['signature_required']) && $_SESSION['signature_required'] === 1) {
                    echo "<script type='module' src='../dist/js/sign.js'></script>\n";
                }
                echo "<script type='module' src='../dist/js/assignment.js'></script>\n";
                break;

            case "/profile":
                echo "<script type='module' src='../dist/js/profilehandler.js'></script>\n";
                break;

            case "/timesheet":
                echo "<script type='module' src='../dist/js/tsheet.js'></script>\n";
                break;

            default:
                return;
                break;                
    }
};
getClockandMainOrNav($url);
pageScripts($url);
?>