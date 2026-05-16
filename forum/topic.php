<?php require __DIR__ . '/db.php';

$id = (int)($_GET['id'] ?? 0);
$topic = $db->querySingle("SELECT * FROM topics WHERE id = $id", true);
if (!$topic) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['body'])) {
  $author = trim($_POST['author']) ?: 'Anonymous';
  $body = trim($_POST['body']);
  if ($body) {
    $stmt = $db->prepare("INSERT INTO replies (topic_id, author, body) VALUES (?, ?, ?)");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $stmt->bindValue(2, $author, SQLITE3_TEXT);
    $stmt->bindValue(3, $body, SQLITE3_TEXT);
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
    <a href="#">Announcements</a>
  </div>

  <div class="content">
    <a href="index.php">&larr; Back to Forum</a>
    <h1><?= htmlspecialchars($topic['title']) ?></h1>
    <p class="meta">by <?= htmlspecialchars($topic['author']) ?> &middot; <?= $topic['created_at'] ?></p>

    <div class="replies">
      <?php
      $replies = $db->query("SELECT * FROM replies WHERE topic_id = $id ORDER BY created_at ASC");
      $hasReplies = false;
      while ($reply = $replies->fetchArray(SQLITE3_ASSOC)):
        $hasReplies = true;
      ?>
      <div class="reply">
        <strong><?= htmlspecialchars($reply['author']) ?></strong>
        <span class="meta"><?= $reply['created_at'] ?></span>
        <p><?= nl2br(htmlspecialchars($reply['body'])) ?></p>
      </div>
      <?php endwhile; ?>
      <?php if (!$hasReplies): ?>
      <p>No replies yet.</p>
      <?php endif; ?>
    </div>

    <form method="post" class="reply-form">
      <h3>Post a reply</h3>
      <input type="text" name="author" placeholder="Your name (optional)">
      <textarea name="body" placeholder="Write your reply..." required></textarea>
      <button type="submit">Post Reply</button>
    </form>
  </div>
</body>
</html>
