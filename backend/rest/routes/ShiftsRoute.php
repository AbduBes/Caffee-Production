<?php

/**
 * @OA\Schema(
 *     schema="Shift",
 *     type="object",
 *     required={"user_id", "date", "start_time", "end_time"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=3),
 *     @OA\Property(property="date", type="string", format="date", example="2025-02-01"),
 *     @OA\Property(property="start_time", type="string", format="time", example="08:00"),
 *     @OA\Property(property="end_time", type="string", format="time", example="16:00")
 * )
 */


/**
 * @OA\Get(
 *     path="/shifts",
 *     tags={"Shifts"},
 *     summary="Get all shifts",
 *     description="Returns a list of all shifts",
 *     @OA\Response(
 *         response=200,
 *         description="List of shifts",
 *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Shift"))
 *     )
 * )
 */

/**
 * @OA\Get(
 *     path="/shifts/{id}",
 *     tags={"Shifts"},
 *     summary="Get shift by ID",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Shift ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Shift found",
 *         @OA\JsonContent(ref="#/components/schemas/Shift")
 *     ),
 *     @OA\Response(response=404, description="Shift not found")
 * )
 */

/**
 * @OA\Post(
 *     path="/shifts",
 *     tags={"Shifts"},
 *     summary="Create new shift",
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Shift details",
 *         @OA\JsonContent(ref="#/components/schemas/Shift")
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Shift created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Shift")
 *     )
 * )
 */

/**
 * @OA\Put(
 *     path="/shifts/{id}",
 *     tags={"Shifts"},
 *     summary="Update shift",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Shift ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Shift details",
 *         @OA\JsonContent(ref="#/components/schemas/Shift")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Shift updated successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Shift")
 *     )
 * )
 */

/**
 * @OA\Delete(
 *     path="/shifts/{id}",
 *     tags={"Shifts"},
 *     summary="Delete shift",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Shift ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="Shift deleted"),
 *     @OA\Response(response=404, description="Shift not found")
 * )
 */


/**
 * @OA\Get(
 *     path="/users/{id}/shifts",
 *     tags={"Shifts"},
 *     summary="Get shifts for specific user",
 *     description="Useful endpoint for shift details of users",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of shifts for employee",
 *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Shift"))
 *     )
 * )
 */
/**
 * @OA\Get(
 *     path="/shifts/latest",
 *     tags={"Shifts"},
 *     summary="Get latest shift",
 *     description="Returns the latest shift added",
 *    security={{"bearerAuth": {}}},    
 *     @OA\Response(
 *         response=200,
 *         description="List of shifts",
 *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Shift"))
 *     )
 * )
 */

Flight::route("GET /shifts/latest", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $result = Flight::shifts()->getLatestShift();
    Flight::json($result);
});

Flight::route("GET /shifts/@id/summary", function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::shifts()->getShiftSummary($id));
});

// GENERIC ROUTES AFTER (broader routes last)
Flight::route("GET /shifts", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::shifts()->getLastFourShifts());
});

Flight::route("GET /shifts/@id", function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $shift = Flight::shifts()->getById($id);
    if ($shift) {
        Flight::json($shift);
    } else {
        Flight::halt(404, "Shift not found");
    }
});

Flight::route("POST /shifts", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data;
    $shift = [
        'user_id'    => $data->user_id,
        'date'       => $data->date,
        'smena'      => $data->smena,
        'venue_id'   => $data->venue_id
    ];
    $createdShift = Flight::shifts()->add($shift);
    Flight::json($createdShift, 201);
});

Flight::route("PUT /shifts/@id", function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data;
    $shift = Flight::shifts()->update($data, $id);
    Flight::json($shift);
});

Flight::route("DELETE /shifts/@id", function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::shifts()->delete($id);
    Flight::json(["message" => "Shift deleted"]);
});

Flight::route("GET /users/@id/shifts", function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::shifts()->getShiftByEmployee($id);
    Flight::json($data);
});