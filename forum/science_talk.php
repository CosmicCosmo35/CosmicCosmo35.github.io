<?php require __DIR__ . '/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Science Talk - Awesome Science</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="/forum">Forum</a>
    <a href="/science_talk" class="active">Science Talk</a>
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
    <h1>Science Talk</h1>
    <p class="meta">Share your projects and photos!</p>
    <?php if (isLoggedIn()): ?>
      <a href="/post_science" class="btn">+ New Post</a>
    <?php endif; ?>

    <?php
    $result = $db->query("SELECT * FROM science_posts ORDER BY created_at DESC");
    $hasAny = false;
    while ($row = $result->fetchArray(SQLITE3_ASSOC)):
      $hasAny = true;
    ?>
    <div class="science-card">
      <?php if ($row['image_path']): ?>
        <a href="science_/post?id=<?= $row['id'] ?>"><img src="uploads/projects/<?= htmlspecialchars($row['image_path']) ?>" alt="" class="science-thumb"></a>
      <?php endif; ?>
      <div class="science-info">
        <h2><a href="science_/post?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></h2>
        <p class="meta">by <?= authorLink($row['author'], $row['user_id']) ?> &middot; <?= formatDate($row['created_at']) ?></p>
        <div class="preview-body"><?= renderMarkdown(mb_substr($row['body'], 0, 200)) ?></div>
      </div>
    </div>
    <?php endwhile; ?>
    <?php if (!$hasAny): ?>
    <p>No posts yet.</p>
    <?php endif; ?>
  </div>
</body>
</html>
