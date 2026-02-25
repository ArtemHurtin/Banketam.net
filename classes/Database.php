<?
require_once __DIR__ . '/../config/database.php';

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";doname=" . DB_NAME . ";charset=ut8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Ошибка подключерия к базе данных: " . $e->getMessage());
        }
    }

    public static function getIntense(){
        if (self::$instance=== null) {
            self::$instance = new self ();
        }
    return self::$instance->pdo;
 }
}
?>