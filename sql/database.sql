CREATE DATABASE IF NOT EXISTS banquet_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci:
USE banquet_db;

-- Пользователи
CREATE TABLE users (
    id INT AUTO_INCEREMENT PRIMARY KEY,
    login VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--Залы
CREATE TABLE halls (
    id INT AUTO_INCEREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('зал','ресторан', 'летняя веранда', 'закрытая веранда') NOT NULL,
    description TEXT,
    capacity INT NOT NULL
    price_per_hour DECIMAL(10,2) NOT NULL,
    adress VARCHAR(255) NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--Бронирование
CREATE TABLE bookings (
    id INT AUTO_INCEREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hall_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    guests_count INT NOT NULL,
    payment_method ENUM('cash', 'card', 'online') NOT NULL,
    status ENUM ('Новая', 'Банкет назначен', 'Банкет завершен') DEFAULT 'Новая',
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREGIN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREGIN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_booking (user_id, booking_id)
);

--Администратор (пароль: Demo20)

INSERT INTO users (login, passord, full_name, phone, email, role) VALUES
('Admin26', 'добавлюхэшпотом','Администратор''+7(xxx)-xxx-xx-xx', 'adminbanquet.ru', 'admin');

-- Тестовые залы

INSERT INTO halls (name, type, description, capacity, price_per_hour, adress, image) VALUES
('Золотой зал', 'зал', 'роскошный банкетныц зал с панорамными окнами'150, 1500, ул.XXX, xx )