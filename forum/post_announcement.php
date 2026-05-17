<?php require __DIR__ . '/db.php';

if (!isAdmin()) { header('Location: /announcements'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $body = trim($_POST['body'] ?? '');
  if ($title && $body && strlen($title) <= MAX_TITLE_LENGTH && strlen($body) <= MAX_BODY_LENGTH) {
    sleep(POST_DELAY);
    $stmt = $db->prepare("INSERT INTO announcements (title, author, user_id, body) VALUES (?, ?, ?, ?)");
    $stmt->bindValue(1, $title, SQLITE3_TEXT);
    $stmt->bindValue(2, currentUser(), SQLITE3_TEXT);
    $stmt->bindValue(3, currentUserId(), SQLITE3_INTEGER);
    $stmt->bindValue(4, $body, SQLITE3_TEXT);
    $stmt->execute();
    $id = $db->lastInsertRowID();
    header("Location: /announcement/$id");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Announcement</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="/forum">Forum</a>
    <a href="/science_talk">Science Talk</a>
    <a href="/announcements">Announcements</a>
    <a href="/search" class="auth-link">Search</a>
    <span class="spacer"></span>
    <a href="/profile" class="user-badge"><?= htmlspecialchars(currentUser()) ?></a>
    <a href="/logout" class="auth-link">Logout</a>
  </div>

  <div class="content">
    <a href="/announcements">&larr; Back to Announcements</a>
    <h1>Create New Announcement</h1>

    <form method="post" class="reply-form">
      <input type="text" name="title" placeholder="Announcement title" required maxlength="<?= MAX_TITLE_LENGTH ?>">
      <span class="char-count">0 / <?= MAX_BODY_LENGTH ?></span>
      <textarea name="body" placeholder="Write your announcement... Markdown supported (max <?= MAX_BODY_LENGTH ?> characters)" required maxlength="<?= MAX_BODY_LENGTH ?>"></textarea>
      <p class="meta" style="margin-top:-8px"><?= POST_DELAY ?>s delay after posting.</p>
      <button type="submit">Post Announcement</button>
    </form>
  </div>
  <script src="char-count.js"></script>
</body>
</html>
