<?php
require "partials/head.php";
require "partials/banner.php";
include "partials/info-modal.php";
include "partials/confirm-modal.php";
?>

<main class="container-fluid my-3 py-4 messenger-page">
    <div class="row">
        <div class="col-12">
            <div class="card messenger-card shadow-sm border-0 overflow-hidden">

                <!-- Page Header -->
                <div class="card-header messenger-header d-flex justify-content-between align-items-center bg-prodriverclr">
                    <div>
                        <h2 class="h5 mb-1 text-light">Messenger</h2>
                        <p class="mb-0 small text-body-secondary">
                            Internal communication center
                        </p>
                    </div>

                    <div>
                        <button id="messenger-new-message" type="button" class="btn btn-sm btn-outline-secondary" disabled>
                            New Message
                        </button>
                    </div>
                </div>

                <div id="messenger-push-controls" class="px-3 py-2 border-bottom" data-public-key="<?= htmlspecialchars($_ENV['WEB_PUSH_PUBLIC_KEY'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                    <button id="messenger-enable-notifications" type="button" class="btn btn-sm btn-outline-primary">
                        Enable notifications
                    </button>

                    <p id="messenger-push-status" class="small text-body-secondary mt-2 mb-0" role="status">
                        Enable message notifications for this browser.
                    </p>
                </div>

                <!-- Main Messenger Body -->
                <div class="card-body p-0">
                    <div class="row g-0 messenger-layout">

                        <!-- Sidebar -->
                        <aside class="col-12 col-lg-4 col-xl-3 messenger-sidebar border-end" data-messenger-user-id="<?= (int) $_SESSION['user_id'] ?>">
                            <div id="messenger-contacts-loading" class="p-3 text-center">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading contacts...</span>
                                </div>

                                <span class="ms-2 small text-body-secondary">
                                    Loading contacts...
                                </span>
                            </div>

                            <div id="messenger-contacts-error" class="alert alert-danger m-3 d-none" role="alert"></div>

                            <div class="p-3 border-bottom">
                                <button id="messenger-select-conversations" type="button" class="btn btn-sm btn-outline-secondary" aria-pressed="false">
                                    Select conversations
                                </button>

                                <div id="messenger-bulk-controls" class="mt-2 d-none">
                                    <p id="messenger-selection-count" class="small mb-2" role="status">
                                        0 selected
                                    </p>

                                    <div class="d-flex flex-wrap gap-2">
                                        <button id="messenger-delete-selected" type="button" class="btn btn-sm btn-outline-danger" disabled>
                                            Delete selected
                                        </button>

                                        <button id="messenger-selection-cancel" type="button" class="btn btn-sm btn-outline-secondary">
                                            Cancel
                                        </button>
                                    </div>
                                </div>

                                <p id="messenger-delete-error" class="small text-danger mt-2 mb-0 d-none" role="alert"></p>
                            </div>

                            <!-- Administration Section -->
                            <details class="messenger-section admin-section border-bottom" data-messenger-section="administration" open>

                                <summary class="messenger-section-label messenger-section-toggle px-3 py-3">
                                    <span class="fw-bold text-uppercase small">
                                        Administration
                                    </span>

                                    <span class="d-flex align-items-center gap-2">
                                        <span id="messenger-admin-unread-total" class="badge rounded-pill text-bg-danger d-none"></span>

                                        <i class="fa-solid fa-chevron-down messenger-section-chevron" aria-hidden="true"></i>
                                    </span>
                                </summary>

                                <div id="messenger-admin-conversations" class="list-group list-group-flush messenger-thread-list messenger-section-list"></div>
                            </details>

                            <!-- Dispatch Section -->
                            <details class="messenger-section dispatch-section border-bottom" data-messenger-section="dispatch" open>

                                <summary class="messenger-section-label messenger-section-toggle px-3 py-3">
                                    <span class="fw-bold text-uppercase small">
                                        Dispatch Channel
                                    </span>

                                    <span class="d-flex align-items-center gap-2">
                                        <span id="messenger-dispatch-unread-total" class="badge rounded-pill text-bg-danger d-none"></span>

                                        <i class="fa-solid fa-chevron-down messenger-section-chevron" aria-hidden="true"></i>
                                    </span>
                                </summary>

                                <div id="messenger-dispatch-conversations" class="list-group list-group-flush messenger-section-list"></div>
                            </details>

                            <!-- Driver Section -->
                            <details class="messenger-section driver-section" data-messenger-section="drivers" open>

                                <summary class="messenger-section-label messenger-section-toggle px-3 py-3">
                                    <span class="fw-bold text-uppercase small">
                                        Driver Conversations
                                    </span>

                                    <span class="d-flex align-items-center gap-2">
                                        <span id="messenger-driver-unread-total" class="badge rounded-pill text-bg-danger d-none"></span>

                                        <i class="fa-solid fa-chevron-down messenger-section-chevron" aria-hidden="true"></i>
                                    </span>
                                </summary>

                                <div class="px-3 pb-3">
                                    <label for="messenger-conversation-search" class="visually-hidden">
                                        Search driver conversations
                                    </label>

                                    <input id="messenger-conversation-search" type="search" class="form-control form-control-sm" placeholder="Search conversations" autocomplete="off">
                                </div>

                                <div id="messenger-driver-conversations" class="list-group list-group-flush messenger-thread-list messenger-section-list"></div>

                                <p id="messenger-conversations-empty" class="small text-body-secondary text-center p-3 mb-0 d-none">
                                    No matching conversations. Choose New Message to start one.
                                </p>
                            </details>
                        </aside>

                        <!-- Chat Panel -->
                        <section class="col-12 col-lg-8 col-xl-9 messenger-main">
                            <div class="d-flex flex-column messenger-chat-pane">

                                <!-- Active Conversation Header -->
                                <div class="messenger-chat-header border-bottom p-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 id="messenger-conversation-name" class="h6 mb-1">
                                            Select a contact
                                        </h3>

                                        <small id="messenger-conversation-description" class="text-body-secondary">
                                            Choose someone from the contact list.
                                        </small>
                                    </div>
                                    <button id="messenger-delete-conversation" type="button" class="btn btn-sm btn-outline-danger ms-2 flex-shrink-0" disabled>
                                        Delete conversation
                                    </button>
                                </div>

                                <!-- Conversation Error -->
                                <div id="messenger-conversation-error" class="alert alert-danger m-3 d-none" role="alert"></div>

                                <!-- Message Area -->
                                <div id="messenger-message-area" class="messenger-message-area flex-grow-1 p-3" aria-busy="false">

                                    <div id="messenger-conversation-loading" class="text-center py-4 d-none" role="status">
                                        <span class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></span>
                                        <span class="ms-2">Opening conversation...</span>
                                    </div>

                                    <p id="messenger-conversation-placeholder" class="text-body-secondary text-center py-4 mb-0" role="status">
                                        Select a contact to open a conversation.
                                    </p>

                                    <div id="messenger-older-controls" class="text-center mb-3 d-none">
                                        <button id="messenger-load-older" type="button" class="btn btn-sm btn-outline-secondary">
                                            Load older messages
                                        </button>

                                        <p id="messenger-older-error" class="small text-danger mt-2 mb-0 d-none" role="alert"></p>
                                    </div>

                                    <div id="messenger-message-list"></div>
                                </div>

                                <!-- Composer -->
                                <div class="messenger-composer border-top p-3">
                                    <form id="messenger-message-form">
                                        <div class="row g-2 align-items-end">
                                            <div class="col">
                                                <label for="messengerMessage" class="visually-hidden">Message</label>
                                                <textarea id="messengerMessage" class="form-control" rows="2" placeholder="Type your message..." disabled></textarea>
                                            </div>

                                            <input id="drvrToken" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                                            
                                            <div class="col-12 col-md-auto">
                                                <button id="messenger-send-button" type="submit" class="btn btn-primary w-100" disabled>
                                                    Send
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<dialog id="messenger-contact-picker" class="messenger-contact-picker" aria-labelledby="messenger-picker-title">
    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
        <h3 id="messenger-picker-title" class="h5 mb-0">
            New Message
        </h3>

        <button id="messenger-picker-close" type="button" class="btn-close" aria-label="Close contact picker"></button>
    </div>

    <div class="p-3">
        <label for="messenger-picker-search" class="form-label">
            Find a driver or dispatcher
        </label>

        <input id="messenger-picker-search" type="search" class="form-control" placeholder="Search contacts" autocomplete="off">
    </div>

    <div id="messenger-picker-contacts" class="list-group list-group-flush messenger-picker-list"></div>

    <p id="messenger-picker-empty" class="text-body-secondary text-center p-3 mb-0 d-none">
        No matching contacts were found.
    </p>
</dialog>

<?php
        require "partials/footer.php";
?>
