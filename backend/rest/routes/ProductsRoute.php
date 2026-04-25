<?php

/**
 * @OA\Get(
 *      path="/products",
 *      tags={"Products"},
 *      summary="Get all products",
 *      security={{"bearerAuth": {}}},
 *      @OA\Response(
 *           response=200,
 *           description="Array of all products"
 *      )
 * )
 */
Flight::route("GET /products", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::product()->getAll());
});

/**
 * @OA\Get(
 *     path="/products/{id}",
 *     tags={"Products"},
 *     summary="Get product by ID",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Product ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product found",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found"
 *     )
 * )
 */
Flight::route("GET /products/@id", function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $product = Flight::product()->getById($id);
    if ($product) {
        Flight::json($product);
    } else {
        Flight::halt(404, "Product not found");
    }
});

/**
 * @OA\Post(
 *     path="/products",
 *     tags={"Products"},
 *     summary="Create product",
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required = {"name", "price"},
 *             @OA\Property(property="name", type="string", example="Espresso"),
 *             @OA\Property(property="price", type="number", format="float", example=2.50)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Product created"
 *     )
 * )
 */
Flight::route("POST /products", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data->getData();
    $product = Flight::product()->add($data);
    Flight::json($product, 201);
});

/**
 * @OA\Put(
 *     path="/products/{id}/price",
 *     tags={"Products"},
 *     summary="Update product price",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Product ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"price"},
 *             @OA\Property(property="price", type="number", format="float", example=220.00)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product price updated"
 *     )
 * )
 */

Flight::route("PUT /products/@id/price", function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);

    $data = Flight::request()->data;

    if (!isset($data->price)) {
        Flight::halt(400, "Price is required");
    }

    Flight::product()->updatePrice($id, $data->price);

    Flight::json([
        "message" => "Product price updated successfully"
    ]);
});

/**
 * @OA\Delete(
 *     path="/products/{id}",
 *     tags={"Products"},
 *     summary="Delete product",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product deleted"
 *     )
 * )
 */
Flight::route("DELETE /products/@id", function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::product()->delete($id);
    Flight::json(["message" => "Product deleted"]);
});

/**
 * @OA\Get(
 *     path="/products/sales",
 *     tags={"Products"},
 *     summary="Get all products with total sales and revenue",
 *     description="Returns each product with calculated total items sold and total revenue",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of products with sales statistics"
 *     )
 * )
 */
Flight::route("GET /products/sales", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $results = Flight::product()->getProductsWithSales();
    Flight::json($results);
});