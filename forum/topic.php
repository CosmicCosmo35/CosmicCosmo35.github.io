<?php require __DIR__ . '/db.php';

$id = (int)($_GET['id'] ?? 0);
$topic = $db->querySingle("SELECT * FROM topics WHERE id = $id", true);
if (!$topic) { header('Location: index.php'); exit; }

$replyCount = $db->querySingle("SELECT COUNT(*) FROM replies WHERE topic_id = $id");

$topicUser = null;
$topicAvatar = false;
if ($topic['user_id']) {
  $topicUser = $db->querySingle("SELECT id, username FROM users WHERE id = " . $topic['user_id'], true);
  if ($topicUser) $topicAvatar = getAvatar($topicUser['id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['body'])) {
  if (!isLoggedIn()) { header('Location: login.php'); exit; }
  $author = currentUser();
  $body = trim($_POST['body']);
  $userId = currentUserId();
  if ($body && strlen($body) <= MAX_REPLY_LENGTH) {
    sleep(POST_DELAY);
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
    <a href="science_talk.php">Science Talk</a>
    <a href="announcements.php">Announcements</a>
    <span class="spacer"></span>
    <?php if (isLoggedIn()): ?>
      <a href="profile.php" class="user-badge"><?= htmlspecialchars(currentUser()) ?></a>
      <a href="logout.php" class="auth-link">Logout</a>
    <?php else: ?>
      <a href="login.php" class="auth-link">Login</a>
      <a href="register.php" class="auth-link">Register</a>
    <?php endif; ?>
  </div>

  <div class="content topic-layout">
    <div class="topic-main">
      <a href="index.php" class="back-link">&larr; Back to Forum</a>
      <h1><?= htmlspecialchars($topic['title']) ?></h1>

      <div class="replies">
        <?php
        $replies = $db->query("SELECT * FROM replies WHERE topic_id = $id ORDER BY created_at ASC");
        $hasReplies = false;
        while ($reply = $replies->fetchArray(SQLITE3_ASSOC)):
          $hasReplies = true;
          $replyAvatar = $reply['user_id'] ? getAvatar($reply['user_id']) : false;
        ?>
        <div class="reply">
          <div class="reply-side">
            <?php if ($replyAvatar): ?>
              <img src="<?= $replyAvatar ?>" alt="" class="reply-avatar">
            <?php else: ?>
              <div class="reply-avatar placeholder"><?= strtoupper(($reply['author'] ?: 'A')[0]) ?></div>
            <?php endif; ?>
          </div>
          <div class="reply-body-wrap">
            <div class="reply-head">
              <strong><?= authorLink($reply['author'], $reply['user_id']) ?></strong>
              <span class="meta"><?= formatDate($reply['created_at']) ?></span>
            </div>
            <div class="reply-body"><?= renderMarkdown($reply['body']) ?></div>
          </div>
        </div>
        <?php endwhile; ?>
        <?php if (!$hasReplies): ?>
        <p class="empty-state">No replies yet. Be the first to respond!</p>
        <?php endif; ?>
      </div>

      <?php if (isLoggedIn()): ?>
      <form method="post" class="reply-form">
        <h3>Post a reply</h3>
        <textarea name="body" placeholder="Write your reply... (max <?= MAX_REPLY_LENGTH ?> characters)" required maxlength="<?= MAX_REPLY_LENGTH ?>"></textarea>
        <div class="reply-form-footer">
          <span class="char-count">0 / <?= MAX_REPLY_LENGTH ?></span>
          <span class="meta"><?= POST_DELAY ?>s delay</span>
        </div>
        <button type="submit">Post Reply</button>
      </form>
      <?php else: ?>
      <p class="login-prompt"><a href="login.php">Login</a> to post a reply.</p>
      <?php endif; ?>
    </div>

    <div class="topic-sidebar">
      <?php if ($topicUser): ?>
      <div class="sidebar-card">
        <h3 class="sidebar-title">Author</h3>
        <div class="sidebar-user">
          <?php if ($topicAvatar): ?>
            <img src="<?= $topicAvatar ?>" alt="" class="sidebar-avatar">
          <?php else: ?>
            <div class="sidebar-avatar placeholder"><?= strtoupper($topicUser['username'][0]) ?></div>
          <?php endif; ?>
          <div>
            <strong><?= htmlspecialchars($topicUser['username']) ?></strong>
            <a href="profile.php?id=<?= $topicUser['id'] ?>" class="sidebar-link">View profile &rarr;</a>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="sidebar-card">
        <h3 class="sidebar-title">Topic info</h3>
        <div class="sidebar-info">
          <div class="info-row">
            <span class="info-label">Created</span>
            <span class="info-value"><?= formatDate($topic['created_at']) ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Replies</span>
            <span class="info-value"><?= $replyCount ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="char-count.js"></script>
</body>
</html>
