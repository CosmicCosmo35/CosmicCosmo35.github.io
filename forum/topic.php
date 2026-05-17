<?php require __DIR__ . '/db.php';

$id = (int)($_GET['id'] ?? 0);
$topic = $db->querySingle("SELECT * FROM topics WHERE id = $id", true);
if (!$topic) { header('Location: /forum'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['body'])) {
  if (!isLoggedIn()) { header('Location: /login'); exit; }
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
    $totalPages = max(1, (int)ceil(($db->querySingle("SELECT COUNT(*) FROM replies WHERE topic_id = $id")) / REPLIES_PER_PAGE));
    header("Location: /topic/$id?page=$totalPages");
    exit;
  }
}

if (isset($_GET['delete_reply'])) {
  $rid = (int)$_GET['delete_reply'];
  $reply = $db->querySingle("SELECT * FROM replies WHERE id = $rid", true);
  if ($reply && $reply['topic_id'] == $id && isLoggedIn() && (currentUserId() == $reply['user_id'] || isAdmin())) {
    $db->exec("DELETE FROM replies WHERE id = $rid");
    header("Location: /topic/$id");
    exit;
  }
}

if (isset($_GET['delete_topic'])) {
  if (isLoggedIn() && (currentUserId() == $topic['user_id'] || isAdmin())) {
    $db->exec("DELETE FROM replies WHERE topic_id = $id");
    $db->exec("DELETE FROM topics WHERE id = $id");
    header("Location: /forum");
    exit;
  }
}

$canModify = isLoggedIn() && (currentUserId() == $topic['user_id'] || isAdmin());

$page = max(1, (int)($_GET['page'] ?? 1));
$totalReplies = $db->querySingle("SELECT COUNT(*) FROM replies WHERE topic_id = $id");
$totalPages = max(1, (int)ceil($totalReplies / REPLIES_PER_PAGE));
$offset = ($page - 1) * REPLIES_PER_PAGE;
$replies = $db->query("SELECT * FROM replies WHERE topic_id = $id ORDER BY created_at ASC LIMIT " . REPLIES_PER_PAGE . " OFFSET $offset");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($topic['title']) ?> - Forum</title>
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
    <h1><?= htmlspecialchars($topic['title']) ?></h1>
    <p class="meta">
      by <?= authorLink($topic['author'], $topic['user_id']) ?> &middot; <?= formatDate($topic['created_at']) ?>
      <?= renderTags($topic['tags']) ?>
      <?php if ($canModify): ?>
        &middot; <a href="/edit_topic/<?= $id ?>" style="color:#222">Edit</a>
        &middot; <a href="/topic/<?= $id ?>?delete_topic=1" style="color:#d33" onclick="return confirm('Delete this topic and all replies?')">Delete</a>
      <?php endif; ?>
    </p>

    <?= paginationLinks($page, $totalPages, 'topic.php?id=' . $id . '&') ?>

    <div class="replies">
      <?php
      $hasReplies = false;
      while ($reply = $replies->fetchArray(SQLITE3_ASSOC)):
        $hasReplies = true;
        $canEditReply = isLoggedIn() && (currentUserId() == $reply['user_id'] || isAdmin());
      ?>
      <div class="reply">
        <strong><?= authorLink($reply['author'], $reply['user_id']) ?></strong>
        <span class="meta"><?= formatDate($reply['created_at']) ?></span>
        <?php if ($canEditReply): ?>
          <span class="meta">
            &middot; <a href="/edit_reply/<?= $reply['id'] ?>" style="color:#222">Edit</a>
            &middot; <a href="/topic/<?= $id ?>?delete_reply=<?= $reply['id'] ?>" style="color:#d33" onclick="return confirm('Delete this reply?')">Delete</a>
          </span>
        <?php endif; ?>
        <div class="reply-body"><?= renderMarkdown($reply['body']) ?></div>
      </div>
      <?php endwhile; ?>
      <?php if (!$hasReplies): ?>
      <p>No replies yet.</p>
      <?php endif; ?>
    </div>

    <?= paginationLinks($page, $totalPages, '/topic/' . $id . '?') ?>

    <?php if (isLoggedIn()): ?>
    <form method="post" class="reply-form">
      <h3>Post a reply</h3>
      <textarea name="body" placeholder="Write your reply... (max <?= MAX_REPLY_LENGTH ?> characters)" required maxlength="<?= MAX_REPLY_LENGTH ?>"></textarea>
      <span class="char-count">0 / <?= MAX_REPLY_LENGTH ?></span>
      <p class="meta" style="margin-top:-8px"><?= POST_DELAY ?>s delay after posting.</p>
      <button type="submit">Post Reply</button>
    </form>
    <?php else: ?>
    <p class="login-prompt"><a href="/login">Login</a> to post a reply.</p>
    <?php endif; ?>
  </div>
  <script src="/forum/char-count.js"></script>
</body>
</html>
