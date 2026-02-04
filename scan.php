<?php
require_once __DIR__ . '/partials_header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Scan QR code</h5>
                <p class="text-muted">Scan the QR code on your invitation to view the wedding photo gallery.</p>
                <div id="qr-reader" class="border rounded p-3"></div>
                <div class="alert alert-info mt-3" id="qr-result" role="alert" hidden></div>
            </div>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/partials_footer.php';
?>
