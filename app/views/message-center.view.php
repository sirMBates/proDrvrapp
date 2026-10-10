<?php
require "partials/head.php";
require "partials/banner.php";
include "partials/info-modal.php";
?>

<main class="container-fluid my-3 py-4 messenger-page">
    <div class="row">
        <div class="col-12">
            <div class="card messenger-card shadow-sm border-0 overflow-hidden">

                <!-- Page Header -->
                <div class="card-header messenger-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 mb-1">Messenger</h2>
                        <p class="mb-0 small text-body-secondary">
                            Internal communication center
                        </p>
                    </div>

                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            New Message
                        </button>
                    </div>
                </div>

                <!-- Main Messenger Body -->
                <div class="card-body p-0">
                    <div class="row g-0 messenger-layout">

                        <!-- Sidebar -->
                        <aside class="col-12 col-lg-4 col-xl-3 messenger-sidebar border-end">
                            <div id="messenger-contacts-loading" class="p-3 text-center">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading contacts...</span>
                                </div>

                                <span class="ms-2 small text-body-secondary">
                                    Loading contacts...
                                </span>
                            </div>

                            <div id="messenger-contacts-error" class="alert alert-danger m-3 d-none" role="alert"></div>

                            <!-- Dispatch Section -->
                            <div class="messenger-section dispatch-section border-bottom">
                                <div class="messenger-section-label px-3 pt-3 pb-2">
                                    <span class="fw-bold text-uppercase small">Dispatch Channel</span>
                                </div>

                                <div id="messenger-dispatch-contacts" class="list-group list-group-flush"></div>
                            </div>

                            <!-- Driver Section -->
                            <div class="messenger-section driver-section">
                                <div class="messenger-section-label px-3 pt-3 pb-2">
                                    <span class="fw-bold text-uppercase small">Driver Conversations</span>
                                </div>

                                <div class="px-3 pb-3">
                                    <input id="messenger-contact-search" type="search" class="form-control form-control-sm" placeholder="Search drivers" autocomplete="off">
                                </div>

                                <div id="messenger-driver-contacts" class="list-group list-group-flush messenger-thread-list"></div>

                                <p id="messenger-contacts-empty" class="small text-body-secondary text-center p-3 mb-0 d-none">
                                    No matching contacts were found.
                                </p>
                            </div>
                        </aside>

                        <!-- Chat Panel -->
                        <section class="col-12 col-lg-8 col-xl-9 messenger-main">
                            <div class="d-flex flex-column messenger-chat-pane">

                                <!-- Active Conversation Header -->
                                <div class="messenger-chat-header border-bottom p-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="h6 mb-1">Dispatch</h3>
                                        <small class="text-body-secondary">Primary communication channel</small>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge text-bg-secondary">2 unread</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary">Details</button>
                                    </div>
                                </div>

                                <!-- Message Area -->
                                <div class="messenger-message-area flex-grow-1 p-3">

                                    <div class="text-center mb-3">
                                        <span class="badge text-bg-light border">Today</span>
                                    </div>

                                    <!-- Dispatch incoming -->
                                    <div class="message-row message-row-received mb-3">
                                        <div class="message-meta small mb-1 dispatch-label">
                                            Dispatch · 10:02 AM
                                        </div>
                                        <div class="message-bubble dispatch-bubble">
                                            Pickup delayed about 15 minutes.
                                        </div>
                                    </div>

                                    <!-- Driver sent -->
                                    <div class="message-row message-row-sent mb-3 text-end">
                                        <div class="message-meta small text-body-secondary mb-1">
                                            You · 10:04 AM
                                        </div>
                                        <div class="message-bubble sent-bubble d-inline-block">
                                            Copy that. I’m on standby.
                                        </div>
                                    </div>

                                    <!-- Dispatch incoming -->
                                    <div class="message-row message-row-received mb-3">
                                        <div class="message-meta small text-body-secondary mb-1">
                                            Dispatch · 10:06 AM
                                        </div>
                                        <div class="message-bubble dispatch-bubble">
                                            Thanks. I’ll update you once the client confirms.
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <small class="text-body-secondary">Seen 10:07 AM</small>
                                    </div>
                                </div>

                                <!-- Composer -->
                                <div class="messenger-composer border-top p-3">
                                    <form>
                                        <div class="row g-2 align-items-end">
                                            <div class="col">
                                                <label for="messengerMessage" class="visually-hidden">Message</label>
                                                <textarea
                                                    id="messengerMessage"
                                                    class="form-control"
                                                    rows="2"
                                                    placeholder="Type your message..."></textarea>
                                            </div>

                                            <input id="drvrToken" type="hidden" class="form-control" name="drvrtoken" value="<?= $_SESSION['drvr_token']?>" required>
                                            
                                            <div class="col-12 col-md-auto">
                                                <button type="submit" class="btn btn-primary w-100">
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

<?php
        require "partials/footer.php";
?>