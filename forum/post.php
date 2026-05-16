<?php require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $author = isLoggedIn() ? currentUser() : (trim($_POST['author']) ?: 'Anonymous');
  $body = trim($_POST['body'] ?? '');
  $userId = currentUserId();
  if (strlen($author) > MAX_USERNAME_LENGTH) $author = substr($author, 0, MAX_USERNAME_LENGTH);
  if ($title && $body && strlen($title) <= MAX_TITLE_LENGTH && strlen($body) <= MAX_BODY_LENGTH) {
    $stmt = $db->prepare("INSERT INTO topics (title, author, user_id) VALUES (?, ?, ?)");
    $stmt->bindValue(1, $title, SQLITE3_TEXT);
    $stmt->bindValue(2, $author, SQLITE3_TEXT);
    $stmt->bindValue(3, $userId, SQLITE3_INTEGER);
    $stmt->execute();
    $topicId = $db->lastInsertRowID();

    $stmt = $db->prepare("INSERT INTO replies (topic_id, author, user_id, body) VALUES (?, ?, ?, ?)");
    $stmt->bindValue(1, $topicId, SQLITE3_INTEGER);
    $stmt->bindValue(2, $author, SQLITE3_TEXT);
    $stmt->bindValue(3, $userId, SQLITE3_INTEGER);
    $stmt->bindValue(4, $body, SQLITE3_TEXT);
    $stmt->execute();

    header("Location: topic.php?id=$topicId");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Topic - Forum</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="index.php">Forum</a>
    <a href="#">Announcements</a>
    <span class="spacer"></span>
    <?php if (isLoggedIn()): ?>
      <span class="user-badge"><?= htmlspecialchars(currentUser()) ?></span>
      <a href="logout.php" class="auth-link">Logout</a>
    <?php else: ?>
      <a href="login.php" class="auth-link">Login</a>
      <a href="register.php" class="auth-link">Register</a>
    <?php endif; ?>
  </div>

  <div class="content">
    <a href="index.php">&larr; Back to Forum</a>
    <h1>Create New Topic</h1>

    <form method="post" class="reply-form">
      <?php if (!isLoggedIn()): ?>
        <input type="text" name="author" placeholder="Your name (optional)" maxlength="<?= MAX_USERNAME_LENGTH ?>">
      <?php endif; ?>
      <input type="text" name="title" placeholder="Topic title" required maxlength="<?= MAX_TITLE_LENGTH ?>">
      <span class="char-count">0 / <?= MAX_BODY_LENGTH ?></span>
      <textarea name="body" placeholder="Write your post... (max <?= MAX_BODY_LENGTH ?> characters)" required maxlength="<?= MAX_BODY_LENGTH ?>"></textarea>
      <button type="submit">Create Topic</button>
    </form>
  </div>
  <script src="char-count.js"></script>
</body>
</html>
