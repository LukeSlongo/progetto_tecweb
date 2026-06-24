<?php
// config.php

function unifix_load_ini_config()
{
    $candidates = [];

    $env_path = getenv('UNIFIX_INI');
    if ($env_path) {
        $candidates[] = $env_path;
    }

    $candidates[] = __DIR__ . '/unifix.ini';
    $candidates[] = dirname(__DIR__) . '/unifix.ini';

    $home = getenv('HOME') ?: ($_SERVER['HOME'] ?? null);
    if ($home) {
        $candidates[] = rtrim($home, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'unifix.ini';
    }

    foreach (array_unique($candidates) as $path) {
        if (is_readable($path)) {
            return parse_ini_file($path) ?: [];
        }
    }

    return [];
}

function unifix_config_value($key, $default)
{
    static $ini_config = null;

    if ($ini_config === null) {
        $ini_config = unifix_load_ini_config();
    }

    $env_value = getenv($key);
    if ($env_value !== false && $env_value !== '') {
        return $env_value;
    }

    return $ini_config[$key] ?? $default;
}

function unifix_normalize_base_path($base_path)
{
    $base_path = trim((string) $base_path);
    if ($base_path === '' || $base_path === '/') {
        return '';
    }

    return '/' . trim($base_path, '/');
}

// In locale Docker passa le variabili d'ambiente. Sul server TecWeb si usa
// ~/unifix.ini, fuori dalla webroot, con le stesse chiavi DB_* e BASE_PATH.
define('DB_HOST', unifix_config_value('DB_HOST', 'db'));
define('DB_USER', unifix_config_value('DB_USER', 'user'));
define('DB_PASSWORD', unifix_config_value('DB_PASSWORD', 'password'));
define('DB_NAME', unifix_config_value('DB_NAME', 'unifix'));
define('DB_CHAR', 'utf8mb4');
define('BASE_PATH', unifix_normalize_base_path(unifix_config_value('BASE_PATH', '')));
