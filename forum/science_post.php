<?php require __DIR__ . '/db.php';

$id = (int)($_GET['id'] ?? 0);
$post = $db->querySingle("SELECT * FROM science_posts WHERE id = $id", true);
if (!$post) { header('Location: science_talk.php'); exit; }

$postUser = null;
$postAvatar = false;
if ($post['user_id']) {
  $postUser = $db->querySingle("SELECT id, username FROM users WHERE id = " . $post['user_id'], true);
  if ($postUser) $postAvatar = getAvatar($postUser['id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($post['title']) ?> - Science Talk</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="index.php">Forum</a>
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

  <div class="content topic-layout">
    <div class="topic-main">
      <a href="science_talk.php" class="back-link">&larr; Back to Science Talk</a>
      <h1><?= htmlspecialchars($post['title']) ?></h1>

      <?php if ($post['image_path']): ?>
        <div class="science-image">
          <img src="uploads/projects/<?= htmlspecialchars($post['image_path']) ?>" alt="">
        </div>
      <?php endif; ?>

      <div class="body"><?= renderMarkdown($post['body']) ?></div>
    </div>

    <div class="topic-sidebar">
      <?php if ($postUser): ?>
      <div class="sidebar-card">
        <h3 class="sidebar-title">Author</h3>
        <div class="sidebar-user">
          <?php if ($postAvatar): ?>
            <img src="<?= $postAvatar ?>" alt="" class="sidebar-avatar">
          <?php else: ?>
            <div class="sidebar-avatar placeholder"><?= strtoupper($postUser['username'][0]) ?></div>
          <?php endif; ?>
          <div>
            <strong><?= htmlspecialchars($postUser['username']) ?></strong>
            <a href="profile.php?id=<?= $postUser['id'] ?>" class="sidebar-link">View profile &rarr;</a>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="sidebar-card">
        <h3 class="sidebar-title">Info</h3>
        <div class="sidebar-info">
          <div class="info-row">
            <span class="info-label">Posted</span>
            <span class="info-value"><?= formatDate($post['created_at']) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
