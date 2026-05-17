<?php require __DIR__ . '/db.php';

$topicCount = $db->querySingle("SELECT COUNT(*) FROM topics");
$replyCount = $db->querySingle("SELECT COUNT(*) FROM replies");
$userCount = $db->querySingle("SELECT COUNT(*) FROM users");
$scienceCount = $db->querySingle("SELECT COUNT(*) FROM science_posts");
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

    <div class="cat-header"><span class="cat-icon">&#9632;</span> Awesome Science</div>

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
      <div class="board-last">
        <?php
        $latest = $db->querySingle("SELECT id, title, created_at FROM topics ORDER BY created_at DESC LIMIT 1", true);
        if ($latest):
        ?>
        <a href="topic.php?id=<?= $latest['id'] ?>"><?= htmlspecialchars($latest['title']) ?></a>
        <div class="date"><?= formatDate($latest['created_at']) ?></div>
        <?php else: ?>
        No posts yet
        <?php endif; ?>
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
      <div class="board-last">
        <?php
        $latestSci = $db->querySingle("SELECT id, title, created_at FROM science_posts ORDER BY created_at DESC LIMIT 1", true);
        if ($latestSci):
        ?>
        <a href="science_post.php?id=<?= $latestSci['id'] ?>"><?= htmlspecialchars($latestSci['title']) ?></a>
        <div class="date"><?= formatDate($latestSci['created_at']) ?></div>
        <?php else: ?>
        No posts yet
        <?php endif; ?>
      </div>
    </div>

    <div class="board-row">
      <div class="board-icon">A</div>
      <div class="board-info">
        <div class="board-name"><a href="announcements.php">Announcements</a></div>
        <div class="board-desc">Official announcements from Cosmo</div>
      </div>
      <div class="board-stats">
        <?php $annCount = $db->querySingle("SELECT COUNT(*) FROM announcements"); ?>
        <span class="num"><?= $annCount ?></span> posts
      </div>
      <div class="board-last">
        <?php
        $latestAnn = $db->querySingle("SELECT id, title, created_at FROM announcements ORDER BY created_at DESC LIMIT 1", true);
        if ($latestAnn):
        ?>
        <a href="announcement.php?id=<?= $latestAnn['id'] ?>"><?= htmlspecialchars($latestAnn['title']) ?></a>
        <div class="date"><?= formatDate($latestAnn['created_at']) ?></div>
        <?php else: ?>
        No posts yet
        <?php endif; ?>
      </div>
    </div>

    <div class="cat-header" style="margin-top:14px"><span class="cat-icon">&#9632;</span> Recent Topics</div>

    <div class="page-actions">
      <div>
        <h1>Forum</h1>
      </div>
      <?php if (isLoggedIn()): ?>
        <a href="post.php" class="btn">+ New Topic</a>
      <?php else: ?>
        <a href="login.php" class="btn">+ New Topic</a>
      <?php endif; ?>
    </div>

    <table class="forum-table">
      <thead>
        <tr>
          <th class="col-topic">Topic</th>
          <th class="col-author">Author</th>
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
          <td class="col-topic">
            <a href="topic.php?id=<?= $row['id'] ?>" class="topic-title"><?= htmlspecialchars($row['title']) ?></a>
          </td>
          <td class="col-author"><?= authorLink($row['author'], $row['user_id']) ?></td>
          <td class="col-replies"><?= $row['reply_count'] ?></td>
          <td class="col-last"><?= formatDate($row['created_at']) ?></td>
        </tr>
        <?php endwhile; ?>
        <?php if (!$hasAny): ?>
        <tr><td colspan="4" class="empty-row">No topics yet. Be the first to post!</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="info-center">
      <h3>Awesome Science - Info Center</h3>
      <div class="stat-row"><strong>Forum Stats:</strong> <?= $topicCount ?> topics in <?= $replyCount ?> replies by <?= $userCount ?> members</div>
      <div class="stat-row"><strong>Latest Member:</strong> <?php
        $latestUser = $db->querySingle("SELECT username FROM users ORDER BY id DESC LIMIT 1", true);
        echo $latestUser ? htmlspecialchars($latestUser['username']) : 'N/A';
      ?></div>
    </div>

  </div>
</body>
</html>
