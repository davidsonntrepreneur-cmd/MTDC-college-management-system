<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . base_url(role_home()));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if ($u === '' || $p === '') {
        $error = 'Please enter username and password.';
    } elseif (attempt_login($u, $p)) {
        header('Location: ' . base_url(role_home()));
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="login-wrap">
  <div class="login-card">
    <h1 class="login-title">Portal Login</h1>
    <p class="login-sub">Students · Faculty · Administration</p>
    <?php if ($error): ?><div class="alert alert-err"><?= e($error) ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <label>Username / Admission No.</label>
      <input type="text" name="username" required autofocus>
      <label>Password</label>
      <input type="password" name="password" required>
      <button class="btn btn-primary btn-block" type="submit">Sign In</button>
    </form>
    <p class="login-hint">Students: your username is your admission number.<br>Forgot password? Contact the college office.</p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
