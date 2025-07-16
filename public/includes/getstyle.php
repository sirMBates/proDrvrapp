<?php
$url = parse_url($_SERVER['REQUEST_URI'])['path'];

function pageStyle($value) {
        switch($value) {
                case "/":
                        echo "<link rel='stylesheet' href='styles/style.css'>\n";
                        break;
                
                case "/register":
                        echo "<link rel='stylesheet' href='styles/register.css'>\n";
                        break;

                case "/signin":
                        echo "<link rel='stylesheet' href='styles/style.css'>\n";
                        break;

                case "/forget":
                        echo "<link rel='stylesheet' href='styles/style.css'>\n";
                        break;

                case "/compreset":
                        echo "<link rel='stylesheet' href='styles/style.css'>\n";
                        break;

                case "/home":
                        echo "<link rel='stylesheet' href='styles/home.css'>\n";
                        break;

                case "/orders":
                        echo "<link rel='stylesheet' href='styles/orders.css'>\n";
                        break;

                case "/profile":
                        echo "<link rel='stylesheet' href='styles/profile.css'>\n";
                        break;

                case "/payroll":
                        echo "<link rel='stylesheet' href='styles/payroll.css'>\n";
                        break;

                case "/prodrvrapp/public/mail/sendmail.php":
                        return;
                        break;

                case "/prodrvrapp/public/views/printable.php":
                        echo "<link rel='stylesheet' href='styles/payroll.css'>\n";
                        break;
                        
                default:
                        return;
                        break;
        }
};
pageStyle($url);
?>