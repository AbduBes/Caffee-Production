<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/ShiftsDao.php';

class ShiftsService extends BaseService {
    public function __construct() {
        $dao = new ShiftsDao();

        parent::__construct($dao);
    }

    public function add($entity)
    {
        return $this->dao->add($entity);
    }

    public function getLastFourShifts() {
        return $this->dao->getLastFourShifts();
    }

    public function getShiftByEmployee($id){
        return $this->dao->getShiftByEmployee($id);
    }

    public function getLatestShift() {
        return $this->dao->getLatestShift();
    }

    public function getShiftSummary($shiftId) {
        return $this->dao->getShiftSummary($shiftId);
    }
}