<?php require __DIR__ . '/db.php';

$id = (int)($_GET['id'] ?? 0);
$announcement = $db->querySingle("SELECT * FROM announcements WHERE id = $id", true);
if (!$announcement) { header('Location: announcements.php'); exit; }

$replyCount = $db->querySingle("SELECT COUNT(*) FROM announcement_replies WHERE announcement_id = $id");
$myReplyCount = isLoggedIn() ? $db->querySingle("SELECT COUNT(*) FROM announcement_replies WHERE announcement_id = $id AND user_id = " . currentUserId()) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['body'])) {
  if (!isLoggedIn()) { header('Location: login.php'); exit; }
  if ($myReplyCount >= MAX_ANNOUNCEMENT_REPLIES) {
    $error = 'You have reached the maximum of ' . MAX_ANNOUNCEMENT_REPLIES . ' replies per announcement.';
  } else {
    $author = currentUser();
    $body = trim($_POST['body']);
    $userId = currentUserId();
    if ($body && strlen($body) <= MAX_REPLY_LENGTH) {
      $stmt = $db->prepare("INSERT INTO announcement_replies (announcement_id, author, user_id, body) VALUES (?, ?, ?, ?)");
      $stmt->bindValue(1, $id, SQLITE3_INTEGER);
      $stmt->bindValue(2, $author, SQLITE3_TEXT);
      $stmt->bindValue(3, $userId, SQLITE3_INTEGER);
      $stmt->bindValue(4, $body, SQLITE3_TEXT);
      $stmt->execute();
      header("Location: announcement.php?id=$id");
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
  <title><?= htmlspecialchars($announcement['title']) ?> - Announcements</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="index.php">Forum</a>
    <a href="announcements.php" class="active">Announcements</a>
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
    <a href="announcements.php">&larr; Back to Announcements</a>
    <h1><?= htmlspecialchars($announcement['title']) ?></h1>
    <p class="meta">by <?= htmlspecialchars($announcement['author']) ?> &middot; <?= formatDate($announcement['created_at']) ?></p>
    <div class="body"><?= renderMarkdown($announcement['body']) ?></div>

    <h2 class="reply-heading">Replies (<?= $replyCount ?>)</h2>
    <div class="replies">
      <?php
      $replies = $db->query("SELECT * FROM announcement_replies WHERE announcement_id = $id ORDER BY created_at ASC");
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
      <?php if ($myReplyCount < MAX_ANNOUNCEMENT_REPLIES): ?>
      <form method="post" class="reply-form">
        <h3>Post a reply (<?= $myReplyCount ?>/<?= MAX_ANNOUNCEMENT_REPLIES ?> used)</h3>
        <textarea name="body" placeholder="Write your reply... (max <?= MAX_REPLY_LENGTH ?> characters)" required maxlength="<?= MAX_REPLY_LENGTH ?>"></textarea>
        <span class="char-count">0 / <?= MAX_REPLY_LENGTH ?></span>
        <button type="submit">Post Reply</button>
      </form>
      <?php else: ?>
      <p class="login-prompt">You've used all <?= MAX_ANNOUNCEMENT_REPLIES ?> of your replies on this announcement.</p>
      <?php endif; ?>
    <?php else: ?>
      <p class="login-prompt"><a href="login.php">Login</a> to post a reply.</p>
    <?php endif; ?>
  </div>
  <script src="char-count.js"></script>
</body>
</html>
