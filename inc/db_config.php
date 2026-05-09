<?php

if (!function_exists('load_database_config')) {
  function load_database_config(): array
  {
    $config = [
      'DB_HOST' => '',
      'DB_NAME' => '',
      'DB_USER' => '',
      'DB_PASS' => '',
      'DB_PORT' => '3306',
    ];

    $localConfigPath = __DIR__ . '/../config.local.php';
    if (is_file($localConfigPath) && is_readable($localConfigPath)) {
      $localConfig = require $localConfigPath;
      if (is_array($localConfig)) {
        foreach ($config as $key => $defaultValue) {
          if (array_key_exists($key, $localConfig) && is_scalar($localConfig[$key])) {
            $config[$key] = (string)$localConfig[$key];
          }
        }
      }
    }

    foreach (array_keys($config) as $key) {
      $value = getenv($key);
      if (is_string($value) && $value !== '') {
        $config[$key] = $value;
      }
    }

    return $config;
  }
}
