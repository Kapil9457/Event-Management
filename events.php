<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

require_login();
$user = current_user();

$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $eventDate = $_POST['event_date'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name && $eventDate) {
        $token = bin2hex(random_bytes(12));
        $stmt = db()->prepare('INSERT INTO events (user_id, name, event_date, location, description, qr_token) VALUES (:user_id, :name, :event_date, :location, :description, :qr_token)');
        $stmt->execute([
            'user_id' => $user['id'],
            'name' => $name,
            'event_date' => $eventDate,
            'location' => $location,
            'description' => $description,
            'qr_token' => $token,
        ]);
        $notice = 'Event created successfully.';
    } else {
        $notice = 'Please provide an event name and date.';
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
