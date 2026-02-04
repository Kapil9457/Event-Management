<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();
$user = current_user();

$stmt = db()->prepare('SELECT COUNT(*) as total_events FROM events WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user['id']]);
$eventCount = (int) $stmt->fetchColumn();

$stmt = db()->prepare('SELECT COUNT(*) as total_photos FROM photos WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user['id']]);
$photoCount = (int) $stmt->fetchColumn();

require_once __DIR__ . '/partials_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Welcome back, <?= htmlspecialchars($user['name']) ?></h2>
        <p class="text-muted mb-0">Role: <?= htmlspecialchars(str_replace('_', ' ', $user['role'])) ?></p>
    </div>
    <a class="btn btn-primary" href="events.php">Manage Events</a>
</div>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Total Events</h5>
                <p class="display-6"><?= $eventCount ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Total Uploaded Photos</h5>
                <p class="display-6"><?= $photoCount ?></p>
            </div>
        </div>
    </div>
</div>
<?php
if ($user['role'] === 'super_admin'):
    $stmt = db()->query('SELECT COUNT(*) as total_users FROM users');
    $totalUsers = (int) $stmt->fetchColumn();
?>
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <h5>Super Admin Overview</h5>
        <p class="mb-0">Total registered users: <strong><?= $totalUsers ?></strong></p>
    </div>
</div>
<?php endif; ?>
<?php
require_once __DIR__ . '/partials_footer.php';
?>
