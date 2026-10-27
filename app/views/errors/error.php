<?php
    $title = $errorTitle;
    require base_path("app/views/partials/outhead.php");
?>
<style>
.error-code {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.08); }
    100% { transform: scale(1); }
}

.error-page {
    min-height: 100vh;
    padding: 1rem;
}

.error-card {
    max-width: 650px;
    width: 100%;
}

[data-bs-theme="dark"] .error-page {
    background: #121212;
}

[data-bs-theme="dark"] .error-page .lead {
    color: #adb5bd !important;
}

.bus-image {
    width: 250px;
    max-width: 100%;
    height: auto;
    animation: bounce 3s ease-in-out infinite;
}

@keyframes bounce {
    0%,100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-8px);
    }
}

/* Mobile adjustments */
@media (max-width: 576px) {
    .error-card {
        padding: 1.5rem !important;
    }

    .bus-image {
        width: 175px;
    }

    .error-code {
        font-size: 4rem !important;
    }

    .error-title {
        font-size: 1.5rem;
    }

    .error-message {
        font-size: 1rem !important;
    }

    .error-button {
        width: 100%
    }
}

@media (max-width: 400px) {
    .error-code {
        font-size: 3.25rem !important;
    }

    .bus-image {
        width: 140px;
    }
}
</style>

<div class="container error-page d-flex justify-content-center align-items-center">
    <div class="error-card text-center p-5 shadow-lg rounded-4 bg-body-tertiary">
        <img src="/dist/images-videos/logoandicons/bus-error.svg" alt="Route Error" class="bus-image mb-4">

        <h1 class="display-1 fw-bold text-danger error-code">
            <?= $errorCode ?>
        </h1>

        <h2 class="mb-3 error-title">
            <?= htmlspecialchars($errorTitle) ?>
        </h2>

        <p class="fs-4 text-body-secondary mb-4 error-message">
            <?= htmlspecialchars($errorMessage) ?>
        </p>

        <button class="btn btn-warning btn-lg error-button" onclick="window.history.length > 1 ? history.back() : window.location.href='/'">
            ← Return To Previous Page
        </button>
    </div>
</div>
