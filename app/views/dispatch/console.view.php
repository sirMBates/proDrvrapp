<div class="container-fluid dispatch-console py-4">
    <div class="row g-4">

        <!-- Sidebar -->
        <aside class="col-12 col-lg-2">
            <div class="card h-100 border-0 shadow-sm dispatch-sidebar">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">ProDriver</h5>

                    <nav class="nav flex-column gap-2">
                        <a href="#" class="nav-link active">Dashboard</a>
                        <a href="#" class="nav-link">Drivers</a>
                        <a href="#" class="nav-link">Messages</a>
                        <a href="#" class="nav-link">Emergencies</a>
                        <a href="#" class="nav-link">Assignments</a>
                        <a href="#" class="nav-link">Settings</a>
                    </nav>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="col-12 col-lg-10">

            <!-- Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <div>
                    <h2 class="fw-bold mb-1">Dispatch Console</h2>
                    <p class="text-body-secondary mb-0">
                        Monitor drivers, assignments, messages, and emergencies.
                    </p>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary">Alerts</button>
                    <button class="btn btn-primary">New Assignment</button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <small class="text-body-secondary">Active Drivers</small>
                            <h3 class="fw-bold mb-0">18</h3>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <small class="text-body-secondary">Emergencies</small>
                            <h3 class="fw-bold text-danger mb-0">1</h3>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <small class="text-body-secondary">Unread Messages</small>
                            <h3 class="fw-bold mb-0">6</h3>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <small class="text-body-secondary">Assignments</small>
                            <h3 class="fw-bold mb-0">12</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">

                <!-- Driver Status -->
                <section class="col-12 col-xl-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Driver Status</h5>
                            <input type="text" class="form-control form-control-sm w-auto" placeholder="Search drivers">
                        </div>

                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">

                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Marvin Bates</strong>
                                        <div class="small text-body-secondary">Assignment #2481</div>
                                    </div>
                                    <span class="badge text-bg-success">Available</span>
                                </div>

                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Mike Johnson</strong>
                                        <div class="small text-body-secondary">Assignment #2483</div>
                                    </div>
                                    <span class="badge text-bg-primary">On Assignment</span>
                                </div>

                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Sarah Davis</strong>
                                        <div class="small text-body-secondary">Assignment #2480</div>
                                    </div>
                                    <span class="badge text-bg-danger">Emergency</span>
                                </div>

                            </div>
                        </div>
                    </div>
                </section>

                <!-- Emergency Panel -->
                <section class="col-12 col-xl-5">
                    <div class="card border-0 shadow-sm h-100 emergency-card">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0 text-danger">Emergency Control</h5>
                        </div>

                        <div class="card-body">
                            <div class="alert alert-danger">
                                <strong>Emergency Active</strong><br>
                                Sarah Davis triggered emergency mode at 11:42 AM.
                            </div>

                            <p class="mb-1"><strong>Assignment:</strong> #2480</p>
                            <p class="mb-3"><strong>Status:</strong> Awaiting dispatch review</p>

                            <textarea class="form-control mb-3" rows="3" placeholder="Add dispatch notes..."></textarea>

                            <div class="d-flex gap-2">
                                <button class="btn btn-danger">Clear Emergency</button>
                                <button class="btn btn-outline-secondary">Keep Active</button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Messages -->
                <section class="col-12 col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0">Messages</h5>
                        </div>

                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Mike Johnson</strong>
                                <p class="small text-body-secondary mb-0">I’m at the lot now.</p>
                            </div>

                            <div class="mb-3">
                                <strong>Route A Team</strong>
                                <p class="small text-body-secondary mb-0">Updated route sheet posted.</p>
                            </div>

                            <div class="mb-3">
                                <strong class="text-danger">System Alert</strong>
                                <p class="small text-body-secondary mb-0">Emergency triggered by Sarah Davis.</p>
                            </div>

                            <button class="btn btn-outline-primary btn-sm">Open Messenger</button>
                        </div>
                    </div>
                </section>

                <!-- Assignments -->
                <section class="col-12 col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Assignments</h5>
                            <button class="btn btn-sm btn-primary">Add</button>
                        </div>

                        <div class="card-body">
                            <div class="border-bottom pb-2 mb-2">
                                <strong>#2483</strong>
                                <div class="small text-body-secondary">Mike Johnson · Pickup 4:30 PM</div>
                            </div>

                            <div class="border-bottom pb-2 mb-2">
                                <strong>#2481</strong>
                                <div class="small text-body-secondary">Marvin Bates · Active</div>
                            </div>

                            <div>
                                <strong>#2480</strong>
                                <div class="small text-body-secondary">Sarah Davis · Emergency Active</div>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </main>
    </div>
</div>