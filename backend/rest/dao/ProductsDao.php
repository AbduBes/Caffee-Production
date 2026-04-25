<?php
require_once 'BaseDao.php';

class ProductsDao extends BaseDao{
    protected $table_name;

    public function __construct()
    {
        $this->table_name = "products";
        parent::__construct($this->table_name);
    }

    public function getProductsWithSales()
        {
    $query = "
        SELECT 
            p.id,
            p.name,
            p.price,
            COALESCE(SUM(sp.productsSold), 0) AS totalSold,
            COALESCE(SUM(sp.total), 0) AS totalRevenue
        FROM products p
        LEFT JOIN shiftProducts sp ON p.id = sp.productId
        GROUP BY p.id, p.name, p.price
    ";

    return $this->query($query, []);
    }

    public function updatePrice($id, $newPrice) {
        $query = "UPDATE products SET price = :price WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':price', $newPrice);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }
}