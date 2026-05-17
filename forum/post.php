<?php require __DIR__ . '/db.php';
if (!isLoggedIn()) { header('Location: /login'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $author = currentUser();
  $body = trim($_POST['body'] ?? '');
  $tags = trim($_POST['tags'] ?? '');
  $userId = currentUserId();
  if (!$title || !$body) {
    $error = 'Title and body are required.';
  } elseif (strlen($title) > MAX_TITLE_LENGTH) {
    $error = 'Title too long (max ' . MAX_TITLE_LENGTH . ').';
  } elseif (strlen($body) > MAX_BODY_LENGTH) {
    $error = 'Body too long (max ' . MAX_BODY_LENGTH . ').';
  } elseif (strlen($tags) > MAX_TAGS_LENGTH) {
    $error = 'Tags too long (max ' . MAX_TAGS_LENGTH . ' characters).';
  } else {
    sleep(POST_DELAY);
    $stmt = $db->prepare("INSERT INTO topics (title, author, user_id, tags) VALUES (?, ?, ?, ?)");
    $stmt->bindValue(1, $title, SQLITE3_TEXT);
    $stmt->bindValue(2, $author, SQLITE3_TEXT);
    $stmt->bindValue(3, $userId, SQLITE3_INTEGER);
    $stmt->bindValue(4, $tags, SQLITE3_TEXT);
    $stmt->execute();
    $topicId = $db->lastInsertRowID();

    $stmt = $db->prepare("INSERT INTO replies (topic_id, author, user_id, body) VALUES (?, ?, ?, ?)");
    $stmt->bindValue(1, $topicId, SQLITE3_INTEGER);
    $stmt->bindValue(2, $author, SQLITE3_TEXT);
    $stmt->bindValue(3, $userId, SQLITE3_INTEGER);
    $stmt->bindValue(4, $body, SQLITE3_TEXT);
    $stmt->execute();

    header("Location: /topic/$topicId");
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
  <link rel="stylesheet" href="/forum/style.css">
</head>
<body>
  <div class="topbar">
    <img src="/Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="/forum">Forum</a>
    <a href="/science_talk">Science Talk</a>
    <a href="/announcements">Announcements</a>
    <a href="/search" class="auth-link">Search</a>
    <span class="spacer"></span>
    <?php if (isLoggedIn()): ?>
      <a href="/profile" class="user-badge"><?= htmlspecialchars(currentUser()) ?></a>
      <a href="/logout" class="auth-link">Logout</a>
    <?php else: ?>
      <a href="/login" class="auth-link">Login</a>
      <a href="/register" class="auth-link">Register</a>
    <?php endif; ?>
  </div>

  <div class="content">
    <a href="/forum">&larr; Back to Forum</a>
    <h1>Create New Topic</h1>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" class="reply-form">
      <input type="text" name="title" placeholder="Topic title" required maxlength="<?= MAX_TITLE_LENGTH ?>">
      <input type="text" name="tags" placeholder="Tags (comma-separated, e.g. physics, chemistry)" maxlength="<?= MAX_TAGS_LENGTH ?>">
      <textarea name="body" placeholder="Write your post... (max <?= MAX_BODY_LENGTH ?> characters)" required maxlength="<?= MAX_BODY_LENGTH ?>"></textarea>
      <span class="char-count">0 / <?= MAX_BODY_LENGTH ?></span>
      <p class="meta" style="margin-top:-8px"><?= POST_DELAY ?>s delay after posting.</p>
      <button type="submit">Create Topic</button>
    </form>
  </div>
  <script src="/forum/char-count.js"></script>
</body>
</html>
