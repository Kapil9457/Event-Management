<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

require_login();
$user = current_user();

$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    if ($action === 'delete') {
        $eventId = (int) ($_POST['event_id'] ?? 0);
        $stmt = db()->prepare('SELECT * FROM events WHERE id = :id');
        $stmt->execute(['id' => $eventId]);
        $event = $stmt->fetch();
        if ($event && ($user['role'] === 'super_admin' || (int) $event['user_id'] === (int) $user['id'])) {
            $deleteStmt = db()->prepare('DELETE FROM events WHERE id = :id');
            $deleteStmt->execute(['id' => $eventId]);
            $notice = 'Event deleted successfully.';
        } else {
            $notice = 'Unable to delete event.';
        }
    } else {
        $name = trim($_POST['name'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $pin = trim($_POST['pin'] ?? '');

        if ($name && $eventDate && $pin) {
            $token = bin2hex(random_bytes(12));
            $pinHash = password_hash($pin, PASSWORD_DEFAULT);
            $stmt = db()->prepare('INSERT INTO events (user_id, name, event_date, location, description, qr_token, pin_hash) VALUES (:user_id, :name, :event_date, :location, :description, :qr_token, :pin_hash)');
            $stmt->execute([
                'user_id' => $user['id'],
                'name' => $name,
                'event_date' => $eventDate,
                'location' => $location,
                'description' => $description,
                'qr_token' => $token,
                'pin_hash' => $pinHash,
            ]);
            $notice = 'Event created successfully.';
        } else {
            $notice = 'Please provide an event name, date, and PIN.';
        }
    }
}

if ($user['role'] === 'super_admin') {
    $stmt = db()->query('SELECT events.*, users.name as owner_name FROM events JOIN users ON users.id = events.user_id ORDER BY event_date DESC');
} else {
    $stmt = db()->prepare('SELECT events.*, users.name as owner_name FROM events JOIN users ON users.id = events.user_id WHERE events.user_id = :user_id ORDER BY event_date DESC');
    $stmt->execute(['user_id' => $user['id']]);
}
$events = $stmt->fetchAll();

require_once __DIR__ . '/partials_header.php';
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Create new event</h5>
                <?php if ($notice): ?>
                    <div class="alert alert-info"><?= htmlspecialchars($notice) ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Event Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Event Date</label>
                        <input type="date" name="event_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Access PIN</label>
                        <input type="password" name="pin" class="form-control" required>
                        <div class="form-text">Guests must enter this PIN after scanning the QR code.</div>
                    </div>
                    <button class="btn btn-primary w-100">Create Event</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Your events</h5>
                    <span class="badge bg-secondary"><?= count($events) ?> total</span>
                </div>
                <div class="list-group">
                    <?php foreach ($events as $event): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($event['name']) ?></h6>
                                    <p class="mb-1 text-muted"><?= htmlspecialchars($event['event_date']) ?> · <?= htmlspecialchars($event['location']) ?></p>
                                    <small class="text-muted">Owner: <?= htmlspecialchars($event['owner_name']) ?></small>
                                </div>
                                <div class="text-end">
                                    <a class="btn btn-sm btn-outline-primary mb-2" href="upload.php?event_id=<?= $event['id'] ?>">Upload photos</a>
                                    <a class="btn btn-sm btn-outline-secondary mb-2" href="view_event.php?token=<?= $event['qr_token'] ?>">View gallery</a>
                                    <button class="btn btn-sm btn-dark" data-qr-token="<?= $event['qr_token'] ?>">Show QR</button>
                                    <?php if ($user['role'] === 'super_admin' || (int) $event['user_id'] === (int) $user['id']): ?>
                                        <form method="post" class="mt-2" onsubmit="return confirm('Delete this event and all photos?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger w-100">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="qr-preview mt-3" id="qr-<?= $event['id'] ?>"></div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$events): ?>
                        <div class="text-muted">No events yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/partials_footer.php';
?>
