<?php
require_once 'BaseDao.php';

class ShiftProductsDao extends BaseDao {

    protected $table_name;

    public function __construct()
    {
        $this->table_name = "shiftproducts";
        parent::__construct($this->table_name);
    }

    /**
     * Get product price and entry_price from products table
     */
    private function getProductPrices($product_id) {
        $query = "SELECT price, entry_price FROM products WHERE id = :product_id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate total and true_total based on product prices and quantity
     */
    private function calculateTotals($product_id, $products_sold, $entry_price = null) {
        $product = $this->getProductPrices($product_id);
        
        if (!$product) {
            throw new Exception("Product not found");
        }

        // If entry_price not provided, use the one from products table
        if ($entry_price === null) {
            $entry_price = $product['entry_price'];
        }

        $total = $product['price'] * $products_sold;
        $true_total = $total - ($entry_price * $products_sold);

        return [
            'total' => $total,
            'true_total' => $true_total,
            'entry_price' => $entry_price
        ];
    }

    /**
     * Override add to include calculations
     */
    public function add($entity) {
        // Calculate totals before inserting
        $calculations = $this->calculateTotals(
            $entity['product_id'],
            $entity['products_sold'],
            $entity['entry_price'] ?? null
        );

        // Add calculated values to entity
        $entity['total'] = $calculations['total'];
        $entity['true_total'] = $calculations['true_total'];
        $entity['entry_price'] = $calculations['entry_price'];

        // Call parent add method
        return parent::add($entity);
    }

    /**
     * Override update to include calculations
     */
    public function update($entity, $id, $id_column = "id") {
        // Calculate totals before updating
        $calculations = $this->calculateTotals(
            $entity['product_id'],
            $entity['products_sold'],
            $entity['entry_price'] ?? null
        );

        // Add calculated values to entity
        $entity['total'] = $calculations['total'];
        $entity['true_total'] = $calculations['true_total'];
        $entity['entry_price'] = $calculations['entry_price'];

        // Call parent update method
        return parent::update($entity, $id, $id_column);
    }

    public function getByShiftId($shiftId) {
        $stmt = $this->connection->prepare(
            "SELECT sp.*, p.name, p.price FROM shiftproducts sp
             JOIN products p ON sp.product_id = p.id
             WHERE sp.shift_id = :shiftId"
        );
        $stmt->bindParam(':shiftId', $shiftId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getShiftSales($shiftId) {
        $query = "SELECT 
    p.name AS product_name,
    sp.products_sold AS quantity_sold,
    sp.total AS total_sale,
    sp.entry_price AS entry_price,
    p.price AS price,
    sp.true_total AS true_total
FROM shiftproducts sp
JOIN products p ON sp.product_id = p.id
WHERE sp.shift_id = :shiftId;";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':shiftId', $shiftId);   
        $stmt->execute();
        return $stmt->fetchAll();
    }   
}