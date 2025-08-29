<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <?php
        $url = $_SERVER['REQUEST_URI'];
        if ($url !== '/views/404.php') {
                require_once base_path("app/includes/getstyle.php");
        }
        ?>
        <link rel="stylesheet" href="../../dist/styles/scss/main.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />        
        <script src="https://kit.fontawesome.com/f2acae2623.js" crossorigin="anonymous"></script>
        <script src='https://code.jquery.com/jquery-3.7.1.min.js' integrity='sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=' crossorigin='anonymous'></script>
        <link rel="icon" type="image/png" href="images-videos/logoandicons/bus-driver-icon-png-14404.png">                
        <title><?= $title;?></title>
</head>
<body class="d-flex flex-column align-items-center min-vh-100 overflow-y-scroll noprint prodrvrbkgd">
