<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/security.php';

if (current_user()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = find_user_by_email($email);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        $error = 'Invalid credentials.';
    } else {
        login_user($user);
        set_secure_cookie('last_login', encrypt_value(date('c')));
        header('Location: dashboard.php');
        exit;
    }
}

require_once __DIR__ . '/partials_header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h4 mb-3">Sign in</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Login</button>
                </form>
                <p class="text-muted small mt-3">Use the seeded super admin credentials from database.sql.</p>
            </div>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/partials_footer.php';
?>
