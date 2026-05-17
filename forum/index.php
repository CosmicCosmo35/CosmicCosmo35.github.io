<?php require __DIR__ . '/db.php';

$tagFilter = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * TOPICS_PER_PAGE;

if ($tagFilter) {
  $countStmt = $db->prepare("SELECT COUNT(*) FROM topics WHERE tags LIKE ?");
  $countStmt->bindValue(1, '%' . $tagFilter . '%', SQLITE3_TEXT);
  $totalTopics = $countStmt->execute()->fetchArray()[0];
  $totalPages = max(1, (int)ceil($totalTopics / TOPICS_PER_PAGE));
  $stmt = $db->prepare("SELECT t.*, (SELECT COUNT(*) FROM replies WHERE topic_id = t.id) AS reply_count FROM topics t WHERE t.tags LIKE ? ORDER BY t.created_at DESC LIMIT ? OFFSET ?");
  $stmt->bindValue(1, '%' . $tagFilter . '%', SQLITE3_TEXT);
  $stmt->bindValue(2, TOPICS_PER_PAGE, SQLITE3_INTEGER);
  $stmt->bindValue(3, $offset, SQLITE3_INTEGER);
  $result = $stmt->execute();
} else {
  $totalTopics = $db->querySingle("SELECT COUNT(*) FROM topics");
  $totalPages = max(1, (int)ceil($totalTopics / TOPICS_PER_PAGE));
  $result = $db->query("SELECT t.*, (SELECT COUNT(*) FROM replies WHERE topic_id = t.id) AS reply_count FROM topics t ORDER BY t.created_at DESC LIMIT " . TOPICS_PER_PAGE . " OFFSET $offset");
}
$urlBase = '/forum' . ($tagFilter ? '?tag=' . urlencode($tagFilter) . '&' : '?');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forum - Awesome Science</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="topbar">
    <img src="../Logo.png" alt="Logo">
    <a href="../index.html">Home</a>
    <a href="/forum" class="active">Forum</a>
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
    <h1>Forum<?= $tagFilter ? ' - Tag: ' . htmlspecialchars($tagFilter) : '' ?></h1>
    <div style="display:flex;justify-content:space-between;align-items:center">
      <a href="/post" class="btn" style="margin-bottom:0">+ New Topic</a>
      <span class="meta" style="margin-bottom:0"><?= $totalTopics ?> topic<?= $totalTopics !== 1 ? 's' : '' ?></span>
    </div>

    <table class="topic-table" style="margin-top:16px">
      <tr><th>Topic</th><th>Tags</th><th>Author</th><th>Replies</th><th>Last updated</th></tr>
      <?php
      $hasAny = false;
      while ($row = $result->fetchArray(SQLITE3_ASSOC)):
        $hasAny = true;
      ?>
      <tr>
        <td><a href="/topic/<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></td>
        <td><?= renderTags($row['tags']) ?></td>
        <td><?= authorLink($row['author'], $row['user_id']) ?></td>
        <td><?= $row['reply_count'] ?></td>
        <td><?= formatDate($row['created_at']) ?></td>
      </tr>
      <?php endwhile; ?>
      <?php if (!$hasAny): ?>
      <tr><td colspan="5" style="text-align:center;color:#888;padding:24px">No topics yet.</td></tr>
      <?php endif; ?>
    </table>

    <?= paginationLinks($page, $totalPages, $urlBase) ?>
  </div>
</body>
</html>
