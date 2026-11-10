<header class="dispatch-header bg-prodriverclr border-bottom border-dark">
    <div class="container-fluid d-flex align-items-center gap-3 h-100">

        <button id="dispatch-menu-toggle" type="button" class="btn p-0 border-0" aria-label="Toggle dispatch navigation" aria-controls="dispatch-sidebar" aria-expanded="true">
            <img src="/dist/images-videos/logoandicons/prodrvr-bus-icon.png" alt="" width="40" height="40">
        </button>

        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold text-light">ProDriver</span>
            <span class="badge dispatch-role-badge">Dispatch</span>
        </div>

        <div class="ms-auto d-flex align-items-center gap-3 text-light">

            <?php
            require base_path('app/views/partials/shared/clock.php');
            ?>

            <button type="button" class="btn btn-sm text-light" aria-label="Notifications" disabled>
                <i class="fa-solid fa-bell"></i>
            </button>

            <button type="button" class="btn btn-sm text-light" aria-label="Dispatch account" disabled>
                <i class="fa-solid fa-circle-user"></i>
            </button>

        </div>
    </div>
</header>