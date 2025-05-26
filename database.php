<?php

// No need to autoload for .env if we are hardcoding credentials.
// require_once __DIR__ . '/vendor/autoload.php';

// Hardcoded Database Configuration
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_PORT'] = '5432';
$_ENV['DB_DATABASE'] = 'dwemer';
$_ENV['DB_USERNAME'] = 'dwemer';
$_ENV['DB_PASSWORD'] = 'dwemer';

// The rest of your application can continue to use $_ENV variables as before.
// This keeps the api.php script unchanged in how it accesses these values. 