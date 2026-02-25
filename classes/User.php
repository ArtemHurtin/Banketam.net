<?php
require_once __DIR__ . '/Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function register($data) {
        // Проверка уникальности логина
        $stmt = $this->db->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$data['login']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Логин уже занят'];
        }
        
        // Валидация
        if (!preg_match('/^[a-zA-Z0-9]{6,}$/', $data['login'])) {
            return ['success' => false, 'message' => 'Логин должен содержать только латинские буквы и цифры, минимум 6 символов'];
        }
        
        if (strlen($data['password']) < 8) {
            return ['success' => false, 'message' => 'Пароль должен быть не менее 8 символов'];
        }
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (login, password, full_name, phone, email) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        try {
            $stmt->execute([
                $data['login'],
                $hashedPassword,
                $data['full_name'],
                $data['phone'],
                $data['email']
            ]);
            return ['success' => true, 'user_id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Ошибка регистрации'];
        }
    }
    
    public function login($login, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return ['success' => true, 'user' => $user];
        }
        return ['success' => false, 'message' => 'Неверный логин или пароль'];
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, login, full_name, phone, email, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT id, login, full_name, phone, email, role, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
}
?>