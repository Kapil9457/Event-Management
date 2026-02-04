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

$stmt = db()->prepare('SELECT * FROM photos WHERE event_id = :event_id ORDER BY uploaded_at DESC');
$stmt->execute(['event_id' => $event['id']]);
$photos = $stmt->fetchAll();

set_secure_cookie('last_event', encrypt_value($token), 60 * 60 * 24 * 7);

require_once __DIR__ . '/partials_header.php';
?>
<div class="mb-4">
    <h2 class="mb-1"><?= htmlspecialchars($event['name']) ?></h2>
    <p class="text-muted">Hosted by <?= htmlspecialchars($event['owner_name']) ?> · <?= htmlspecialchars($event['event_date']) ?></p>
    <p><?= htmlspecialchars($event['description']) ?></p>
</div>
<div class="row g-3">
    <?php foreach ($photos as $photo): ?>
        <div class="col-sm-6 col-lg-4">
            <div class="card shadow-sm h-100">
                <img src="uploads/<?= htmlspecialchars($photo['filename']) ?>" class="card-img-top" alt="Wedding photo">
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (!$photos): ?>
        <p class="text-muted">No photos yet. Ask the host to upload.</p>
    <?php endif; ?>
</div>
<?php
require_once __DIR__ . '/partials_footer.php';
?>
