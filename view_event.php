<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    header('Location: index.php');
    exit;
}

$stmt = db()->prepare('SELECT events.*, users.name as owner_name FROM events JOIN users ON users.id = events.user_id WHERE qr_token = :token LIMIT 1');
$stmt->execute(['token' => $token]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: index.php');
    exit;
}

$_SESSION['verified_events'] = $_SESSION['verified_events'] ?? [];
$pinError = '';
$pinVerified = in_array((int) $event['id'], $_SESSION['verified_events'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = trim($_POST['pin'] ?? '');
    if ($pin && password_verify($pin, $event['pin_hash'])) {
        $_SESSION['verified_events'][] = (int) $event['id'];
        $pinVerified = true;
    } else {
        $pinError = 'Incorrect PIN. Please try again.';
    }
}

if ($pinVerified) {
    $stmt = db()->prepare('SELECT * FROM photos WHERE event_id = :event_id ORDER BY uploaded_at DESC');
    $stmt->execute(['event_id' => $event['id']]);
    $photos = $stmt->fetchAll();
} else {
    $photos = [];
}

set_secure_cookie('last_event', encrypt_value($token), 60 * 60 * 24 * 7);

require_once __DIR__ . '/partials_header.php';
?>
<div class="mb-4">
    <h2 class="mb-1"><?= htmlspecialchars($event['name']) ?></h2>
    <p class="text-muted">Hosted by <?= htmlspecialchars($event['owner_name']) ?> · <?= htmlspecialchars($event['event_date']) ?></p>
    <p><?= htmlspecialchars($event['description']) ?></p>
</div>
<?php if (!$pinVerified): ?>
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Enter Event PIN</h5>
                    <p class="text-muted">Guests must enter the PIN provided by the host to view photos.</p>
                    <?php if ($pinError): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($pinError) ?></div>
                    <?php endif; ?>
                    <form method="post" id="pin-form" data-event-token="<?= htmlspecialchars($token) ?>" data-pin-error="<?= $pinError ? '1' : '0' ?>">
                        <div class="mb-3">
                            <label class="form-label">Event PIN</label>
                            <input type="password" name="pin" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100">Unlock Gallery</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3 premium-gallery" data-event-token="<?= htmlspecialchars($token) ?>">
        <?php foreach ($photos as $photo): ?>
            <div class="col-sm-6 col-lg-4">
                <div class="card shadow-lg border-0 h-100 premium-card">
                    <div class="premium-image-wrapper">
                        <img src="uploads/<?= htmlspecialchars($photo['filename']) ?>" class="card-img-top premium-image" alt="Wedding photo" loading="lazy">
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-0">Uploaded <?= htmlspecialchars($photo['uploaded_at']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$photos): ?>
            <p class="text-muted">No photos yet. Ask the host to upload.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php
require_once __DIR__ . '/partials_footer.php';
?>
