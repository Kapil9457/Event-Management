<?php
require_once __DIR__ . '/partials_header.php';
?>
<div class="row align-items-center g-4">
    <div class="col-lg-6">
        <h1 class="display-5 fw-bold">Capture every wedding moment</h1>
        <p class="lead">A secure event management system for wedding photo uploads, QR access, and curated galleries for every celebration.</p>
        <div class="d-flex gap-3">
            <a class="btn btn-primary btn-lg" href="login.php">Get Started</a>
            <a class="btn btn-outline-secondary btn-lg" href="scan.php">Scan QR</a>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">What you can do</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Super admin controls all users and events.</li>
                    <li class="list-group-item">Users manage their events and upload photos securely.</li>
                    <li class="list-group-item">Guests view galleries via QR scanning.</li>
                    <li class="list-group-item">Encrypted cookies help streamline repeat access.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/partials_footer.php';
?>
