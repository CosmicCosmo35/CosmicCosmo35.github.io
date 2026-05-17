<?php require __DIR__ . '/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Announcements - Awesome Science</title>
  <link rel="stylesheet" href="/forum/style.css">
</head>
<body>
  <div class="topbar">
    <img src="/Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="/forum">Forum</a>
    <a href="/science_talk">Science Talk</a>
    <a href="/announcements" class="active">Announcements</a>
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
    <h1>Announcements</h1>
    <?php if (isAdmin()): ?>
      <a href="/post_announcement" class="btn">+ New Announcement</a>
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
      <h2><a href="/announcement/<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></h2>
      <p class="meta">by <?= authorLink($row['author'], $row['user_id']) ?> &middot; <?= formatDate($row['created_at']) ?> &middot; <?= $row['reply_count'] ?> replies</p>
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
