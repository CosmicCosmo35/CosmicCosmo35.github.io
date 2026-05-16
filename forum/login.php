<?php require __DIR__ . '/db.php';

if (isLoggedIn()) {
  header('Location: index.php');
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->bindValue(1, $username, SQLITE3_TEXT);
  $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    header('Location: index.php');
    exit;
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
  <title>Login - Forcount</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="index.php">Forum</a>
    <a href="announcements.php">Announcements</a>
    <span class="spacer"></span>
    <a href="login.php" class="auth-link active">Login</a>
    <a href="register.php" class="auth-link">Register</a>
  </div>

  <div class="content auth-page">
    <h1>Login to Forcount</h1>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" class="auth-form">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <p class="auth-switch">Don't have an account? <a href="register.php">Register</a></p>
  </div>
</body>
</html>
