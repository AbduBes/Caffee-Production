<?php

// Copy this file to config.local.php and fill in your own values.
// config.local.php is git-ignored - never commit real credentials.
return [
    'DB_HOST'     => 'localhost',
    'DB_PORT'     => '3306',
    'DB_NAME'     => 'cafe',
    'DB_USER'     => 'root',
    'DB_PASSWORD' => '',
    // Long random string used to sign login tokens, e.g. the output of:
    // php -r "echo bin2hex(random_bytes(32));"
    'JWT_SECRET'  => '',
    'APP_DEBUG'   => false,
];
