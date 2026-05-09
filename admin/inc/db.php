<?php
require __DIR__ . '/config.php';

date_default_timezone_set(APP_TIMEZONE);

if (
  !defined('DB_HOST') || DB_HOST === '' ||
  !defined('DB_NAME') || DB_NAME === '' ||
  !defined('DB_USER') || DB_USER === '' ||
  !defined('DB_PASS') || DB_PASS === ''
) {
  http_response_code(500);
  error_log('Database configuration is incomplete.');
  exit('Service temporarily unavailable.');
}

$dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";

$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
  http_response_code(500);
  exit('Database connection failed.');
}
