<?php require __DIR__ . '/db.php';

$id = (int)($_GET['id'] ?? 0);
$post = $db->querySingle("SELECT * FROM science_posts WHERE id = $id", true);
if (!$post) { header('Location: /science_talk'); exit; }
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
    <a href="/forum">Forum</a>
    <a href="/science_talk">Science Talk</a>
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
    <a href="/science_talk">&larr; Back to Science Talk</a>
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <p class="meta">by <?= authorLink($post['author'], $post['user_id']) ?> &middot; <?= formatDate($post['created_at']) ?></p>

    <?php if ($post['image_path']): ?>
      <div class="science-image">
        <img src="uploads/projects/<?= htmlspecialchars($post['image_path']) ?>" alt="">
      </div>
    <?php endif; ?>

    <div class="body"><?= renderMarkdown($post['body']) ?></div>
  </div>
</body>
</html>
