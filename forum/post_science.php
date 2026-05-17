<?php require __DIR__ . '/db.php';
if (!isLoggedIn()) { header('Location: login.php'); exit; }

$error = '';
$imageName = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $body = trim($_POST['body'] ?? '');
  if (!empty($_FILES['image']['name'])) {
    $imageName = uploadImage($_FILES['image'], PROJECT_DIR);
    if (!$imageName) $error = 'Invalid or oversize image (max 5MB, jpg/png/gif/webp).';
  }
  if ($title && $body && !$error && strlen($title) <= MAX_TITLE_LENGTH && strlen($body) <= MAX_BODY_LENGTH) {
    sleep(POST_DELAY);
    $stmt = $db->prepare("INSERT INTO science_posts (title, author, user_id, body, image_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bindValue(1, $title, SQLITE3_TEXT);
    $stmt->bindValue(2, currentUser(), SQLITE3_TEXT);
    $stmt->bindValue(3, currentUserId(), SQLITE3_INTEGER);
    $stmt->bindValue(4, $body, SQLITE3_TEXT);
    $stmt->bindValue(5, $imageName, SQLITE3_TEXT);
    $stmt->execute();
    $id = $db->lastInsertRowID();
    header("Location: science_post.php?id=$id");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Post - Science Talk</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="index.php">Forum</a>
    <a href="science_talk.php" class="active">Science Talk</a>
    <a href="announcements.php">Announcements</a>
    <span class="spacer"></span>
    <a href="profile.php" class="user-badge"><?= htmlspecialchars(currentUser()) ?></a>
    <a href="logout.php" class="auth-link">Logout</a>
  </div>

  <div class="content">
    <div class="breadcrumb"><a href="science_talk.php">Science Talk</a> &raquo; New Post</div>
    <h1 style="font-size:16px;margin-bottom:10px">New Science Post</h1>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" class="reply-form" enctype="multipart/form-data">
      <input type="text" name="title" placeholder="Project title" required maxlength="<?= MAX_TITLE_LENGTH ?>">
      <textarea name="body" placeholder="Describe your project... (max <?= MAX_BODY_LENGTH ?> characters)" required maxlength="<?= MAX_BODY_LENGTH ?>"></textarea>
      <div style="margin:6px 0">
        <label class="file-label">Photo (optional, max 5MB): <input type="file" name="image" accept="image/*"></label>
      </div>
      <div class="reply-form-footer">
        <span class="char-count">0 / <?= MAX_BODY_LENGTH ?></span>
        <span class="meta"><?= POST_DELAY ?>s delay</span>
      </div>
      <button type="submit">Post Project</button>
    </form>
  </div>
  <script src="char-count.js"></script>
</body>
</html>
