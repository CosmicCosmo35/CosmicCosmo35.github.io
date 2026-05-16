<?php require __DIR__ . '/db.php';

$id = (int)($_GET['id'] ?? 0);
$topic = $db->querySingle("SELECT * FROM topics WHERE id = $id", true);
if (!$topic) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['body'])) {
  if (!isLoggedIn()) { header('Location: login.php'); exit; }
  $author = currentUser();
  $body = trim($_POST['body']);
  $userId = currentUserId();
  if ($body && strlen($body) <= MAX_REPLY_LENGTH) {
    $stmt = $db->prepare("INSERT INTO replies (topic_id, author, user_id, body) VALUES (?, ?, ?, ?)");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $stmt->bindValue(2, $author, SQLITE3_TEXT);
    $stmt->bindValue(3, $userId, SQLITE3_INTEGER);
    $stmt->bindValue(4, $body, SQLITE3_TEXT);
    $stmt->execute();
    header("Location: topic.php?id=$id");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($topic['title']) ?> - Forum</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="index.php">Forum</a>
    <a href="announcements.php">Announcements</a>
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
    <h1><?= htmlspecialchars($topic['title']) ?></h1>
    <p class="meta">by <?= htmlspecialchars($topic['author']) ?> &middot; <?= formatDate($topic['created_at']) ?></p>

    <div class="replies">
      <?php
      $replies = $db->query("SELECT * FROM replies WHERE topic_id = $id ORDER BY created_at ASC");
      $hasReplies = false;
      while ($reply = $replies->fetchArray(SQLITE3_ASSOC)):
        $hasReplies = true;
      ?>
      <div class="reply">
        <strong><?= htmlspecialchars($reply['author']) ?></strong>
        <span class="meta"><?= formatDate($reply['created_at']) ?></span>
        <div class="reply-body"><?= renderMarkdown($reply['body']) ?></div>
      </div>
      <?php endwhile; ?>
      <?php if (!$hasReplies): ?>
      <p>No replies yet.</p>
      <?php endif; ?>
    </div>

    <?php if (isLoggedIn()): ?>
    <form method="post" class="reply-form">
      <h3>Post a reply</h3>
      <textarea name="body" placeholder="Write your reply... (max <?= MAX_REPLY_LENGTH ?> characters)" required maxlength="<?= MAX_REPLY_LENGTH ?>"></textarea>
      <span class="char-count">0 / <?= MAX_REPLY_LENGTH ?></span>
      <button type="submit">Post Reply</button>
    </form>
    <?php else: ?>
    <p class="login-prompt"><a href="login.php">Login</a> to post a reply.</p>
    <?php endif; ?>
  </div>
  <script src="char-count.js"></script>
</body>
</html>
