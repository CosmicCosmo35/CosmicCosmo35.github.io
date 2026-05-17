<?php require __DIR__ . '/db.php';

$id = (int)($_GET['id'] ?? 0);
$post = $db->querySingle("SELECT * FROM science_posts WHERE id = $id", true);
if (!$post) { header('Location: science_talk.php'); exit; }

$postUser = $post['user_id'] ? getUserStats($post['user_id']) : null;
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

  <div class="content">
    <div class="breadcrumb">
      <a href="science_talk.php">Science Talk</a> &raquo; <?= htmlspecialchars($post['title']) ?>
    </div>

    <div class="postbit">
      <div class="postbit-user">
        <?php if ($postUser): ?>
          <div class="user-avatar">
            <?php $av = getAvatar($postUser['id']); if ($av): ?>
              <img src="<?= $av ?>" alt="">
            <?php else: ?>
              <div class="avatar-letter"><?= strtoupper($postUser['username'][0]) ?></div>
            <?php endif; ?>
          </div>
          <div class="user-title">Member</div>
          <div class="user-name"><?= authorLink($postUser['username'], $postUser['id']) ?></div>
          <div class="user-stats">
            <span>Posts: <?= $postUser['topics'] + $postUser['replies'] ?></span>
            <span>Joined: <?= formatDate($postUser['created_at']) ?></span>
          </div>
        <?php else: ?>
          <div class="user-avatar"><div class="avatar-letter">?</div></div>
          <div class="user-name"><?= htmlspecialchars($post['author']) ?></div>
        <?php endif; ?>
      </div>
      <div class="postbit-body">
        <div class="postbit-header">
          <span class="postbit-num">#1</span>
          <span class="post-date"><?= formatDate($post['created_at']) ?></span>
        </div>

        <?php if ($post['image_path']): ?>
          <div class="science-image">
            <img src="uploads/projects/<?= htmlspecialchars($post['image_path']) ?>" alt="">
          </div>
        <?php endif; ?>

        <div class="postbit-content"><?= renderMarkdown($post['body']) ?></div>
      </div>
    </div>
  </div>
</body>
</html>
