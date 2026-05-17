<?php
session_start();

$db = new SQLite3(__DIR__ . '/forum.db');

$db->exec("CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  avatar TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS topics (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  author TEXT NOT NULL DEFAULT 'Anonymous',
  user_id INTEGER,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS replies (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  topic_id INTEGER NOT NULL,
  author TEXT NOT NULL DEFAULT 'Anonymous',
  user_id INTEGER,
  body TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (topic_id) REFERENCES topics(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS announcements (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  author TEXT NOT NULL,
  user_id INTEGER NOT NULL,
  body TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS announcement_replies (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  announcement_id INTEGER NOT NULL,
  author TEXT NOT NULL DEFAULT 'Anonymous',
  user_id INTEGER,
  body TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (announcement_id) REFERENCES announcements(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS science_posts (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  author TEXT NOT NULL DEFAULT 'Anonymous',
  user_id INTEGER,
  body TEXT NOT NULL,
  image_path TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
)");

define('MAX_BODY_LENGTH', 500);
define('MAX_REPLY_LENGTH', 300);
define('MAX_TITLE_LENGTH', 60);
define('MAX_USERNAME_LENGTH', 15);
define('MAX_ANNOUNCEMENT_REPLIES', 2);
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('AVATAR_DIR', UPLOAD_DIR . '/avatars');
define('PROJECT_DIR', UPLOAD_DIR . '/projects');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('POST_DELAY', 10);

function isLoggedIn() {
  return isset($_SESSION['user_id']);
}

function currentUser() {
  return $_SESSION['username'] ?? 'Anonymous';
}

function currentUserId() {
  return $_SESSION['user_id'] ?? null;
}

function isAdmin() {
  return isLoggedIn() && strtolower($_SESSION['username']) === 'cosmo';
}

function formatDate($datetime) {
  return date('d/m/Y', strtotime($datetime));
}

function renderMarkdown($text) {
  $text = htmlspecialchars($text);

  $text = preg_replace('/```(.+?)```/s', '<pre><code>$1</code></pre>', $text);
  $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
  $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
  $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
  $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text);

  $lines = explode("\n", $text);
  $inUl = false;
  $inOl = false;
  $result = [];
  foreach ($lines as $line) {
    if (preg_match('/^#{1,3}\s+(.+)$/', $line, $m)) {
      if ($inUl) { $result[] = '</ul>'; $inUl = false; }
      if ($inOl) { $result[] = '</ol>'; $inOl = false; }
      $level = strlen(trim(explode(' ', $line)[0]));
      $result[] = "<h$level>" . trim($m[1]) . "</h$level>";
    } elseif (preg_match('/^-\s+(.+)$/', $line, $m)) {
      if ($inOl) { $result[] = '</ol>'; $inOl = false; }
      if (!$inUl) { $result[] = '<ul>'; $inUl = true; }
      $result[] = '<li>' . $m[1] . '</li>';
    } elseif (preg_match('/^\d+\.\s+(.+)$/', $line, $m)) {
      if ($inUl) { $result[] = '</ul>'; $inUl = false; }
      if (!$inOl) { $result[] = '<ol>'; $inOl = true; }
      $result[] = '<li>' . $m[1] . '</li>';
    } else {
      if ($inUl) { $result[] = '</ul>'; $inUl = false; }
      if ($inOl) { $result[] = '</ol>'; $inOl = false; }
      if (trim($line) === '') {
        $result[] = '';
      } else {
        $result[] = '<p>' . $line . '</p>';
      }
    }
  }
  if ($inUl) $result[] = '</ul>';
  if ($inOl) $result[] = '</ol>';
  return implode("\n", $result);
}

function authorLink($author, $userId) {
  if ($userId) {
    return '<a href="profile.php?id=' . $userId . '">' . htmlspecialchars($author) . '</a>';
  }
  return htmlspecialchars($author);
}

function getAvatar($userId) {
  $path = AVATAR_DIR . '/' . (int)$userId . '.jpg';
  if (file_exists($path)) {
    return 'uploads/avatars/' . (int)$userId . '.jpg?t=' . filemtime($path);
  }
  return false;
}

function getUserStats($userId) {
  global $db;
  $user = $db->querySingle("SELECT id, username, created_at FROM users WHERE id = " . (int)$userId, true);
  if (!$user) return null;
  $topics = $db->querySingle("SELECT COUNT(*) FROM topics WHERE user_id = " . (int)$userId);
  $replies = $db->querySingle("SELECT COUNT(*) FROM replies WHERE user_id = " . (int)$userId);
  $user['topics'] = $topics;
  $user['replies'] = $replies;
  return $user;
}

function ensureDirs() {
  foreach ([UPLOAD_DIR, AVATAR_DIR, PROJECT_DIR] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
  }
}

function uploadImage($file, $destDir) {
  $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
  if ($file['error'] !== UPLOAD_ERR_OK) return null;
  if ($file['size'] > MAX_FILE_SIZE) return null;
  if (!in_array($file['type'], $allowed)) return null;
  $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
  $name = uniqid() . '.' . $ext;
  $dest = $destDir . '/' . $name;
  if (move_uploaded_file($file['tmp_name'], $dest)) {
    return $name;
  }
  return null;
}

ensureDirs();
