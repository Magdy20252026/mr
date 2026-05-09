<?php
require_once dirname(__DIR__, 2) . '/inc/db_config.php';

$dbConfig = load_database_config();

// Database config
define('DB_HOST', $dbConfig['DB_HOST']);
define('DB_NAME', $dbConfig['DB_NAME']);
define('DB_USER', $dbConfig['DB_USER']);
define('DB_PASS', $dbConfig['DB_PASS']);
define('DB_PORT', $dbConfig['DB_PORT']);

// App
define('APP_TIMEZONE', 'Africa/Cairo');
define('APP_EMBED_SECRET_KEY_MIN_LENGTH', 32);
$embedSecret = getenv('APP_EMBED_SECRET_KEY');
if (!is_string($embedSecret) || strlen($embedSecret) < APP_EMBED_SECRET_KEY_MIN_LENGTH) {
  $embedSecret = '';
  $adminConfigPath = dirname(__DIR__, 2) . '/admin/inc/config.php';
  if (is_file($adminConfigPath) && is_readable($adminConfigPath)) {
    $adminConfigContents = file_get_contents($adminConfigPath);
    if (
      is_string($adminConfigContents) &&
      preg_match('/define\(\s*[\'"]APP_EMBED_SECRET_KEY[\'"]\s*,\s*[\'"]([A-Za-z0-9_-]{32,})[\'"]\s*\)\s*;/', $adminConfigContents, $matches)
    ) {
      $embedSecret = (string)($matches[1] ?? '');
    }
  }
}
if (strlen($embedSecret) < APP_EMBED_SECRET_KEY_MIN_LENGTH) {
  $embedSecret = '';
}
define('APP_EMBED_SECRET_KEY', $embedSecret);
