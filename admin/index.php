<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (adminUser() !== null) {
    adminRedirect('dashboard.php');
}

$error = '';
$email = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Please refresh and try again.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if ($email === '' || $password === '') {
            $error = 'Enter email and password.';
        } elseif (!attemptAdminLogin($email, $password)) {
            $error = 'Invalid credentials.';
        } else {
            $next = (string) ($_GET['next'] ?? $_POST['next'] ?? '');
            if ($next !== '' && str_starts_with($next, '/')) {
                header('Location: ' . $next, true, 303);
                exit;
            }
            adminRedirect('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin login · PropGemini</title>
    <link rel="stylesheet" href="<?= h(adminUrl('assets/admin.css')) ?>">
</head>
<body class="login-page">
    <div class="login-orb one" aria-hidden="true"></div>
    <div class="login-orb two" aria-hidden="true"></div>
    <div class="login-card" id="loginCard">
        <div class="brand-mark"><span>P</span></div>
        <p class="eyebrow">PropGemini</p>
        <h1>Welcome back</h1>
        <p>Sign in to manage listings, enquiries and the private client workspace.</p>
        <?php if ($error !== ''): ?>
            <div class="login-error"><?= h($error) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="on" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="next" value="<?= h((string) ($_GET['next'] ?? '')) ?>">
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required value="<?= h($email) ?>" autocomplete="username">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <div class="password-wrap">
                    <input id="password" name="password" type="password" required autocomplete="current-password">
                    <button type="button" id="togglePassword" aria-label="Show password">Show</button>
                </div>
            </div>
            <button class="btn" type="submit" id="loginSubmit">Enter workspace</button>
        </form>
        <div class="login-meta">
            <span>Secure admin access</span>
            <span id="loginClock"></span>
        </div>
    </div>
    <script>
    (() => {
      const toggle = document.getElementById('togglePassword');
      const password = document.getElementById('password');
      const clock = document.getElementById('loginClock');
      const form = document.getElementById('loginForm');
      const submit = document.getElementById('loginSubmit');
      const card = document.getElementById('loginCard');

      toggle?.addEventListener('click', () => {
        const show = password.type === 'password';
        password.type = show ? 'text' : 'password';
        toggle.textContent = show ? 'Hide' : 'Show';
      });

      const tick = () => {
        if (!clock) return;
        clock.textContent = new Date().toLocaleString('en-IN', { hour: '2-digit', minute: '2-digit' });
      };
      tick();
      setInterval(tick, 30000);

      form?.addEventListener('submit', () => {
        submit.disabled = true;
        submit.textContent = 'Signing in…';
        card.style.transform = 'scale(.985)';
      });

      card?.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width - 0.5;
        const y = (e.clientY - rect.top) / rect.height - 0.5;
        card.style.transform = `perspective(900px) rotateX(${(-y * 4).toFixed(2)}deg) rotateY(${(x * 6).toFixed(2)}deg)`;
      });
      card?.addEventListener('mouseleave', () => {
        card.style.transform = '';
      });
    })();
    </script>
</body>
</html>
