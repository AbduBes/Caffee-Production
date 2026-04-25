<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../../vendor/autoload.php';

// IMPORTANT: your BASE_URL must match the real folder name "Cafe"
if($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1'){
    define('BASE_URL', 'http://localhost/Cafe/backend');
} else {
    define('BASE_URL', 'https://add-production-server-after-deployment/backend');
}

/*
 * CRITICAL PART:
 * You must scan the folder where your annotations actually are.
 * Example: backend/rest/
 */
$openapi = \OpenApi\Generator::scan([
    __DIR__ . '/../../../rest',
    __DIR__ . '/../../../public/v1/docs' // folder containing doc_setup.php
]);

header('Content-Type: application/json');
echo $openapi->toJson();

