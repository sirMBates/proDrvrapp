<?php
$url = parse_url($_SERVER['REQUEST_URI'])['path'];
function pageStyle($value) {
        switch($value) {
                case "/signup":
                        echo "<link rel='stylesheet' href='../public/dist/styles/style.css'>\n";
                        break;
                
                case "/register":
                        echo "<link rel='stylesheet' href='../public/dist/styles/register.css'>\n";
                        break;

                case "/signin":
                        echo "<link rel='stylesheet' href='../public/dist/styles/style.css'>\n";
                        break;

                case "/forget":
                        echo "<link rel='stylesheet' href='../public/dist/styles/style.css'>\n";
                        break;

                case "/completereset":
                        echo "<link rel='stylesheet' href='../public/dist/styles/style.css'>\n";
                        break;

                case "/":
                        return;
                        break;

                case "/contact":
                        return;
                        break;

                case "/faqs":
                        return;
                        break;

                case "/counter":
                        return;
                        break;

                case "/assignments":
                        echo "<link rel='stylesheet' href='../public/dist/styles/jobsview.css'>\n";
                        break;

                case "/int_messages":
                        echo "<link rel='stylesheet' href='../public/dist/styles/messages.css'>\n";
                        break;

                case "/timesheet":
                        echo "<link rel='stylesheet' href='../public/dist/styles/tsheet.css'>\n";
                        break;

                case "/profile":
                        return;
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