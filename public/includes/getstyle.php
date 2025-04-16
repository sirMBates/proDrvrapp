<?php
$url = $_SERVER['REQUEST_URI'];
function correctStyling($url) {
        if (strpos($url, '?') !== false) {
                $viewdUrl = parse_url($url, PHP_URL_QUERY);
                var_dump($viewdUrl);
        }
};

function pageStyle() {
        global $url;
        correctStyling($url);
        switch($url) {
                case "/prodrvrapp/public/views/drvrsignup.php":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/dvrsignup.css'>\n";
                        break;

                case "/prodrvrapp/public/views/drvrsignup.php?error=stmtfailed":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/dvrsignup.css'>\n";
                        break;

                case "/prodrvrapp/public/views/drvrsignup.php?error=emptyinput":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/dvrsignup.css'>\n";
                        break;

                case "/prodrvrapp/public/views/drvrsignup.php?error=namenotvalid":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/dvrsignup.css'>\n";
                        break;

                case "/prodrvrapp/public/views/drvrsignup.php?error=emailnotvalid":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/dvrsignup.css'>\n";
                        break;

                case "/prodrvrapp/public/views/drvrsignup.php?error=passwordnotvalid":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/dvrsignup.css'>\n";
                        break;

                case "/prodrvrapp/public/views/drvrsignup.php?error=nameexistalready":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/dvrsignup.css'>\n";
                        break;
                
                case "/prodrvrapp/public/views/drvrinfo.php":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/onboard.css'>\n";
                        break;

                case "/prodrvrapp/public/views/drvrsignin.php":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/dvrsignin.css'>\n";
                        break;

                case "/prodrvrapp/public/views/drvrsignin.php?error=stmtfailed":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/dvrsignin.css'>\n";
                        break;

                case "/prodrvrapp/public/views/drvrsignin.php?error=emptyinput":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/dvrsignin.css'>\n";
                        break;

                case "/prodrvrapp/public/views/drvrsignin.php?error=usernotfound":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/dvrsignin.css'>\n";
                        break;

                case "/prodrvrapp/public/views/drvrsignin.php?error=wrongpassword":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/dvrsignin.css'>\n";
                        break;

                case "/prodrvrapp/public/mail/sendmail.php":
                        return;
                        break;

                case "/prodrvrapp/public/views/home.php":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/home-style.css'>\n";
                        break;

                case "/prodrvrapp/public/views/joborder.php":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/orderpage.css'>\n";
                        break;

                case "/prodrvrapp/public/views/dprofile.php":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/profilepage.css'>\n";
                        break;

                case "/prodrvrapp/public/views/payroll.php":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/payrollpage.css'>\n";
                        break;

                case "/prodrvrapp/public/views/printable.php":
                        echo "<link rel='stylesheet' href='/prodrvrapp/public/assets/styles/payrollpage.css'>\n";
                        break;
                        
                default:
                        return;
                        break;
        }
};
pageStyle();
?>