<?php

/**
 * @OA\Get(
 *     path="/reports/monthly",
 *     tags={"Reports"},
 *     summary="Get monthly sales report",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="month",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string", example="2026-02")
 *     ),
 *     @OA\Response(response=200, description="Monthly report data"),
 *     @OA\Response(response=401, description="Unauthorized")
 * )
 */
Flight::route("GET /reports/monthly", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $month = Flight::request()->query->month;
    if (empty($month)) {
        Flight::halt(400, "Month parameter is required (format: YYYY-MM)");
    }
    Flight::json(Flight::reports()->getMonthlyReport($month));
});

/**
 * @OA\Get(
 *     path="/reports/daily",
 *     tags={"Reports"},
 *     summary="Get daily sales breakdown for a month",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="month",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string", example="2026-02")
 *     ),
 *     @OA\Response(response=200, description="Daily sales data"),
 *     @OA\Response(response=401, description="Unauthorized")
 * )
 */
Flight::route("GET /reports/daily", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $month = Flight::request()->query->month;
    if (empty($month)) {
        Flight::halt(400, "Month parameter is required (format: YYYY-MM)");
    }
    Flight::json(Flight::reports()->getDailySales($month));
});

/**
 * @OA\Get(
 *     path="/reports/products-by-venue",
 *     tags={"Reports"},
 *     summary="Get product sales by venue",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="month",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string", example="2026-02")
 *     ),
 *     @OA\Response(response=200, description="Product sales by venue"),
 *     @OA\Response(response=401, description="Unauthorized")
 * )
 */
Flight::route("GET /reports/products-by-venue", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $month = Flight::request()->query->month;
    if (empty($month)) {
        Flight::halt(400, "Month parameter is required (format: YYYY-MM)");
    }
    Flight::json(Flight::reports()->getProductSalesByVenue($month));
});

/**
 * @OA\Get(
 *     path="/reports/shift-distribution",
 *     tags={"Reports"},
 *     summary="Get shift distribution (first vs second shift)",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="month",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string", example="2026-02")
 *     ),
 *     @OA\Response(response=200, description="Shift distribution data"),
 *     @OA\Response(response=401, description="Unauthorized")
 * )
 */
Flight::route("GET /reports/shift-distribution", function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $month = Flight::request()->query->month;
    if (empty($month)) {
        Flight::halt(400, "Month parameter is required (format: YYYY-MM)");
    }
    Flight::json(Flight::reports()->getShiftDistribution($month));
});