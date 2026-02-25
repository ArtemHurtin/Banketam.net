<?php
require_once __DIR__ . '/Database.php';

class Review {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        // Проверка, что бронь завершена
        $stmt = $this->db->prepare("
            SELECT id FROM bookings 
            WHERE id = ? AND user_id = ? AND status = 'Банкет завершен'
        ");
        $stmt->execute([$data['booking_id'], $data['user_id']]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Отзыв можно оставить только после завершенного банкета'];
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO reviews (user_id, booking_id, rating, comment) 
            VALUES (?, ?, ?, ?)
        ");
        
        try {
            $stmt->execute([
                $data['user_id'],
                $data['booking_id'],
                $data['rating'],
                $data['comment'] ?? ''
            ]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Ошибка сохранения отзыва'];
        }
    }
    
    public function getByBooking($booking_id) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.full_name 
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.booking_id = ?
        ");
        $stmt->execute([$booking_id]);
        return $stmt->fetch();
    }
    
    public function getByUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT r.*, b.hall_id, h.name as hall_name 
            FROM reviews r
            JOIN bookings b ON r.booking_id = b.id
            JOIN halls h ON b.hall_id = h.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
}
?>