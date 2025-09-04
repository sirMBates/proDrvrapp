<?php
$url = parse_url($_SERVER['REQUEST_URI'])['path'];
function pageStyle($value) {
        switch($value) {
                case "/signup":
                        echo "<link rel='stylesheet' href='../dist/styles/style.css'>\n";
                        break;
                
                case "/register":
                        echo "<link rel='stylesheet' href='../dist/styles/register.css'>\n";
                        break;

                case "/signin":
                        echo "<link rel='stylesheet' href='../dist/styles/style.css'>\n";
                        break;

                case "/forget":
                        echo "<link rel='stylesheet' href='../dist/styles/style.css'>\n";
                        break;

                case "/compreset":
                        echo "<link rel='stylesheet' href='../dist/styles/style.css'>\n";
                        break;

                case "/":
                        return;
                        break;

                case "/contact":
                        return;
                        break;

                case "/orders":
                        echo "<link rel='stylesheet' href='../dist/styles/orders.css'>\n";
                        break;

                case "/profile":
                        return;
                        break;

                case "/timesheet":
                        echo "<link rel='stylesheet' href='../dist/styles/tsheet.css'>\n";
                        break;

                case "/printable":
                        echo "<link rel='stylesheet' href=''>\n";
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