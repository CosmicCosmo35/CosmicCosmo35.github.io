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

  <div class="content">
    <div class="breadcrumb">
      <a href="announcements.php">Announcements</a> &raquo; <?= htmlspecialchars($announcement['title']) ?>
    </div>

    <div class="postbit">
      <div class="postbit-user">
        <?php
        $annUser = null;
        if ($announcement['user_id']) {
          $annUser = getUserStats($announcement['user_id']);
        }
        if ($annUser): ?>
          <div class="user-avatar">
            <?php $av = getAvatar($annUser['id']); if ($av): ?>
              <img src="<?= $av ?>" alt="">
            <?php else: ?>
              <div class="avatar-letter"><?= strtoupper($annUser['username'][0]) ?></div>
            <?php endif; ?>
          </div>
          <div class="user-title">Admin</div>
          <div class="user-name"><?= authorLink($annUser['username'], $annUser['id']) ?></div>
          <div class="user-stats">
            <span>Posts: <?= $annUser['topics'] + $annUser['replies'] ?></span>
            <span>Joined: <?= formatDate($annUser['created_at']) ?></span>
          </div>
        <?php else: ?>
          <div class="user-avatar"><div class="avatar-letter">?</div></div>
          <div class="user-name"><?= htmlspecialchars($announcement['author']) ?></div>
        <?php endif; ?>
      </div>
      <div class="postbit-body">
        <div class="postbit-header">
          <span class="postbit-num">#1</span>
          <span class="post-date"><?= formatDate($announcement['created_at']) ?></span>
        </div>
        <div class="postbit-content"><?= renderMarkdown($announcement['body']) ?></div>
      </div>
    </div>

    <h2 class="reply-heading">Replies (<?= $replyCount ?>)</h2>

    <?php
    $replies = $db->query("SELECT * FROM announcement_replies WHERE announcement_id = $id ORDER BY created_at ASC");
    $hasReplies = false;
    $replyNum = 1;
    while ($reply = $replies->fetchArray(SQLITE3_ASSOC)):
      $hasReplies = true;
      $replyNum++;
      $replyUser = $reply['user_id'] ? getUserStats($reply['user_id']) : null;
    ?>
    <div class="postbit">
      <div class="postbit-user">
        <?php if ($replyUser): ?>
          <div class="user-avatar">
            <?php $av = getAvatar($replyUser['id']); if ($av): ?>
              <img src="<?= $av ?>" alt="">
            <?php else: ?>
              <div class="avatar-letter"><?= strtoupper($replyUser['username'][0]) ?></div>
            <?php endif; ?>
          </div>
          <div class="user-title">Member</div>
          <div class="user-name"><?= authorLink($replyUser['username'], $replyUser['id']) ?></div>
          <div class="user-stats">
            <span>Posts: <?= $replyUser['topics'] + $replyUser['replies'] ?></span>
            <span>Joined: <?= formatDate($replyUser['created_at']) ?></span>
          </div>
        <?php else: ?>
          <div class="user-avatar"><div class="avatar-letter"><?= strtoupper(($reply['author'] ?: 'A')[0]) ?></div></div>
          <div class="user-name"><?= htmlspecialchars($reply['author']) ?></div>
        <?php endif; ?>
      </div>
      <div class="postbit-body">
        <div class="postbit-header">
          <span class="postbit-num">#<?= $replyNum ?></span>
          <span class="post-date"><?= formatDate($reply['created_at']) ?></span>
        </div>
        <div class="postbit-content"><?= renderMarkdown($reply['body']) ?></div>
      </div>
    </div>
    <?php endwhile; ?>
    <?php if (!$hasReplies): ?>
    <p class="meta" style="margin:10px 0">No replies yet.</p>
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
      <div class="login-prompt">You've used all <?= MAX_ANNOUNCEMENT_REPLIES ?> of your replies on this announcement.</div>
      <?php endif; ?>
    <?php else: ?>
    <div class="login-prompt"><a href="login.php">Login</a> to post a reply.</div>
    <?php endif; ?>
  </div>
  <script src="char-count.js"></script>
</body>
</html>
