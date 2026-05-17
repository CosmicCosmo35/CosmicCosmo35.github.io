<?php require __DIR__ . '/db.php';

$userId = (int)($_GET['id'] ?? currentUserId());
if (!$userId) { header('Location: index.php'); exit; }

$user = $db->querySingle("SELECT id, username, created_at, avatar FROM users WHERE id = $userId", true);
if (!$user) { header('Location: index.php'); exit; }

$topicCount = $db->querySingle("SELECT COUNT(*) FROM topics WHERE user_id = $userId");
$replyCount = $db->querySingle("SELECT COUNT(*) FROM replies WHERE user_id = $userId");
$announceReplyCount = $db->querySingle("SELECT COUNT(*) FROM announcement_replies WHERE user_id = $userId");
$scienceCount = $db->querySingle("SELECT COUNT(*) FROM science_posts WHERE user_id = $userId");
$isOwn = isLoggedIn() && currentUserId() == $userId;

$avatarUrl = getAvatar($userId);
$uploadError = '';

if ($isOwn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
  $file = $_FILES['avatar'];
  $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
  if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= MAX_FILE_SIZE && in_array($file['type'], $allowed)) {
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $dest = AVATAR_DIR . '/' . $userId . '.' . $ext;
    foreach (glob(AVATAR_DIR . '/' . $userId . '.*') as $f) unlink($f);
    move_uploaded_file($file['tmp_name'], $dest);
    header("Location: profile.php?id=$userId");
    exit;
  } else {
    $uploadError = 'Invalid image (max 5MB, jpg/png/gif/webp).';
  }
}
$avatarUrl = getAvatar($userId);
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

  <div class="content" style="max-width:520px">
    <div class="profile-card">
      <div class="profile-header">
        <div class="profile-avatar">
          <?php if ($avatarUrl): ?>
            <img src="<?= $avatarUrl ?>" alt="">
          <?php else: ?>
            <div class="profile-placeholder"><?= strtoupper($user['username'][0]) ?></div>
          <?php endif; ?>
        </div>
        <div>
          <h1><?= htmlspecialchars($user['username']) ?></h1>
          <?php if ($isOwn): ?>
            <p class="meta" style="margin-top:2px">This is you.</p>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($isOwn && $uploadError): ?>
        <p class="error"><?= htmlspecialchars($uploadError) ?></p>
      <?php endif; ?>

      <?php if ($isOwn): ?>
      <form method="post" class="avatar-form" enctype="multipart/form-data">
        <label class="file-label">Change avatar: <input type="file" name="avatar" accept="image/*" required></label>
        <button type="submit">Upload</button>
      </form>
      <?php endif; ?>

      <table class="profile-table">
        <tr><td>Member since</td><td><?= formatDate($user['created_at']) ?></td></tr>
        <tr><td>Topics created</td><td><?= $topicCount ?></td></tr>
        <tr><td>Replies posted</td><td><?= $replyCount ?></td></tr>
        <tr><td>Science posts</td><td><?= $scienceCount ?></td></tr>
        <tr><td>Announcement replies</td><td><?= $announceReplyCount ?></td></tr>
      </table>
    </div>
  </div>
</body>
</html>
