-- Создание базы данных
CREATE DATABASE IF NOT EXISTS banquet_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE banquet_db;

-- Таблица пользователей
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица залов
CREATE TABLE halls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('зал', 'ресторан', 'летняя веранда', 'закрытая веранда') NOT NULL,
    description TEXT,
    capacity INT NOT NULL,
    price_per_hour DECIMAL(10,2) NOT NULL,
    address VARCHAR(255) NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица бронирований
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hall_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    guests_count INT NOT NULL,
    payment_method ENUM('cash', 'card', 'online') NOT NULL,
    status ENUM('Новая', 'Банкет назначен', 'Банкет завершен') DEFAULT 'Новая',
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hall_id) REFERENCES halls(id) ON DELETE CASCADE,
    INDEX idx_booking_date (booking_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица отзывов
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_booking (user_id, booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Добавляем администратора (пароль: Demo20 - нужно заменить хеш!)
INSERT INTO users (login, password, full_name, phone, email, role) VALUES 
('Admin26', '$2y$10$YourHashHere123456789012345678901234567890', 'Администратор', '+7 (999) 999-99-99', 'admin@banquet.ru', 'admin');

--  тестовые залы
INSERT INTO halls (name, type, description, capacity, price_per_hour, address, image) VALUES
('Золотой зал', 'зал', 'Роскошный банкетный зал с панорамными окнами. Идеально подходит для свадеб и юбилеев. Есть своя парковка, гардероб, сцена.', 150, 5000.00, 'ул. Ленина, 15', 'assets/images/halls/golden.jpg'),
('Летняя терраса', 'летняя веранда', 'Открытая веранда с видом на парк. Живая музыка по выходным. Работает только в теплое время года.', 80, 3500.00, 'пр. Мира, 32', 'assets/images/halls/terrace.jpg'),
('Ресторан Уют', 'ресторан', 'Уютный ресторан с живой музыкой. Европейская и русская кухня. Отдельный зал для банкетов.', 120, 4500.00, 'ул. Пушкина, 10', 'assets/images/halls/cozy.jpg'),
('Зимний сад', 'закрытая веранда', 'Закрытая отапливаемая веранда с растениями. Круглогодично. Панорамное остекление.', 60, 4000.00, 'ул. Гагарина, 5', 'assets/images/halls/winter.jpg');

-- тестовые бронирования (для примера)
INSERT INTO bookings (user_id, hall_id, booking_date, booking_time, guests_count, payment_method, status, comment) VALUES
(1, 1, '2025-04-15', '18:00:00', 100, 'card', 'Новая', 'Просьба украсить зал цветами'),
(1, 2, '2025-03-20', '15:00:00', 50, 'cash', 'Банкет назначен', 'Вегетарианское меню'),
(1, 3, '2025-02-10', '19:00:00', 80, 'online', 'Банкет завершен', 'Все отлично, спасибо!');

--  тестовый отзыв (для завершенного банкета)
INSERT INTO reviews (user_id, booking_id, rating, comment) VALUES
(1, 3, 5, 'Отличное место! Вкусная еда, приятная атмосфера, вежливый персонал. Обязательно придем еще!');

-- Создаем представление для удобного просмотра бронирований с данными пользователей и залов
CREATE VIEW view_bookings_full AS
SELECT 
    b.*,
    u.full_name as user_name,
    u.phone as user_phone,
    u.email as user_email,
    h.name as hall_name,
    h.type as hall_type,
    h.address as hall_address,
    h.capacity as hall_capacity,
    h.price_per_hour as hall_price,
    r.id as review_id,
    r.rating as review_rating,
    r.comment as review_comment
FROM bookings b
JOIN users u ON b.user_id = u.id
JOIN halls h ON b.hall_id = h.id
LEFT JOIN reviews r ON b.id = r.booking_id;

-- индексы для быстрого поиска
CREATE INDEX idx_users_login ON users(login);
CREATE INDEX idx_halls_type ON halls(type);
CREATE INDEX idx_halls_capacity ON halls(capacity);
CREATE INDEX idx_bookings_user ON bookings(user_id);
CREATE INDEX idx_bookings_hall ON bookings(hall_id);
CREATE INDEX idx_bookings_date_status ON bookings(booking_date, status);