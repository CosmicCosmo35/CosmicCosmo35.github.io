<?php require __DIR__ . '/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Announcements - Awesome Science</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="index.php">Forum</a>
    <a href="announcements.php" class="active">Announcements</a>
    <span class="spacer"></span>
    <?php if (isLoggedIn()): ?>
      <span class="user-badge"><?= htmlspecialchars(currentUser()) ?></span>
      <a href="logout.php" class="auth-link">Logout</a>
    <?php else: ?>
      <a href="login.php" class="auth-link">Login</a>
      <a href="register.php" class="auth-link">Register</a>
    <?php endif; ?>
  </div>

  <div class="content">
    <h1>Announcements</h1>
    <?php if (isAdmin()): ?>
      <a href="post_announcement.php" class="btn">+ New Announcement</a>
    <?php endif; ?>

    <?php
    $result = $db->query("SELECT a.*, (SELECT COUNT(*) FROM announcement_replies WHERE announcement_id = a.id) AS reply_count FROM announcements a ORDER BY a.created_at DESC");
    if ($result->numColumns() === 0) { echo '<p>No announcements yet.</p>'; }
    else {
      $hasAny = false;
      while ($row = $result->fetchArray(SQLITE3_ASSOC)):
        $hasAny = true;
    ?>
    <div class="announcement-preview">
      <h2><a href="announcement.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></h2>
      <p class="meta">by <?= htmlspecialchars($row['author']) ?> &middot; <?= formatDate($row['created_at']) ?> &middot; <?= $row['reply_count'] ?>/<?= MAX_ANNOUNCEMENT_REPLIES ?> replies</p>
      <div class="preview-body"><?= renderMarkdown(mb_substr($row['body'], 0, 200)) ?></div>
    </div>
    <?php
      endwhile;
      if (!$hasAny) echo '<p>No announcements yet.</p>';
    }
    ?>
  </div>
</body>
</html>
