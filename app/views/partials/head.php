<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <meta name="theme-color" content="#1D5283">
        <script>(() => {
                try {
                        const savedMode = localStorage.getItem('prodriverThemeMode');
                        const legacyOverride = localStorage.getItem('userThemeOverride');
                        const validModes = ['auto', 'light', 'dark'];
                        let mode = validModes.includes(savedMode) ? savedMode : ['light', 'dark'].includes(legacyOverride) ? legacyOverride : 'auto';

                        if (mode === 'auto') {
                                const hour = new Date().getHours();
                                mode = hour >= 20 || hour <= 6 ? 'dark' : 'light';
                        }

                        document.documentElement.dataset.bsTheme = mode;
                } catch (error) {
                        document.documentElement.dataset.bsTheme = 'light';
                }
        })();</script>

        <link rel="manifest" href="/manifest.json">
        <link rel="stylesheet" href="/dist/styles/components.css">
        <?php
                require base_path("app/includes/getstyle.php");
        ?>
        <link rel="stylesheet" href="/dist/styles/scss/main.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />        
        <script src="https://kit.fontawesome.com/f2acae2623.js" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>
        <link rel="icon" type="image/png" sizes="50x50" href="/dist/images-videos/logoandicons/prodrvr-bus-icon.png">                
        <title><?= $title;?></title>
</head>
<body class="d-flex flex-column overflow-x-hidden min-vh-100 noprint">
