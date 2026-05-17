<?php require __DIR__ . '/db.php';

$topicCount = $db->querySingle("SELECT COUNT(*) FROM topics");
$replyCount = $db->querySingle("SELECT COUNT(*) FROM replies");
$userCount = $db->querySingle("SELECT COUNT(*) FROM users");
$scienceCount = $db->querySingle("SELECT COUNT(*) FROM science_posts");
$annCount = $db->querySingle("SELECT COUNT(*) FROM announcements");

$latestUser = $db->querySingle("SELECT username FROM users ORDER BY id DESC LIMIT 1", true);
$latestUser = $latestUser ? htmlspecialchars($latestUser['username']) : 'N/A';
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
    <a href="index.php" class="active">Forum</a>
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
    <div class="cat-row">Awesome Science</div>

    <div class="board-row">
      <div class="board-icon">F</div>
      <div class="board-info">
        <div class="board-name"><a href="index.php">Forum</a></div>
        <div class="board-desc">General discussion and topics</div>
      </div>
      <div class="board-stats">
        <span class="num"><?= $topicCount ?></span> topics<br>
        <span class="num"><?= $replyCount ?></span> replies
      </div>
    </div>

    <div class="board-row">
      <div class="board-icon">S</div>
      <div class="board-info">
        <div class="board-name"><a href="science_talk.php">Science Talk</a></div>
        <div class="board-desc">Share your projects and photos</div>
      </div>
      <div class="board-stats">
        <span class="num"><?= $scienceCount ?></span> posts
      </div>
    </div>

    <div class="board-row">
      <div class="board-icon">A</div>
      <div class="board-info">
        <div class="board-name"><a href="announcements.php">Announcements</a></div>
        <div class="board-desc">Official announcements from Cosmo</div>
      </div>
      <div class="board-stats">
        <span class="num"><?= $annCount ?></span> posts
      </div>
    </div>

    <div class="cat-row" style="margin-top:8px">Recent Topics</div>

    <div class="page-head">
      <h1>Forum Topics</h1>
      <div class="page-head-right">
        <span class="page-nav"><?= $topicCount ?> topics</span>
        <?php if (isLoggedIn()): ?>
          <a href="post.php" class="btn">+ New Topic</a>
        <?php else: ?>
          <a href="login.php" class="btn">+ New Topic</a>
        <?php endif; ?>
      </div>
    </div>

    <table class="forum-table">
      <thead>
        <tr>
          <th class="col-topic">Topic</th>
          <th class="col-started">Started By</th>
          <th class="col-replies">Replies</th>
          <th class="col-last">Last Post</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $result = $db->query("SELECT t.*, (SELECT COUNT(*) FROM replies WHERE topic_id = t.id) AS reply_count FROM topics t ORDER BY t.created_at DESC");
        $hasAny = false;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)):
          $hasAny = true;
        ?>
        <tr>
          <td class="col-topic"><a href="topic.php?id=<?= $row['id'] ?>" class="topic-title"><?= htmlspecialchars($row['title']) ?></a></td>
          <td class="col-started"><?= authorLink($row['author'], $row['user_id']) ?><br><?= formatDate($row['created_at']) ?></td>
          <td class="col-replies"><?= $row['reply_count'] ?></td>
          <td class="col-last"><?= formatDate($row['created_at']) ?></td>
        </tr>
        <?php endwhile; ?>
        <?php if (!$hasAny): ?>
        <tr><td colspan="4" class="empty-row" style="text-align:center;color:#999;padding:30px">No topics yet. Be the first to post!</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="info-center">
      <h3>Forum Statistics</h3>
      <div class="stat-row">Total topics: <?= $topicCount ?> &bull; Total replies: <?= $replyCount ?> &bull; Total members: <?= $userCount ?></div>
      <div class="stat-row">Latest member: <?= $latestUser ?></div>
    </div>
  </div>
</body>
</html>
