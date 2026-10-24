<div id="dashboard-message-preview" class="card dashboard-message-preview shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center gap-2 bg-prodriverclr text-light">
        <h2 class="h5 mb-0">Messages</h2>

        <span data-message-preview-total class="badge rounded-pill text-bg-danger d-none"></span>
    </div>

    <div class="card-body p-0">
        <p data-message-preview-loading class="text-body-secondary p-3 mb-0" role="status">
            Loading messages...
        </p>

        <p data-message-preview-error class="text-danger p-3 mb-0 d-none" role="alert"></p>

        <p data-message-preview-empty class="text-body-secondary p-3 mb-0 d-none">
            No conversations yet. Open Messenger to start one.
        </p>

        <div data-message-preview-list class="list-group list-group-flush"></div>
    </div>

    <div class="card-footer text-end">
        <a href="/message-center" class="btn btn-sm btn-outline-primary">
            Open Messenger
        </a>
    </div>
</div>