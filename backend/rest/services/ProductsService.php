<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/ProductsDao.php';

class ProductsService extends BaseService {
    public function __construct() {
        $dao = new ProductsDao();

        parent::__construct($dao);
    }

    public function add($entity)
    {
        if(empty($entity['name']) || $entity['price'] < 0 ) {
            Flight::halt(400, "Invalid input");
        }
        return $this->dao->add($entity);
    }

    public function getProductsWithSales () {
        return $this->dao->getProductsWithSales();
    }
    public function updatePrice($id, $newPrice) {
        if ($newPrice < 0) {
            Flight::halt(400, "Price cannot be negative");
        }
        $this->dao->updatePrice($id, $newPrice);
    }
}