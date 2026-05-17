<?php require __DIR__ . '/db.php';

if (isLoggedIn()) {
  header('Location: /forum');
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm'] ?? '';

  if (strlen($username) < 3 || strlen($username) > MAX_USERNAME_LENGTH) {
    $error = 'Username must be 3-' . MAX_USERNAME_LENGTH . ' characters.';
  } elseif ($password !== $confirm) {
    $error = 'Passwords do not match.';
  } elseif (strlen($password) < 4) {
    $error = 'Password must be at least 4 characters.';
  } else {
    $existing = $db->prepare("SELECT id FROM users WHERE username = ?");
    $existing->bindValue(1, $username, SQLITE3_TEXT);
    if ($existing->execute()->fetchArray()) {
      $error = 'Username already taken.';
    } else {
      sleep(POST_DELAY);
      $stmt = $db->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
      $stmt->bindValue(1, $username, SQLITE3_TEXT);
      $stmt->bindValue(2, password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
      $stmt->execute();
      $_SESSION['user_id'] = $db->lastInsertRowID();
      $_SESSION['username'] = $username;
      header('Location: /forum');
      exit;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Forcount</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="/forum">Forum</a>
    <a href="/science_talk">Science Talk</a>
    <a href="/announcements">Announcements</a>
    <a href="/search" class="auth-link">Search</a>
    <span class="spacer"></span>
    <a href="/login" class="auth-link">Login</a>
    <a href="/register" class="auth-link active">Register</a>
  </div>

  <div class="content auth-page">
    <h1>Create a Forcount</h1>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" class="auth-form">
      <input type="text" name="username" placeholder="Username" required minlength="3" maxlength="<?= MAX_USERNAME_LENGTH ?>">
      <input type="password" name="password" placeholder="Password" required minlength="4">
      <input type="password" name="confirm" placeholder="Confirm password" required>
      <button type="submit">Register</button>
    </form>
    <p class="auth-switch">Already have an account? <a href="/login">Login</a></p>
  </div>
</body>
</html>
