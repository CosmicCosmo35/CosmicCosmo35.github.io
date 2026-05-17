<?php require __DIR__ . '/db.php';

$id = (int)($_GET['id'] ?? 0);
$topic = $db->querySingle("SELECT * FROM topics WHERE id = $id", true);
if (!$topic) { header('Location: /forum'); exit; }
if (!isLoggedIn() || !(currentUserId() == $topic['user_id'] || isAdmin())) { header('Location: /login'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $tags = trim($_POST['tags'] ?? '');
  if (!$title) {
    $error = 'Title is required.';
  } elseif (strlen($title) > MAX_TITLE_LENGTH) {
    $error = 'Title too long (max ' . MAX_TITLE_LENGTH . ').';
  } elseif (strlen($tags) > MAX_TAGS_LENGTH) {
    $error = 'Tags too long (max ' . MAX_TAGS_LENGTH . ' characters).';
  } else {
    $stmt = $db->prepare("UPDATE topics SET title = ?, tags = ? WHERE id = ?");
    $stmt->bindValue(1, $title, SQLITE3_TEXT);
    $stmt->bindValue(2, $tags, SQLITE3_TEXT);
    $stmt->bindValue(3, $id, SQLITE3_INTEGER);
    $stmt->execute();
    header("Location: /topic/$id");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Topic - Forum</title>
  <link rel="stylesheet" href="/forum/style.css">
</head>
<body>
  <div class="topbar">
    <img src="/Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="/forum">Forum</a>
    <a href="/science_talk">Science Talk</a>
    <a href="/announcements">Announcements</a>
    <a href="/search" class="auth-link">Search</a>
    <span class="spacer"></span>
    <?php if (isLoggedIn()): ?>
      <a href="/profile" class="user-badge"><?= htmlspecialchars(currentUser()) ?></a>
      <a href="/logout" class="auth-link">Logout</a>
    <?php endif; ?>
  </div>

  <div class="content">
    <a href="/topic/<?= $id ?>">&larr; Back to topic</a>
    <h1>Edit Topic</h1>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" class="reply-form">
      <input type="text" name="title" value="<?= htmlspecialchars($topic['title']) ?>" required maxlength="<?= MAX_TITLE_LENGTH ?>">
      <input type="text" name="tags" value="<?= htmlspecialchars($topic['tags']) ?>" placeholder="Tags (comma-separated)" maxlength="<?= MAX_TAGS_LENGTH ?>">
      <button type="submit">Save Changes</button>
    </form>
  </div>
</body>
</html>
