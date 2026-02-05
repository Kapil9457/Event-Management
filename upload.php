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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'upload';
    if ($action === 'delete_photo') {
        $photoId = (int) ($_POST['photo_id'] ?? 0);
        $stmt = db()->prepare('SELECT * FROM photos WHERE id = :id');
        $stmt->execute(['id' => $photoId]);
        $photo = $stmt->fetch();
        if ($photo && ($user['role'] === 'super_admin' || (int) $photo['user_id'] === (int) $user['id'])) {
            $deleteStmt = db()->prepare('DELETE FROM photos WHERE id = :id');
            $deleteStmt->execute(['id' => $photoId]);
            $filePath = UPLOAD_DIR . '/' . $photo['filename'];
            if (is_file($filePath)) {
                unlink($filePath);
            }
            $notice = 'Photo deleted.';
        } else {
            $notice = 'Unable to delete photo.';
        }
    } elseif (isset($_FILES['photos'])) {
        $files = $_FILES['photos'];
        $uploadedCount = 0;
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            if ($files['size'][$i] > MAX_UPLOAD_BYTES) {
                continue;
            }
            if (!in_array(mime_content_type($files['tmp_name'][$i]), ALLOWED_IMAGE_TYPES, true)) {
                continue;
            }
            $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
            $safeName = sprintf('%s_%s.%s', $eventId, uniqid('', true), $extension);
            $destination = UPLOAD_DIR . '/' . $safeName;
            if (move_uploaded_file($files['tmp_name'][$i], $destination)) {
                $stmt = db()->prepare('INSERT INTO photos (event_id, user_id, filename) VALUES (:event_id, :user_id, :filename)');
                $stmt->execute([
                    'event_id' => $eventId,
                    'user_id' => $user['id'],
                    'filename' => $safeName,
                ]);
                $uploadedCount++;
            }
        }
        $notice = $uploadedCount > 0 ? sprintf('Uploaded %d photo(s).', $uploadedCount) : 'No valid photos uploaded.';
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
                        <label class="form-label">Select photos</label>
                        <input type="file" name="photos[]" class="form-control" accept="image/*" multiple required>
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
                        <img src="uploads/<?= htmlspecialchars($photo['filename']) ?>" class="card-img-top" alt="Event photo" loading="lazy">
                        <div class="card-body">
                            <p class="small text-muted mb-2">Uploaded <?= htmlspecialchars($photo['uploaded_at']) ?></p>
                            <?php if ($user['role'] === 'super_admin' || (int) $photo['user_id'] === (int) $user['id']): ?>
                                <form method="post" onsubmit="return confirm('Delete this photo?');">
                                    <input type="hidden" name="action" value="delete_photo">
                                    <input type="hidden" name="photo_id" value="<?= $photo['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger w-100">Delete</button>
                                </form>
                            <?php endif; ?>
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
