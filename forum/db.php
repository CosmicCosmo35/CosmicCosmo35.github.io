<?php
session_start();

$db = new SQLite3(__DIR__ . '/forum.db');

$db->exec("CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
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

define('MAX_BODY_LENGTH', 500);
define('MAX_REPLY_LENGTH', 300);
define('MAX_TITLE_LENGTH', 60);
define('MAX_USERNAME_LENGTH', 15);
define('MAX_ANNOUNCEMENT_REPLIES', 2);

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
  return isset($_SESSION['user_id']) && $_SESSION['user_id'] === 1;
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
