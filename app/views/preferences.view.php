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
                Choose how ProDriver appears across your ProDriver account.
            </p>

            <div class="d-flex align-items-center gap-2 mb-3">
                <button type="button" id="themeBtn" class="btn btn-light" aria-label="Change theme" style="background: none; border: none; width: 50px; height: 50px;">
                    <i class="fa-fw fa-solid fa-moon fa-lg text-dark" aria-hidden="true"></i>
                </button>

                <div>
                    <div class="small text-body-secondary">
                        Theme
                    </div>

                    <span id="themeBtnText" class="h5 mb-0">
                        Dark theme
                    </span>
                </div>
            </div>

            <hr class="my-3">

            <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                <div>
                    <div class="fw-semibold">
                        Theme Mode
                    </div>

                    <small id="themeModeDescription" class="text-body-secondary">
                        Let ProDriver choose automatically.
                    </small>
                </div>

                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <span id="themeAutoLabel" class="theme-auto fw-semibold">
                        Auto
                    </span>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="themeModeSwitch" aria-label="Switch between automatic and manual theme mode">
                    </div>

                    <span id="themeManualLabel" class="text-body-secondary">
                        Manual
                    </span>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
require "partials/footer.php";
?>