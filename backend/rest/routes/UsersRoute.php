<?php

/**
* @OA\Get(
*      path="/users",
*      tags={"Users"},
*      summary="Get all users",
*      security={{"bearerAuth": {}}},
*      @OA\Response(
*           response=200,
*           description="Array of all users in the database"
*      )
* )
*/

Flight::route("GET /users", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::users()->get_all_employees());
});

/**
 * @OA\Get(
 *     path="/users/{id}",
 *     tags={"Users"},
 *     summary="Get user by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the user to retrieve",
 *         @OA\Schema(type="integer", example = 1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User found",
 *     )
 * )
 */

Flight::route("GET /users?role=@role", function ($role) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::users()->getByRole($role));
});
Flight::route("GET /users/@id:[0-9]+", function ($id) {
    Flight::auth_middleware()->authorizeSelfOrAdmin($id);
    $user = Flight::users()->getById($id);
    if ($user) {
        Flight::json($user);
    } else {
        Flight::halt(404, "User not found");
    }
});





Flight::route('GET /users/@user_id/shifts', function ($user_id) {
  Flight::auth_middleware()->authorizeSelfOrAdmin($user_id);
  $user_id = (int) $user_id;
  Flight::json(
    Flight::users()->get_employee_shifts($user_id)
  );
});

/**
 * @OA\Post(
 *     path="/users",
 *     tags={"Users"},
 *     summary="Create user",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required = {"name", "email", "password"},
 *             @OA\Property(property="name", type="string", example = "Abdurrahman"),
 *             @OA\Property(property="email", type="string", example = "abdurrahman@gmail.com"),
 *             @OA\Property(property="password", type="string", example = "abdu")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Customer created"
 *     )
 * )
 */
Flight::route("POST /users", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data;
    $user = Flight::users()->add($data);
    Flight::json($user, 201);
});

/**
 * @OA\Put(
 *     path="/users/{id}",
 *     tags={"Users"},
 *     summary="Update user",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Updating user by id",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
*         required=true,
*         @OA\JsonContent(
*             required={"name", "location"},
*             @OA\Property(property="name", type="string", example="Updated Name"),
*             @OA\Property(property="email", type="string", example="Updated Email"),
*         )
*     ),
 *     @OA\Response(
 *         response=200,
 *         description="Customer updated"
 *     )
 * )
 */


Flight::route("PUT /users/@id", function ($id) {
    // Users may edit their own profile; only admins may edit others or change roles.
    Flight::auth_middleware()->authorizeSelfOrAdmin($id);
    $isAdmin = Flight::get('user')->role === Roles::ADMIN;
    $data = Flight::request()->data;
    $user = Flight::users()->update($data, $id, "id", $isAdmin);
    Flight::json($user);
});

/**
 * @OA\Delete(
 *     path="/users/{id}",
 *     tags={"Users"},
 *     summary="Delete user",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User deleted"
 *     )
 * )
 */
Flight::route("DELETE /users/@id", function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::users()->delete($id);
    Flight::json(["message" => "User deleted"]);
});

/**
 * @OA\Get(
 *     path="/users/{name}",
 *     tags={"Users"},
 *     summary="Search users by name",
 *     @OA\Parameter(
 *         name="name",
 *         in="path",
 *         required=true,
 *         description="User by name",
 *         @OA\Schema(type="string", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Filtered results",
 *     )
 * )
 */
Flight::route("GET /users/search/@name", function ($name) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $results = Flight::users()->searchByName($name);
    Flight::json($results);
});