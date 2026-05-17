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
    <div class="page-actions">
      <h1>Forum</h1>
      <?php if (isLoggedIn()): ?>
        <a href="post.php" class="btn">+ New Topic</a>
      <?php else: ?>
        <a href="login.php" class="btn">+ New Topic</a>
      <?php endif; ?>
    </div>

    <table class="forum-table">
      <thead>
        <tr>
          <th class="col-topic">Topic</th>
          <th class="col-author">Author</th>
          <th class="col-replies">Replies</th>
          <th class="col-last">Last Post</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $result = $db->query("SELECT t.*, (SELECT COUNT(*) FROM replies WHERE topic_id = t.id) AS reply_count FROM topics t ORDER BY t.created_at DESC");
        $hasAny = false;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)):
          $hasAny = true;
        ?>
        <tr>
          <td class="col-topic">
            <a href="topic.php?id=<?= $row['id'] ?>" class="topic-title"><?= htmlspecialchars($row['title']) ?></a>
          </td>
          <td class="col-author"><?= authorLink($row['author'], $row['user_id']) ?></td>
          <td class="col-replies"><?= $row['reply_count'] ?></td>
          <td class="col-last"><?= formatDate($row['created_at']) ?></td>
        </tr>
        <?php endwhile; ?>
        <?php if (!$hasAny): ?>
        <tr><td colspan="4" class="empty-row">No topics yet. Be the first to post!</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
