<?php
$uri = $_SERVER['REQUEST_URI'];
function correctStyling($uri) {
        if (strpos($uri, '?') !== false) {
                echo "<script>alert('The requested uri has a parameter.')</script>";
        }
}
correctStyling($uri);

function pageStyle() {
        switch($uri) {
                case "public/views/drvrsignup.php":
                        echo "<link rel='stylesheet' href='../assets/styles/dvrsignup.css'>\n";
                        break;

                case "public/views/drvrsignin.php":
                        echo "<link rel='stylesheet' href='../assets/styles/dvrsignin.css'>\n";
                        break;
                
                case "public/views/complete_signup.php":
                        echo "<link rel='stylesheet' href='../assets/styles/onboard.css'>\n";
                        break;

                case "public/mail/sendmail.php":
                        return;
                        break;

                case "public/views/home.php":
                        echo "<link rel='stylesheet' href='../assets/styles/home-style.css'>\n";
                        break;

                case "public/views/joborder.php":
                        echo "<link rel='stylesheet' href='../assets/styles/orderpage.css'>\n";
                        break;

                case "public/views/dprofile.php":
                        echo "<link rel='stylesheet' href='../assets/styles/profilepage.css'>\n";
                        break;

                case "public/views/payroll.php":
                        echo "<link rel='stylesheet' href='../assets/styles/payrollpage.css'>\n";
                        break;

                case "public/views/printable.php":
                        echo "<link rel='stylesheet' href='../assets/styles/payrollpage.css'>\n";
                        break;
                        
                default:
                        return;
                        break;
        }
};
pageStyle();
?>