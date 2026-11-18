<?php

/**
 * @OA\Info(
 *     title="Caffee API",
 *     version="1.0",
 *     description="API documentation for Caffee management system"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

require_once "./vendor/autoload.php";
require_once "./data/roles.php";
require_once "./rest/services/UsersService.php";
require_once "./rest/services/ProductsService.php";
require_once "./rest/services/ShiftProductsService.php";
require_once "./rest/services/ReportsService.php";
require_once "./rest/services/ShiftsService.php";
require_once "./rest/services/AuthService.php";
require_once "./middleware/AuthMiddleware.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


$debug = Config::DEBUG();
ini_set('display_errors', $debug ? 1 : 0);
ini_set('display_startup_errors', $debug ? 1 : 0);
error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED));

Flight::map('json', function ($data, $code = 200) {
    // 1. Force wipe the output buffer of any hidden spaces or warnings
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // 2. Set the exact header so the browser knows it is JSON
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    
    // 3. Print the JSON
    echo json_encode($data);
    
    // 4. Kill the script instantly. InfinityFree cannot append anything after this.
    exit;
});



// Log unexpected errors server-side; only show details in debug mode.
Flight::map('error', function (Throwable $e) use ($debug) {
    error_log((string) $e);
    Flight::json(['error' => $debug ? $e->getMessage() : 'Internal server error'], 500);
});

Flight::register('users', "UsersService");
Flight::register('product', "ProductsService");
Flight::register('shifts', "ShiftsService");
Flight::register('reports', "ReportsService");
Flight::register('sp', "ShiftProductsService");
Flight::register("auth_service", "AuthService");
Flight::register('auth_middleware', "AuthMiddleware");

Flight::before('start', function () {

    $url = Flight::request()->url;

    // ✅ PUBLIC ROUTES (NO AUTH)
    if (
        strpos($url, '/auth/login') !== false ||
        strpos($url, '/auth/register') !== false
    ) {
        return; // ⬅️ THIS IS CRITICAL
    }
    
    if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$headerName] = $value;
            	}
        	}
        return $headers;
    	}
	}

    $headers = getallheaders();
    $token = null;

    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    }

    if (!$token) {
        Flight::halt(401, "Missing authentication token");
    }

    try {
        $decoded_token = JWT::decode(
            $token,
            new Key(Config::JWT_SECRET(), 'HS256')
        );

        Flight::set('user', $decoded_token->user);
        Flight::set('jwt_token', $token);

    } catch (Exception $e) {
        Flight::halt(401, "Invalid or expired token");
    }
});

require_once "./rest/routes/AuthRoute.php";
require_once "./rest/routes/UsersRoute.php";
require_once "./rest/routes/ProductsRoute.php";
require_once "./rest/routes/ShiftsRoute.php";
require_once "./rest/routes/ReportsRoute.php";
require_once "./rest/routes/ShiftsProductsRoute.php";
Flight::set('flight.base_url', '/backend');


Flight::start();