<?php require __DIR__ . '/db.php';

$tagFilter = isset($_GET['tag']) ? trim($_GET['tag']) : '';
?>
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
    <a href="science_talk.php">Science Talk</a>
    <a href="announcements.php">Announcements</a>
    <a href="search.php" class="auth-link">Search</a>
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
    <h1>Forum<?= $tagFilter ? ' - Tag: ' . htmlspecialchars($tagFilter) : '' ?></h1>
    <a href="post.php" class="btn">+ New Topic</a>

    <table class="topic-table">
      <tr><th>Topic</th><th>Tags</th><th>Author</th><th>Replies</th><th>Last updated</th></tr>
      <?php
      if ($tagFilter) {
        $stmt = $db->prepare("SELECT t.*, (SELECT COUNT(*) FROM replies WHERE topic_id = t.id) AS reply_count FROM topics t WHERE t.tags LIKE ? ORDER BY t.created_at DESC");
        $stmt->bindValue(1, '%' . $tagFilter . '%', SQLITE3_TEXT);
        $result = $stmt->execute();
      } else {
        $result = $db->query("SELECT t.*, (SELECT COUNT(*) FROM replies WHERE topic_id = t.id) AS reply_count FROM topics t ORDER BY t.created_at DESC");
      }
      while ($row = $result->fetchArray(SQLITE3_ASSOC)):
      ?>
      <tr>
        <td><a href="topic.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></td>
        <td><?= renderTags($row['tags']) ?></td>
        <td><?= authorLink($row['author'], $row['user_id']) ?></td>
        <td><?= $row['reply_count'] ?></td>
        <td><?= formatDate($row['created_at']) ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</body>
</html>
