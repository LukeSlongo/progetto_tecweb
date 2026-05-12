<?php
// config.php

// Le variabili sono già disponibili perché Docker Compose le passa come environment
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_USER', getenv('DB_USER') ?: 'user');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'password');
define('DB_NAME', getenv('DB_NAME') ?: 'miosito');
define('DB_CHAR', 'utf8mb4');
