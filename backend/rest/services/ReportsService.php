<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/ReportsDao.php';

class ReportsService extends BaseService {
    public function __construct() {
        $dao = new ReportsDao();
        parent::__construct($dao);
    }

    /**
     * Get complete monthly report
     * @param string $month Format: YYYY-MM (e.g., "2026-02")
     */
    public function getMonthlyReport($month) {
        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            Flight::halt(400, "Invalid month format. Use YYYY-MM");
        }

        // Calculate date range
        $startDate = $month . '-01';
        $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));

        // Get all report data
        $overview = $this->dao->getMonthlyOverview($startDate, $endDate);
        $productSales = $this->dao->getMonthlyProductSales($startDate, $endDate);
        $employeeSales = $this->dao->getMonthlyEmployeeSales($startDate, $endDate);
        $venueCosts = $this->dao->getMonthlyVenueCosts($startDate, $endDate);
        $totalVenueCosts = $this->dao->getMonthlyVenueCostsTotal($startDate, $endDate);
        
        // Calculate net profit (gross profit - venue costs)
        $grossProfit = floatval($overview['gross_profit'] ?? 0);
        $netProfit = $grossProfit - $totalVenueCosts;

        // Format overview
        $formattedOverview = [
            'totalRevenue' => floatval($overview['total_revenue'] ?? 0),
            'totalProductCosts' => floatval($overview['total_product_costs'] ?? 0),
            'totalVenueCosts' => $totalVenueCosts,
            'totalCosts' => floatval($overview['total_product_costs'] ?? 0) + $totalVenueCosts,
            'grossProfit' => $grossProfit,
            'netProfit' => $netProfit,
            'totalShifts' => intval($overview['total_shifts'] ?? 0),
            'totalProductsSold' => intval($overview['total_products_sold'] ?? 0)
        ];

        // Format product sales
        $formattedProductSales = array_map(function($product) {
            return [
                'id' => intval($product['id']),
                'name' => $product['name'],
                'price' => floatval($product['price']),
                'entry_price' => floatval($product['entry_price']),
                'quantity' => intval($product['quantity']),
                'revenue' => floatval($product['revenue']),
                'cost' => floatval($product['cost']),
                'profit' => floatval($product['profit'])
            ];
        }, $productSales);

        // Format employee sales
        $formattedEmployeeSales = array_map(function($employee) {
            return [
                'id' => intval($employee['id']),
                'name' => $employee['name'],
                'shifts' => intval($employee['shifts']),
                'days_worked' => intval($employee['days_worked']),
                'products_sold' => intval($employee['products_sold']),
                'revenue' => floatval($employee['revenue']),
                'profit' => floatval($employee['profit'])
            ];
        }, $employeeSales);

        // Format venue costs
        $formattedVenueCosts = array_map(function($venue) {
            return [
                'id' => intval($venue['id']),
                'name' => $venue['name'],
                'daily_cost' => floatval($venue['daily_cost']),
                'days_active' => intval($venue['days_active']),
                'total_shifts' => intval($venue['total_shifts']),
                'total_cost' => floatval($venue['total_cost']),
                'revenue' => floatval($venue['revenue']),
                'gross_profit' => floatval($venue['gross_profit']),
                'net_profit' => floatval($venue['net_profit'])
            ];
        }, $venueCosts);

        return [
            'month' => $month,
            'overview' => $formattedOverview,
            'productSales' => $formattedProductSales,
            'employeeSales' => $formattedEmployeeSales,
            'venueCosts' => $formattedVenueCosts
        ];
    }

    /**
     * Get daily sales breakdown for a month
     */
    public function getDailySales($month) {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            Flight::halt(400, "Invalid month format. Use YYYY-MM");
        }

        $startDate = $month . '-01';
        $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));

        return $this->dao->getDailySales($startDate, $endDate);
    }

    /**
     * Get product sales by venue
     */
    public function getProductSalesByVenue($month) {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            Flight::halt(400, "Invalid month format. Use YYYY-MM");
        }

        $startDate = $month . '-01';
        $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));

        return $this->dao->getProductSalesByVenue($startDate, $endDate);
    }

    /**
     * Get shift distribution (smena 1 vs smena 2)
     */
    public function getShiftDistribution($month) {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            Flight::halt(400, "Invalid month format. Use YYYY-MM");
        }

        $startDate = $month . '-01';
        $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));

        $distribution = $this->dao->getShiftDistribution($startDate, $endDate);
        
        return array_map(function($shift) {
            return [
                'shift_number' => $shift['shift_number'],
                'shift_name' => $shift['shift_number'] == '1' ? 'Prva Smena' : 'Druga Smena',
                'total_shifts' => intval($shift['total_shifts']),
                'products_sold' => intval($shift['products_sold']),
                'revenue' => floatval($shift['revenue']),
                'profit' => floatval($shift['profit']),
                'avg_revenue_per_shift' => floatval($shift['avg_revenue_per_shift'])
            ];
        }, $distribution);
    }
}