<?php
require_once 'BaseDao.php';

class UsersDao extends BaseDao {
    protected $table_name;

    public function __construct()
    {
        $this->table_name = "users";
        parent::__construct($this->table_name);
    }


    public function searchByName($name)
    {
        $query = "SELECT * FROM users WHERE name LIKE :name";
        return $this->query($query, ["name" => "%$name%"]);
    }
    
    public function get_all_employees()
    {
        $query = "SELECT id, name FROM users WHERE role = 'user' ORDER BY name";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function get_employee_shifts($user_id) {
  $sql = "
    SELECT
      s.id AS shift_id,
      s.date,
      s.smena,
      s.created_at,
      COALESCE(SUM(sp.total), 0) AS revenue,
      COALESCE(SUM(sp.true_total), 0) AS true_total
    FROM shifts s
    LEFT JOIN shiftproducts sp 
      ON s.id = sp.shift_id
    WHERE s.user_id = :user_id
    GROUP BY s.id
    ORDER BY s.date DESC
  ";

  $stmt = $this->connection->prepare($sql);
  $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
  $stmt->execute();

  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getByRole($role) {
        $query = "SELECT name, id FROM users WHERE role = :role";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}