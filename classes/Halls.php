<?php
require_once __DIR__ . '/Database.php';
if (!class_exists('Database')) {
    die('Класс Database не загружен');
}

class Hall {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM halls ORDER BY name");
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM halls WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO halls (name, type, description, capacity, price_per_hour, address, image) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['name'],
            $data['type'],
            $data['description'],
            $data['capacity'],
            $data['price_per_hour'],
            $data['address'],
            $data['image'] ?? null
        ]);
    }
    
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE halls 
            SET name=?, type=?, description=?, capacity=?, price_per_hour=?, address=?, image=?
            WHERE id=?
        ");
        return $stmt->execute([
            $data['name'],
            $data['type'],
            $data['description'],
            $data['capacity'],
            $data['price_per_hour'],
            $data['address'],
            $data['image'] ?? null,
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM halls WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>