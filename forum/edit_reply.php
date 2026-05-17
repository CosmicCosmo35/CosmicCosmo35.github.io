<?php require __DIR__ . '/db.php';

$id = (int)($_GET['id'] ?? 0);
$reply = $db->querySingle("SELECT * FROM replies WHERE id = $id", true);
if (!$reply) { header('Location: index.php'); exit; }
if (!isLoggedIn() || !(currentUserId() == $reply['user_id'] || isAdmin())) { header('Location: login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $body = trim($_POST['body'] ?? '');
  if ($body && strlen($body) <= MAX_REPLY_LENGTH) {
    $stmt = $db->prepare("UPDATE replies SET body = ? WHERE id = ?");
    $stmt->bindValue(1, $body, SQLITE3_TEXT);
    $stmt->bindValue(2, $id, SQLITE3_INTEGER);
    $stmt->execute();
    header("Location: topic.php?id=" . $reply['topic_id']);
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Reply - Forum</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="index.php">Forum</a>
    <a href="science_talk.php">Science Talk</a>
    <a href="announcements.php">Announcements</a>
    <a href="search.php" class="auth-link">Search</a>
    <span class="spacer"></span>
    <?php if (isLoggedIn()): ?>
      <a href="profile.php" class="user-badge"><?= htmlspecialchars(currentUser()) ?></a>
      <a href="logout.php" class="auth-link">Logout</a>
    <?php endif; ?>
  </div>

  <div class="content">
    <a href="topic.php?id=<?= $reply['topic_id'] ?>">&larr; Back to topic</a>
    <h1>Edit Reply</h1>

    <form method="post" class="reply-form">
      <textarea name="body" required maxlength="<?= MAX_REPLY_LENGTH ?>"><?= htmlspecialchars($reply['body']) ?></textarea>
      <span class="char-count"><?= strlen($reply['body']) ?> / <?= MAX_REPLY_LENGTH ?></span>
      <button type="submit">Save Changes</button>
    </form>
  </div>
  <script src="char-count.js"></script>
</body>
</html>
