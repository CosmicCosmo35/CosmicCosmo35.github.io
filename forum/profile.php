<?php require __DIR__ . '/db.php';

$userId = (int)($_GET['id'] ?? currentUserId());
if (!$userId) { header('Location: index.php'); exit; }

$user = $db->querySingle("SELECT id, username, created_at FROM users WHERE id = $userId", true);
if (!$user) { header('Location: index.php'); exit; }

$topicCount = $db->querySingle("SELECT COUNT(*) FROM topics WHERE user_id = $userId");
$replyCount = $db->querySingle("SELECT COUNT(*) FROM replies WHERE user_id = $userId");
$announceReplyCount = $db->querySingle("SELECT COUNT(*) FROM announcement_replies WHERE user_id = $userId");
$isOwn = isLoggedIn() && currentUserId() == $userId;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($user['username']) ?> - Profile</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="index.php">Forum</a>
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

  <div class="content" style="max-width:500px">
    <h1><?= htmlspecialchars($user['username']) ?></h1>
    <?php if ($isOwn): ?>
      <p class="meta" style="margin-bottom:24px">This is you.</p>
    <?php endif; ?>

    <table class="profile-table">
      <tr><td>Member since</td><td><?= formatDate($user['created_at']) ?></td></tr>
      <tr><td>Topics created</td><td><?= $topicCount ?></td></tr>
      <tr><td>Replies posted</td><td><?= $replyCount ?></td></tr>
      <tr><td>Announcement replies</td><td><?= $announceReplyCount ?></td></tr>
    </table>
  </div>
</body>
</html>
