<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/UsersDao.php';

class UsersService extends BaseService {
    public function __construct() {
        $dao = new UsersDao();

        parent::__construct($dao);
    }

    public function searchByName($name){
        return $this->dao->searchByName($name);
    }

    public function get_all_employees()
    {
        return $this->dao->get_all_employees();
    }

    public function get_employee_shifts($user_id) {
        return $this->dao->get_employee_shifts($user_id);
    }

    public function getByRole($role) {
        return $this->dao->getByRole($role);
    }
}