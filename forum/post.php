<?php require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $author = trim($_POST['author']) ?: 'Anonymous';
  $body = trim($_POST['body'] ?? '');
  if ($title && $body) {
    $stmt = $db->prepare("INSERT INTO topics (title, author) VALUES (?, ?)");
    $stmt->bindValue(1, $title, SQLITE3_TEXT);
    $stmt->bindValue(2, $author, SQLITE3_TEXT);
    $stmt->execute();
    $topicId = $db->lastInsertRowID();

    $stmt = $db->prepare("INSERT INTO replies (topic_id, author, body) VALUES (?, ?, ?)");
    $stmt->bindValue(1, $topicId, SQLITE3_INTEGER);
    $stmt->bindValue(2, $author, SQLITE3_TEXT);
    $stmt->bindValue(3, $body, SQLITE3_TEXT);
    $stmt->execute();

    header("Location: topic.php?id=$topicId");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Topic - Forum</title>
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
    <h1>Create New Topic</h1>

    <form method="post" class="reply-form">
      <input type="text" name="author" placeholder="Your name (optional)">
      <input type="text" name="title" placeholder="Topic title" required>
      <textarea name="body" placeholder="Write your post..." required></textarea>
      <button type="submit">Create Topic</button>
    </form>
  </div>
</body>
</html>
