<?php
session_start();

$ADMIN_USER = 'admin';
$ADMIN_PASS = 'password123';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    if ($user === $ADMIN_USER && $pass === $ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin-add-blog.php');
        exit();
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { background: #f7f8fa; font-family: 'Inter', Arial, sans-serif; margin: 0; }
        .login-container {
            max-width: 340px;
            margin: 80px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 32px 28px 24px 28px;
        }
        h2 { text-align: center; font-size: 1.5rem; font-weight: 600; color: #222; margin-bottom: 24px; }
        label { font-weight: 600; color: #333; display: block; margin-bottom: 6px; margin-top: 18px; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; margin-bottom: 8px; background: #fafbfc; transition: border 0.2s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border: 1.5px solid #6366f1; outline: none; background: #fff;
        }
        button[type="submit"] {
            width: 100%; background: #6366f1; color: #fff; font-weight: 600; font-size: 1.1rem; border: none; border-radius: 6px; padding: 12px 0; margin-top: 18px; cursor: pointer; box-shadow: 0 2px 8px rgba(99,102,241,0.08); transition: background 0.2s, box-shadow 0.2s;
        }
        button[type="submit"]:hover { background: #4f46e5; box-shadow: 0 4px 16px rgba(99,102,241,0.13); }
        .error { color: #e11d48; text-align: center; margin-top: 10px; font-weight: 600; }
    </style>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-937335270">
</script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-937335270');
</script>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <form method="POST" autocomplete="off">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required autofocus>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>
        </form>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
    </div>
</body>
</html> 