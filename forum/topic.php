<?php require __DIR__ . '/db.php';

$id = (int)($_GET['id'] ?? 0);
$topic = $db->querySingle("SELECT * FROM topics WHERE id = $id", true);
if (!$topic) { header('Location: index.php'); exit; }

$replyCount = $db->querySingle("SELECT COUNT(*) FROM replies WHERE topic_id = $id");
$topicUserStats = $topic['user_id'] ? getUserStats($topic['user_id']) : null;

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

  <div class="content">
    <div class="breadcrumb">
      <a href="index.php">Forum</a> &raquo; <?= htmlspecialchars($topic['title']) ?>
    </div>

    <div class="postbit" id="post-topic">
      <div class="postbit-user">
        <?php if ($topicUserStats): ?>
          <div class="user-avatar">
            <?php $av = getAvatar($topicUserStats['id']); if ($av): ?>
              <img src="<?= $av ?>" alt="">
            <?php else: ?>
              <div class="avatar-letter"><?= strtoupper($topicUserStats['username'][0]) ?></div>
            <?php endif; ?>
          </div>
          <div class="user-name"><?= authorLink($topicUserStats['username'], $topicUserStats['id']) ?></div>
          <div class="user-stats">
            <span>Posts: <?= $topicUserStats['topics'] + $topicUserStats['replies'] ?></span>
            <span>Joined: <?= formatDate($topicUserStats['created_at']) ?></span>
          </div>
        <?php else: ?>
          <div class="user-avatar"><div class="avatar-letter">?</div></div>
          <div class="user-name"><?= htmlspecialchars($topic['author']) ?></div>
        <?php endif; ?>
      </div>
      <div class="postbit-body">
        <div class="postbit-header">
          <span class="post-date"><?= formatDate($topic['created_at']) ?></span>
        </div>
        <div class="postbit-content">
          <?php
          $firstReply = $db->querySingle("SELECT * FROM replies WHERE topic_id = $id ORDER BY created_at ASC LIMIT 1", true);
          if ($firstReply) {
            echo renderMarkdown($firstReply['body']);
          }
          ?>
        </div>
      </div>
    </div>

    <?php
    $replies = $db->query("SELECT * FROM replies WHERE topic_id = $id ORDER BY created_at ASC");
    $first = true;
    while ($reply = $replies->fetchArray(SQLITE3_ASSOC)):
      if ($first) { $first = false; continue; }
      $replyUserStats = $reply['user_id'] ? getUserStats($reply['user_id']) : null;
    ?>
    <div class="postbit">
      <div class="postbit-user">
        <?php if ($replyUserStats): ?>
          <div class="user-avatar">
            <?php $av = getAvatar($replyUserStats['id']); if ($av): ?>
              <img src="<?= $av ?>" alt="">
            <?php else: ?>
              <div class="avatar-letter"><?= strtoupper($replyUserStats['username'][0]) ?></div>
            <?php endif; ?>
          </div>
          <div class="user-name"><?= authorLink($replyUserStats['username'], $replyUserStats['id']) ?></div>
          <div class="user-stats">
            <span>Posts: <?= $replyUserStats['topics'] + $replyUserStats['replies'] ?></span>
            <span>Joined: <?= formatDate($replyUserStats['created_at']) ?></span>
          </div>
        <?php else: ?>
          <div class="user-avatar"><div class="avatar-letter"><?= strtoupper(($reply['author'] ?: 'A')[0]) ?></div></div>
          <div class="user-name"><?= htmlspecialchars($reply['author']) ?></div>
        <?php endif; ?>
      </div>
      <div class="postbit-body">
        <div class="postbit-header">
          <span class="post-date"><?= formatDate($reply['created_at']) ?></span>
        </div>
        <div class="postbit-content"><?= renderMarkdown($reply['body']) ?></div>
      </div>
    </div>
    <?php endwhile; ?>

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
    <div class="login-prompt"><a href="login.php">Login</a> to post a reply.</div>
    <?php endif; ?>
  </div>
  <script src="char-count.js"></script>
</body>
</html>
