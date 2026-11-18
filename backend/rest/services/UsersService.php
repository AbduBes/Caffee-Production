<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/UsersDao.php';

class UsersService extends BaseService {
    public function __construct() {
        $dao = new UsersDao();

        parent::__construct($dao);
    }

    /** Never send password hashes to the client. */
    private static function withoutPassword($user) {
        if (is_array($user)) {
            unset($user['password']);
        }
        return $user;
    }

    /** Only these fields can be set through the users API. */
    private static function allowedFields($data, $allowRole) {
        $fields = ['name', 'email', 'password'];
        if ($allowRole) {
            $fields[] = 'role';
        }
        $entity = [];
        foreach ($fields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $entity[$field] = $data[$field];
            }
        }
        if (isset($entity['role']) && !in_array($entity['role'], [Roles::ADMIN, Roles::USER], true)) {
            Flight::halt(400, 'Invalid role');
        }
        if (isset($entity['password'])) {
            $entity['password'] = password_hash($entity['password'], PASSWORD_BCRYPT);
        }
        return $entity;
    }

    public function getById($id) {
        return self::withoutPassword($this->dao->getById($id));
    }

    public function add($data) {
        $entity = self::allowedFields($data, true);
        if (empty($entity['name']) || empty($entity['email']) || empty($entity['password'])) {
            Flight::halt(400, 'Name, email and password are required.');
        }
        $entity['role'] = $entity['role'] ?? Roles::USER;
        return self::withoutPassword($this->dao->add($entity));
    }

    public function update($data, $id, $id_column = "id", $allowRole = false) {
        $entity = self::allowedFields($data, $allowRole);
        if (empty($entity)) {
            Flight::halt(400, 'Nothing to update.');
        }
        return self::withoutPassword($this->dao->update($entity, $id, $id_column));
    }

    public function searchByName($name){
        return array_map([self::class, 'withoutPassword'], $this->dao->searchByName($name));
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