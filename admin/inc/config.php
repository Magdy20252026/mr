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

define('APP_EMBED_SECRET_KEY', '9F2kL7sQ3mN8vX1aC6rT0pY5wD4hG2jU');
