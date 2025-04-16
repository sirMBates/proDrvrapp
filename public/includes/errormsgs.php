<?php
    if(!empty($_GET["error"])){
        switch($_GET['error']){
                case 'emptyinput':
                        echo "<div class='alert alert-warning alert-dismissible' role='alert'><i class='me-2 fa-solid fa-triangle-exclamation'></i>Please fill out form.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
                        break;
                case 'namenotvalid':
                        echo "<div class='alert alert-warning alert-dismissible' role='alert'><i class='me-2 fa-solid fa-triangle-exclamation'></i>Please enter a username.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
                        break;
                case 'emailnotvalid':
                        echo "<div class='alert alert-warning alert-dismissible' role='alert'><i class='me-2 fa-solid fa-triangle-exclamation'></i>Please enter your correct email.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
                        break;
                case 'passwordnotvalid':
                        echo "<div class='alert alert-warning alert-dismissible' role='alert'><i class='me-2 fa-solid fa-triangle-exclamation'></i>Please enter a valid password.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
                        break;
                case 'nameexistalready':
                        echo "<div class='alert alert-danger alert-dismissible' role='alert'><i class='me-2 fa-solid fa-circle-radiation'></i>Please choose another username.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
                        break;
                case 'stmtfailed':
                        echo "<div class='alert alert-primary alert-dismissible' role='alert'><i class='me-2 fa-solid fa-face-frown-open'></i>UH-Oh, Unable to connect. Please try again!<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
                        break;
      
        }
    }
?>