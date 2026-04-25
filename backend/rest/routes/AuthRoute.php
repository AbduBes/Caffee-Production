<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

Flight::group('/auth', function() {

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="demo@gmail.com"),
     *             @OA\Property(property="password", type="string", example="some_password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    Flight::route("POST /register", function () {
        $data = Flight::request()->data->getData();
        $response = Flight::auth_service()->register($data);

        if ($response['success']) {
            Flight::json([
                'message' => 'User registered successfully',
                'data' => $response['data']
            ]);
        } else {
            Flight::halt(400, $response['error']);
        }
    });


    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Login and get JWT token",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="enis.doljanac@gmail.com"),
     *             @OA\Property(property="password", type="string", example="test1")
     *         )
     *     ),   
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
    Flight::route("POST /login", function () {
        $data = Flight::request()->data->getData();
        $response = Flight::auth_service()->login($data);
        
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

		header('Content-Type: application/json');

        if ($response['success']) {
            echo json_encode([
                'message' => 'User logged in successfully',
                'data' => $response['data']
            ]);
            exit;
            //Flight::json([
              //  'message' => 'User logged in successfully',
                //'data' => $response['data']
            //]);
        } else {
            Flight::halt(401, $response['error']);
        }
    });

});