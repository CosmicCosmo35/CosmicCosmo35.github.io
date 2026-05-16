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

function isLoggedIn() {
  return isset($_SESSION['user_id']);
}

function currentUser() {
  return $_SESSION['username'] ?? 'Anonymous';
}

function currentUserId() {
  return $_SESSION['user_id'] ?? null;
}

define('MAX_BODY_LENGTH', 500);
define('MAX_REPLY_LENGTH', 300);
define('MAX_TITLE_LENGTH', 60);
define('MAX_USERNAME_LENGTH', 15);
