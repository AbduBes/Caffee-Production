<?php
/**
 * @OA\Get(
 *      path="/shift-products",
 *      tags={"Shift Products"},
 *      summary="Get all shift product entries",
 *      @OA\Response(
 *           response=200,
 *           description="List of shift products"
 *      )
 * )
 */
Flight::route("GET /shift-products", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::sp()->getAll());
});


/**
 * @OA\Get(
 *     path="/shift-products/shift/{shiftId}",
 *     tags={"Shift Products"},
 *     summary="Get all products sold in a shift",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="shiftId",
 *         in="path",
 *         required=true,
 *         description="Shift ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of products sold in the shift",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No products found for this shift"
 *     )
 * )
 */
Flight::route("GET /shift-products/@shiftId", function ($shiftId) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $shiftProducts = Flight::sp()->getShiftSales($shiftId);
    Flight::json($shiftProducts);
});

/**
 * @OA\Post(
 *     path="/shift-products",
 *     tags={"Shift Products"},
 *     summary="Create a shift product entry",
 *     security={{"bearerAuth": {}}}, 
 *     @OA\RequestBody( 
 *         required=true,
 *         @OA\JsonContent(
 *             required={"shift_id", "product_id", "products_sold", "total"},
 *             @OA\Property(property="shift_id", type="integer", example=1),
 *             @OA\Property(property="product_id", type="integer", example=3),
 *             @OA\Property(property="products_sold", type="integer", example=12),
 *             @OA\Property(property="total", type="number", format="float", example=24.00)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Shift product record created"
 *     )
 * )
 */
Flight::route("POST /shift-products", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data->getData();
    $shiftProduct = Flight::sp()->add($data);
    Flight::json($shiftProduct, 201);
});

/**
 * @OA\Put(
 *     path="/shift-products/{id}",
 *     tags={"Shift Products"},
 *     summary="Update shift product record",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"shiftId", "productId", "productsSold", "total"},
 *             @OA\Property(property="shiftId", type="integer", example=1),
 *             @OA\Property(property="productId", type="integer", example=5),
 *             @OA\Property(property="productsSold", type="integer", example=10),
 *             @OA\Property(property="total", type="number", format="float", example=45.00)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Shift product updated"
 *     )
 * )
 */
Flight::route("PUT /shift-products/@id", function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $data = Flight::request()->data;
    $shiftProduct = Flight::sp()->update($data, $id);
    Flight::json($shiftProduct);
});

/**
 * @OA\Delete(
 *     path="/shift-products/{id}",
 *     tags={"Shift Products"},
 *     summary="Delete shift product record",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Shift product deleted"
 *     )
 * )
 */
Flight::route("DELETE /shift-products/@id", function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::sp()->delete($id);
    Flight::json(["message" => "Shift product deleted"]);
});


Flight::route("POST /shift-products/latest", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);

    $data = Flight::request()->data->getData();

    // expected: product_id, products_sold
    $latestShift = Flight::shifts()->getLatestShift();

    if (!$latestShift) {
        Flight::halt(400, "No active shift found");
    }

    Flight::sp()->addShiftProd(
        $latestShift['id'],
        $data['product_id'],
        $data['products_sold']
    );

    Flight::json(["message" => "Product sales added to shift"]);
});