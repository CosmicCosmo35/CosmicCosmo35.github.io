<?php require __DIR__ . '/db.php';

$q = trim($_GET['q'] ?? '');
$results = [];
if ($q) {
  $like = '%' . $q . '%';
  $stmt = $db->prepare("SELECT DISTINCT t.id, t.title, t.author, t.user_id, t.tags, t.created_at, (SELECT COUNT(*) FROM replies WHERE topic_id = t.id) AS reply_count FROM topics t LEFT JOIN replies r ON r.topic_id = t.id WHERE t.title LIKE ? OR r.body LIKE ? ORDER BY t.created_at DESC");
  $stmt->bindValue(1, $like, SQLITE3_TEXT);
  $stmt->bindValue(2, $like, SQLITE3_TEXT);
  $res = $stmt->execute();
  while ($row = $res->fetchArray(SQLITE3_ASSOC)) $results[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search - Forum</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="/forum">Forum</a>
    <a href="/science_talk">Science Talk</a>
    <a href="/announcements">Announcements</a>
    <a href="/search" class="active auth-link">Search</a>
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
    <h1>Search</h1>

    <form method="get" class="reply-form" style="flex-direction:row;gap:8px;max-width:500px">
      <input type="text" name="q" placeholder="Search topics and replies..." value="<?= htmlspecialchars($q) ?>" style="flex:1">
      <button type="submit" style="width:auto">Search</button>
    </form>

    <?php if ($q): ?>
      <p class="meta" style="margin-top:16px"><?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?> for "<?= htmlspecialchars($q) ?>"</p>

      <table class="topic-table">
        <tr><th>Topic</th><th>Tags</th><th>Author</th><th>Replies</th><th>Last updated</th></tr>
        <?php foreach ($results as $row): ?>
        <tr>
          <td><a href="/topic/<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></td>
          <td><?= renderTags($row['tags']) ?></td>
          <td><?= authorLink($row['author'], $row['user_id']) ?></td>
          <td><?= $row['reply_count'] ?></td>
          <td><?= formatDate($row['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$results): ?>
        <tr><td colspan="5" style="text-align:center;color:#888;padding:24px">No results found.</td></tr>
        <?php endif; ?>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
