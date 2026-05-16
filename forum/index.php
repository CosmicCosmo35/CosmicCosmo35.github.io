<?php require __DIR__ . '/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forum - Awesome Science</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="index.php" class="active">Forum</a>
    <a href="#">Announcements</a>
  </div>

  <div class="content">
    <h1>Forum</h1>
    <a href="post.php" class="btn">+ New Topic</a>

    <table class="topic-table">
      <tr><th>Topic</th><th>Author</th><th>Replies</th><th>Last updated</th></tr>
      <?php
      $result = $db->query("SELECT t.*, (SELECT COUNT(*) FROM replies WHERE topic_id = t.id) AS reply_count FROM topics t ORDER BY t.created_at DESC");
      while ($row = $result->fetchArray(SQLITE3_ASSOC)):
      ?>
      <tr>
        <td><a href="topic.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></td>
        <td><?= htmlspecialchars($row['author']) ?></td>
        <td><?= $row['reply_count'] ?></td>
        <td><?= $row['created_at'] ?></td>
      </tr>
      <?php endwhile; ?>
      <?php if ($result->numColumns() && !$result->fetchArray() && !$result->numColumns()): ?>
      <tr><td colspan="4">No topics yet. Be the first!</td></tr>
      <?php endif; ?>
    </table>
  </div>
</body>
</html>
