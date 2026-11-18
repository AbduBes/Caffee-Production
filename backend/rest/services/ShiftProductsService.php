<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/ShiftProductsDao.php';


class ShiftProductsService extends BaseService {
    public function __construct() {
        $dao = new ShiftProductsDao();

        parent::__construct($dao);
    }

    public function add($entity)
    {
        if($entity['products_sold'] <= 0 ) {
            Flight::halt(400, "Invalid input");
        }
        
        return $this->dao->add($entity);
    }

    public function getShiftSales($shiftId){
        return $this->dao->getShiftSales($shiftId);
    }
    public function getByShiftId($shiftId){
        return $this->dao->getByShiftId($shiftId);
    }      
}