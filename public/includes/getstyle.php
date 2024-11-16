<?php
function pageStyle () {
        switch($_SERVER['REQUEST_URI']) {
                case "/public/views/drvrsignup.php":
                        echo "<link rel='stylesheet' href='/public/assets/styles/dvrsignup.css?v=<?= filemtime('/public/assets/styles/dvrsignup.css');?>'>\n";
                        break;

                case "/public/views/drvrsignin.php":
                        echo "<link rel='stylesheet' href='/public/assets/styles/dvrsignin.css?v=<?= filemtime('/public/assets/styles/dvrsignup.css');?>'>\n";
                        break;
                
                case "/public/views/complete_signup.php":
                        echo "<link rel='stylesheet' href='/public/assets/styles/onboard.css?v=1'>\n";
                        break;

                case "/public/mail/sendmail.php":
                        return;
                        break;

                case "/public/views/home.php":
                        echo "<link rel='stylesheet' href='/public/assets/styles/home-style.css?v=1'>\n";
                        break;

                case "/public/views/joborder.php":
                        echo "<link rel='stylesheet' href='/public/assets/styles/orderpage.css?v=1'>\n";
                        break;

                case "/public/views/dprofile.php":
                        echo "<link rel='stylesheet' href='/public/assets/styles/profilepage.css?v=1'>\n";
                        break;

                case "/public/views/payroll.php":
                        echo "<link rel='stylesheet' href='/public/assets/styles/payrollpage.css?v=1'>\n";
                        break;

                case "/public/views/printable.php":
                        echo "<link rel='stylesheet' href='/public/assets/styles/payrollpage.css?v=1'>\n";
                        break;
                        
                default:
                        return;
                        break;
        }
};
pageStyle();
?>