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
      sleep(POST_DELAY);
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
    <a href="science_talk.php">Science Talk</a>
    <a href="announcements.php" class="active">Announcements</a>
    <span class="spacer"></span>
    <?php if (isLoggedIn()): ?>
      <a href="profile.php" class="user-badge"><?= htmlspecialchars(currentUser()) ?></a>
      <a href="logout.php" class="auth-link">Logout</a>
    <?php else: ?>
      <a href="login.php" class="auth-link">Login</a>
      <a href="register.php" class="auth-link">Register</a>
    <?php endif; ?>
  </div>

  <div class="content announcement-layout">
    <div class="topic-main">
      <a href="announcements.php" class="back-link">&larr; Back to Announcements</a>
      <h1><?= htmlspecialchars($announcement['title']) ?></h1>
      <div class="body"><?= renderMarkdown($announcement['body']) ?></div>

      <h2 class="reply-heading">Replies (<?= $replyCount ?>)</h2>
      <?php
      $replies = $db->query("SELECT * FROM announcement_replies WHERE announcement_id = $id ORDER BY created_at ASC");
      $hasReplies = false;
      while ($reply = $replies->fetchArray(SQLITE3_ASSOC)):
        $hasReplies = true;
      ?>
      <div class="reply">
        <div class="reply-side">
          <?php if ($reply['user_id'] && $a = getAvatar($reply['user_id'])): ?>
            <img src="<?= $a ?>" alt="" class="reply-avatar">
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
      <p class="empty-state">No replies yet.</p>
      <?php endif; ?>

      <?php if (isLoggedIn()): ?>
        <?php if ($myReplyCount < MAX_ANNOUNCEMENT_REPLIES): ?>
        <form method="post" class="reply-form">
          <h3>Post a reply (<?= $myReplyCount ?>/<?= MAX_ANNOUNCEMENT_REPLIES ?> used)</h3>
          <textarea name="body" placeholder="Write your reply... (max <?= MAX_REPLY_LENGTH ?> characters)" required maxlength="<?= MAX_REPLY_LENGTH ?>"></textarea>
          <div class="reply-form-footer">
            <span class="char-count">0 / <?= MAX_REPLY_LENGTH ?></span>
            <span class="meta"><?= POST_DELAY ?>s delay</span>
          </div>
          <button type="submit">Post Reply</button>
        </form>
        <?php else: ?>
        <p class="login-prompt">You've used all <?= MAX_ANNOUNCEMENT_REPLIES ?> of your replies on this announcement.</p>
        <?php endif; ?>
      <?php else: ?>
      <p class="login-prompt"><a href="login.php">Login</a> to post a reply.</p>
      <?php endif; ?>
    </div>

    <div class="topic-sidebar">
      <?php
      $annUser = null;
      $annAvatar = false;
      if ($announcement['user_id']) {
        $annUser = $db->querySingle("SELECT id, username FROM users WHERE id = " . $announcement['user_id'], true);
        if ($annUser) $annAvatar = getAvatar($annUser['id']);
      }
      ?>
      <?php if ($annUser): ?>
      <div class="sidebar-card">
        <h3 class="sidebar-title">Author</h3>
        <div class="sidebar-user">
          <?php if ($annAvatar): ?>
            <img src="<?= $annAvatar ?>" alt="" class="sidebar-avatar">
          <?php else: ?>
            <div class="sidebar-avatar placeholder"><?= strtoupper($annUser['username'][0]) ?></div>
          <?php endif; ?>
          <div>
            <strong><?= htmlspecialchars($annUser['username']) ?></strong>
            <a href="profile.php?id=<?= $annUser['id'] ?>" class="sidebar-link">View profile &rarr;</a>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="sidebar-card">
        <h3 class="sidebar-title">Info</h3>
        <div class="sidebar-info">
          <div class="info-row">
            <span class="info-label">Posted</span>
            <span class="info-value"><?= formatDate($announcement['created_at']) ?></span>
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
