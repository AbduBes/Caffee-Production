<?php
require_once 'BaseDao.php';

class ReportsDao extends BaseDao {
    protected $table_name;

    public function __construct()
    {
        $this->table_name = "shifts";
        parent::__construct($this->table_name);
    }

    /**
     * Get monthly product sales report
     */
    public function getMonthlyProductSales($startDate, $endDate) {
        $query = "
            SELECT 
                p.id,
                p.name,
                p.price,
                p.entry_price,
                SUM(sp.products_sold) as quantity,
                SUM(sp.total) as revenue,
                SUM(sp.products_sold * sp.entry_price) as cost,
                SUM(sp.true_total) as profit
            FROM products p
            LEFT JOIN shiftproducts sp ON p.id = sp.product_id
            LEFT JOIN shifts s ON sp.shift_id = s.id
            WHERE s.date >= :start_date AND s.date < :end_date
            GROUP BY p.id, p.name, p.price, p.entry_price
            HAVING quantity > 0
            ORDER BY revenue DESC
        ";
        
        return $this->query($query, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Get monthly employee performance report
     * Note: Employee costs are included in venue daily_cost, so no separate commission
     */
    public function getMonthlyEmployeeSales($startDate, $endDate) {
        $query = "
            SELECT 
    u.id,
    u.name,

    COUNT(DISTINCT s.id) AS shifts,
    COUNT(DISTINCT DATE(s.date), s.venue_id) AS days_worked,

    COALESCE(SUM(sp_agg.products_sold), 0) AS products_sold,
    COALESCE(SUM(sp_agg.revenue), 0) AS revenue,
    COALESCE(SUM(sp_agg.profit), 0) AS profit

FROM shifts s
JOIN users u ON u.id = s.user_id

LEFT JOIN (
    SELECT 
        shift_id,
        SUM(products_sold) AS products_sold,
        SUM(total) AS revenue,
        SUM(true_total) AS profit
    FROM shiftproducts
    GROUP BY shift_id
) sp_agg ON sp_agg.shift_id = s.id

WHERE s.date >= :start_date 
  AND s.date < :end_date

GROUP BY u.id, u.name
ORDER BY revenue DESC;
        ";
        
        return $this->query($query, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Get monthly venue costs and profitability
     * Note: daily_cost includes employee costs and covers both shifts per day
     */
    public function getMonthlyVenueCosts($startDate, $endDate) {
        $query = "
            SELECT 
                v.id,
                v.name,
                v.daily_cost,
                COUNT(DISTINCT s.date) as days_active,
                COUNT(DISTINCT s.id) as total_shifts,
                v.daily_cost * COUNT(DISTINCT s.date) as total_cost,
                COALESCE(SUM(sp.total), 0) as revenue,
                COALESCE(SUM(sp.true_total), 0) as gross_profit,
                COALESCE(SUM(sp.true_total), 0) - (v.daily_cost * COUNT(DISTINCT s.date)) as net_profit
            FROM venues v
            LEFT JOIN shifts s ON v.id = s.venue_id 
                AND s.date >= :start_date 
                AND s.date < :end_date
            LEFT JOIN shiftproducts sp ON s.id = sp.shift_id
            GROUP BY v.id, v.name, v.daily_cost
            ORDER BY net_profit DESC
        ";
        
        return $this->query($query, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Get monthly overview metrics
     */
    public function getMonthlyOverview($startDate, $endDate) {
        $query = "
            SELECT 
                COUNT(DISTINCT s.id) as total_shifts,
                SUM(sp.products_sold) as total_products_sold,
                SUM(sp.total) as total_revenue,
                SUM(sp.products_sold * sp.entry_price) as total_product_costs,
                SUM(sp.true_total) as gross_profit
            FROM shifts s
            LEFT JOIN shiftproducts sp ON s.id = sp.shift_id
            WHERE s.date >= :start_date AND s.date < :end_date
        ";
        
        return $this->query_unique($query, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Get total venue costs for the period
     */
    public function getMonthlyVenueCostsTotal($startDate, $endDate) {
    $query = "
        SELECT 
            v.id,
            v.daily_cost * COUNT(DISTINCT s.date) as venue_cost
        FROM venues v
        LEFT JOIN shifts s ON v.id = s.venue_id 
            AND s.date >= :start_date 
            AND s.date < :end_date
        GROUP BY v.id, v.daily_cost
    ";
    
    $result = $this->query($query, [
        'start_date' => $startDate,
        'end_date' => $endDate
    ]);
    
    $total = 0;
    foreach ($result as $row) {
        $total += floatval($row['venue_cost'] ?? 0);
    }
    
    return $total;
}

    /**
     * Get daily sales breakdown for a month
     */
    public function getDailySales($startDate, $endDate) {
        $query = "
            SELECT 
                s.date,
                COUNT(DISTINCT s.id) as shifts,
                SUM(sp.products_sold) as products_sold,
                SUM(sp.total) as revenue,
                SUM(sp.true_total) as profit
            FROM shifts s
            LEFT JOIN shiftproducts sp ON s.id = sp.shift_id
            WHERE s.date >= :start_date AND s.date < :end_date
            GROUP BY s.date
            ORDER BY s.date ASC
        ";
        
        return $this->query($query, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Get product sales by venue
     */
    public function getProductSalesByVenue($startDate, $endDate) {
        $query = "
            SELECT 
                v.name as venue_name,
                p.name as product_name,
                SUM(sp.products_sold) as quantity,
                SUM(sp.total) as revenue
            FROM venues v
            INNER JOIN shifts s ON v.id = s.venue_id
            INNER JOIN shiftproducts sp ON s.id = sp.shift_id
            INNER JOIN products p ON sp.product_id = p.id
            WHERE s.date >= :start_date AND s.date < :end_date
            GROUP BY v.id, v.name, p.id, p.name
            ORDER BY v.name, revenue DESC
        ";
        
        return $this->query($query, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Get shift distribution (first shift vs second shift)
     */
    public function getShiftDistribution($startDate, $endDate) {
        $query = "
            SELECT 
                s.smena as shift_number,
                COUNT(DISTINCT s.id) as total_shifts,
                SUM(sp.products_sold) as products_sold,
                SUM(sp.total) as revenue,
                SUM(sp.true_total) as profit,
                AVG(sp.total) as avg_revenue_per_shift
            FROM shifts s
            LEFT JOIN shiftproducts sp ON s.id = sp.shift_id
            WHERE s.date >= :start_date AND s.date < :end_date
            GROUP BY s.smena
            ORDER BY s.smena
        ";
        
        return $this->query($query, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
}