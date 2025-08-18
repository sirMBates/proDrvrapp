<?php
$url = $_SERVER['REQUEST_URI'];

function pageStyle($value) {
        switch($value) {
                case "/signup":
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

                case "/":
                        echo "<link rel='stylesheet' href='styles/home.css'>\n";
                        break;

                case "/orders":
                        echo "<link rel='stylesheet' href='styles/orders.css'>\n";
                        break;

                case "/profile":
                        echo "<link rel='stylesheet' href='styles/profile.css'>\n";
                        break;

                case "/timesheet":
                        echo "<link rel='stylesheet' href='styles/tsheet.css'>\n";
                        break;

                case "/contact":
                        return;
                        break;

                case "/printable":
                        echo "<link rel='stylesheet' href='styles/payroll.css'>\n";
                        break;

                case "/views/404.php":
                        return;
                        break;
                        
                default:
                        return;
                        break;
        }
};
pageStyle($url);
?>