<?php
require_once 'BaseDao.php';

class ShiftsDao extends BaseDao {
    protected $table_name;

    public function __construct()
    {
        $this->table_name = "shifts";
        parent::__construct($this->table_name);
    }

    public function getShiftByEmployee($id){
        $query = "SELECT 
  s.*,
  COALESCE(SUM(sp.true_total), 0) AS shift_true_total
FROM shifts s
LEFT JOIN shiftproducts sp 
  ON sp.shift_id = s.id
WHERE s.user_id = :user_id
GROUP BY s.id
ORDER BY s.date DESC;";
        return $this->query($query, ["user_id" => $id]);
    }



    public function getLatestShift() {
    $query = "SELECT * FROM shifts ORDER BY created_at DESC LIMIT 1";
    $results = $this->query($query, []);
    return !empty($results) ? $results[0] : null;
    }


    public function getShiftSummary($shiftId) {
    $query = "
        SELECT
            s.id AS shift_id,
            s.date,
            s.start_time,
            s.end_time,
            u.name AS employee,
            SUM(sp.productsSold) AS total_items,
            SUM(sp.total) AS total_revenue
        FROM shifts s
        LEFT JOIN shiftProducts sp ON s.id = sp.shiftId
        LEFT JOIN users u ON s.user_id = u.id
        WHERE s.id = :shift_id
        GROUP BY s.id
    ";

    return $this->query_unique($query, ["shift_id" => $shiftId]);
    }

    public function getLastFourShifts() {
    $query = "SELECT * FROM shifts ORDER BY created_at DESC LIMIT 4";
    return $this->query($query, []);
    }

}