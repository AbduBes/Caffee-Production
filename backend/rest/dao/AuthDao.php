<?php
require_once __DIR__ . '/BaseDao.php';


class AuthDao extends BaseDao {
   protected $table_name;


   public function __construct() {
       $this->table_name = "users";
       parent::__construct($this->table_name);
   }


   public function get_user_by_email($email) {
    // Check users
    $query = "SELECT id AS id, name, email, password, role FROM users WHERE email = :email";
    $user = $this->query_unique($query, ['email' => $email]);

    return $user;
}
}