<?php
$url = $_SERVER['REQUEST_URI'];
function correctStyling($url) {
        if (strpos($url, '?') !== false) {
                echo "<script>alert('The requested uri has a parameter.')</script>";
        }
}
correctStyling($url);/prodrvapp

function pageStyle() {
        global $url;
        switch($url) {
                case "/prodrvrapp/public/views/drvrsignup.php":
                        echo "<link rel='stylesheet' href='/prodrvapp/public/assets/styles/dvrsignup.css'>\n";
                        break;

                case "/prodrvrapp/public/views/drvrsignin.php":
                        echo "<link rel='stylesheet' href='/prodrvapp/public/assets/styles/dvrsignin.css'>\n";
                        break;
                
                case "/prodrvrapp/public/views/drvrinfo.php":
                        echo "<link rel='stylesheet' href='/prodrvapp/public/assets/styles/onboard.css'>\n";
                        break;

                case "/prodrvrapp/public/mail/sendmail.php":
                        return;
                        break;

                case "/prodrvrapp/public/views/home.php":
                        echo "<link rel='stylesheet' href='/prodrvapp/public/assets/styles/home-style.css'>\n";
                        break;

                case "/prodrvrapp/public/views/joborder.php":
                        echo "<link rel='stylesheet' href='/prodrvapp/public/assets/styles/orderpage.css'>\n";
                        break;

                case "/prodrvrapp/public/views/dprofile.php":
                        echo "<link rel='stylesheet' href='/prodrvapp/public/assets/styles/profilepage.css'>\n";
                        break;

                case "/prodrvrapp/public/views/payroll.php":
                        echo "<link rel='stylesheet' href='/prodrvapp/public/assets/styles/payrollpage.css'>\n";
                        break;

                case "/prodrvrapp/public/views/printable.php":
                        echo "<link rel='stylesheet' href='/prodrvapp/public/assets/styles/payrollpage.css'>\n";
                        break;
                        
                default:
                        return;
                        break;
        }
};
pageStyle();
?>