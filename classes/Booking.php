<?php
require_once __DIR__ . '/Database.php';

class Booking {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        // Проверка на существующую бронь
        $stmt = $this->db->prepare("
            SELECT id FROM bookings 
            WHERE hall_id = ? AND booking_date = ? AND booking_time = ?
        ");
        $stmt->execute([$data['hall_id'], $data['booking_date'], $data['booking_time']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Это время уже занято'];
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO bookings (user_id, hall_id, booking_date, booking_time, guests_count, payment_method, comment) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        try {
            $stmt->execute([
                $data['user_id'],
                $data['hall_id'],
                $data['booking_date'],
                $data['booking_time'],
                $data['guests_count'],
                $data['payment_method'],
                $data['comment'] ?? ''
            ]);
            return ['success' => true, 'booking_id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Ошибка создания заявки'];
        }
    }
    
    public function getUserBookings($user_id) {
        $stmt = $this->db->prepare("
            SELECT b.*, h.name as hall_name, h.type as hall_type, h.image as hall_image,
                   r.id as review_id, r.rating as review_rating, r.comment as review_comment
            FROM bookings b
            JOIN halls h ON b.hall_id = h.id
            LEFT JOIN reviews r ON b.id = r.booking_id
            WHERE b.user_id = ?
            ORDER BY b.booking_date DESC, b.booking_time DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    public function getAll($filters = [], $limit = 10, $offset = 0) {
        $sql = "
            SELECT b.*, u.full_name, u.login, u.phone, u.email, h.name as hall_name, h.type as hall_type
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN halls h ON b.hall_id = h.id
            WHERE 1=1
        ";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['hall_id'])) {
            $sql .= " AND b.hall_id = ?";
            $params[] = $filters['hall_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (u.full_name LIKE ? OR u.phone LIKE ? OR h.name LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) FROM bookings b WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    public function updateStatus($booking_id, $status) {
        $stmt = $this->db->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $booking_id]);
    }
}
?>