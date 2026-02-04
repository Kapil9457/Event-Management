<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

require_login();
$user = current_user();

$eventId = (int) ($_GET['event_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM events WHERE id = :id');
$stmt->execute(['id' => $eventId]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: events.php');
    exit;
}

if ($user['role'] !== 'super_admin' && (int) $event['user_id'] !== (int) $user['id']) {
    header('Location: events.php');
    exit;
}

$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $file = $_FILES['photo'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        if ($file['size'] > MAX_UPLOAD_BYTES) {
            $notice = 'File exceeds max size.';
        } elseif (!in_array(mime_content_type($file['tmp_name']), ALLOWED_IMAGE_TYPES, true)) {
            $notice = 'Invalid file type.';
        } else {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $safeName = sprintf('%s_%s.%s', $eventId, uniqid('', true), $extension);
            $destination = UPLOAD_DIR . '/' . $safeName;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = db()->prepare('INSERT INTO photos (event_id, user_id, filename) VALUES (:event_id, :user_id, :filename)');
                $stmt->execute([
                    'event_id' => $eventId,
                    'user_id' => $user['id'],
                    'filename' => $safeName,
                ]);
                $notice = 'Photo uploaded successfully.';
            } else {
                $notice = 'Upload failed.';
            }
        }
    } else {
        $notice = 'Upload error.';
    }
}

$stmt = db()->prepare('SELECT * FROM photos WHERE event_id = :event_id ORDER BY uploaded_at DESC');
$stmt->execute(['event_id' => $eventId]);
$photos = $stmt->fetchAll();

require_once __DIR__ . '/partials_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1">Upload photos</h3>
        <p class="text-muted mb-0">Event: <?= htmlspecialchars($event['name']) ?></p>
    </div>
    <a class="btn btn-outline-secondary" href="events.php">Back to events</a>
</div>
<?php if ($notice): ?>
    <div class="alert alert-info"><?= htmlspecialchars($notice) ?></div>
<?php endif; ?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Select photo</label>
                        <input type="file" name="photo" class="form-control" accept="image/*" required>
                    </div>
                    <button class="btn btn-primary w-100">Upload</button>
                </form>
                <p class="small text-muted mt-3">Max size: 5MB. JPG, PNG, GIF, WEBP only.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="row g-3">
            <?php foreach ($photos as $photo): ?>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <img src="uploads/<?= htmlspecialchars($photo['filename']) ?>" class="card-img-top" alt="Event photo">
                        <div class="card-body">
                            <p class="small text-muted mb-0">Uploaded <?= htmlspecialchars($photo['uploaded_at']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!$photos): ?>
                <p class="text-muted">No photos uploaded yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/partials_footer.php';
?>
