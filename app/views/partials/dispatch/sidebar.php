<aside id="dispatch-sidebar" class="dispatch-sidebar">
    <input type="hidden" id="userToken" value="<?= htmlspecialchars((string) ($_SESSION['user_token'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <nav class="nav flex-column gap-2 p-3" aria-label="Dispatch navigation">

        <a href="/" class="nav-link active" aria-current="page" title="Dashboard">
            <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
            <span class="dispatch-nav-label">Dashboard</span>
        </a>

        <a href="#" class="nav-link" title="Drivers" aria-disabled="true" tabindex="-1">
            <i class="fa-solid fa-users" aria-hidden="true"></i>
            <span class="dispatch-nav-label">Drivers</span>
        </a>

        <a href="#" class="nav-link" title="Messages" aria-disabled="true" tabindex="-1">
            <i class="fa-solid fa-message" aria-hidden="true"></i>
            <span class="dispatch-nav-label">Messages</span>
        </a>

        <a href="#" class="nav-link" title="Emergencies" aria-disabled="true" tabindex="-1">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
            <span class="dispatch-nav-label">Emergencies</span>
        </a>

        <a href="#" class="nav-link" title="Assignments" aria-disabled="true" tabindex="-1">
            <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i>
            <span class="dispatch-nav-label">Assignments</span>
        </a>

        <a href="#" class="nav-link" title="Settings" aria-disabled="true" tabindex="-1">
            <i class="fa-solid fa-gear" aria-hidden="true"></i>
            <span class="dispatch-nav-label">Settings</span>
        </a>

    </nav>

    <!-- Dispatcher Profile -->
    <div id="dispatch-profile" class="dispatch-profile mt-auto p-3 border-top">
        <div class="d-flex align-items-center gap-2">

            <div class="dispatch-profile-avatar flex-shrink-0">
                <i class="fa-solid fa-circle-user fa-2x" aria-hidden="true"></i>
            </div>

            <div class="dispatch-profile-details overflow-hidden">
                <div id="dispatch-profile-name" class="fw-semibold text-truncate">
                    Loading...
                </div>

                <div id="dispatch-profile-staff-id" class="small text-white-50 text-truncate">
                    &nbsp;
                </div>
            </div>

        </div>
    </div>
</aside>