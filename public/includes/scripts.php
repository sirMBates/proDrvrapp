<?php
    echo "\n\n<script src='../bootstrap/js/bootstrap.bundle.js'></script>\n\n";
    echo "<!-- Load JQuery Color CDN(Content Delivery Network) -->\n";
    echo "<script src='https://code.jquery.com/color/jquery.color-3.0.0.min.js' integrity='sha256-KfnxwOV3FhXN7A/28TCtqslo5fRS23cxO5XcxVO5we8=' crossorigin='anonymous'></script>\n\n";
    $url = $_SERVER['REQUEST_URI'];
    
    function startScripts() {
        global $url;
        switch($url) {
            case "/public/views/drvrsignup.php":
                echo "<script type='module' src='/public/assets/js/signup.js?v=<?= filemtime('/public/assets/js/signup.css');?>'></script>\n";
                break;

            case "/public/views/drvrsignin.php":
                echo "<script type='module' src='/public/assets/js/signin.js?v=<?= filemtime('/public/assets/js/signin.css');?>'></script>\n";
                break;

            case "/public/views/complete_signup.php":
                echo "<script type='module' src='/public/assets/js/messagevalidation.js'></script>\n\n";
                echo "<script type='module' src='/public/assets/js/inputfeedback.js'></script>\n";
                break;

            default:
                return;
                break;
        }
    };
    startScripts();
?>