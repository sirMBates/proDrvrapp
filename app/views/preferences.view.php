<?php
require "partials/head.php";
require "partials/banner.php";
?>

<main class="container py-4">
    <input type="hidden" id="drvrToken" value="<?= htmlspecialchars($_SESSION['drvr_token'] ?? '') ?>">
    <div class="mb-4">
        <h1 class="h2 mb-1">Preferences</h1>
        <p class="text-body-secondary mb-0">
            Customize your ProDriver experience.
        </p>
    </div>

    <section class="card shadow-sm">
        <div class="card-body">
            <h2 class="h5 card-title mb-2">
                <i class="fa-solid fa-palette me-2" aria-hidden="true"></i>
                Appearance
            </h2>

            <p class="text-body-secondary mb-3">
                Choose how ProDriver appears on this device.
            </p>

            <div class="d-flex align-items-center gap-2">
                <button type="button" id="themeBtn" class="btn btn-light" aria-label="Change theme" style="background: none; border: none; width: 50px; height: 50px;">
                    <i class="fa-fw fa-solid fa-moon fa-lg text-dark" aria-hidden="true"></i>
                </button>

                <span id="themeBtnText" class="h5 mb-0">
                    Dark theme
                </span>

                <span id="themeModeIndicator" class="theme-auto" style="font-size: 0.7em; color: cornflowerblue;">
                    Auto
                </span>
            </div>
        </div>
    </section>
</main>

<?php
require "partials/footer.php";
?>