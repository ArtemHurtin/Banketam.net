<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Hall.php';

$hallModel = new Hall();
$halls = $hallModel->getAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Банкетам.Нет - Главная</title>
    <link rel="stylesheet" href="assets/css/mobile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <div class="status-bar">
            <span>9:41</span>
            <span><i class="fas fa-signal"></i> <i class="fas fa-wifi"></i> <i class="fas fa-battery-full"></i></span>
        </div>
        
        <div class="content">
            <!-- Слайдер -->
            <div class="slider-container" id="main-slider">
                <div class="slider">
                    <div class="slide"><img src="assets/images/slider/slide1.jpg" alt="Банкетный зал"></div>
                    <div class="slide"><img src="assets/images/slider/slide2.jpg" alt="Ресторан"></div>
                    <div class="slide"><img src="assets/images/slider/slide3.jpg" alt="Летняя веранда"></div>
                    <div class="slide"><img src="assets/images/slider/slide4.jpg" alt="Закрытая веранда"></div>
                </div>
                <button class="slider-btn prev"><i class="fas fa-chevron-left"></i></button>
                <button class="slider-btn next"><i class="fas fa-chevron-right"></i></button>
                <div class="slider-dots"></div>
            </div>
            
            <h2 style="margin-bottom: 20px;">Добро пожаловать!</h2>
            
            <!-- Блок с залами -->
            <div class="halls-list">
                <?php foreach ($halls as $hall): ?>
                <div class="hall-card">
                    <div class="hall-image" style="background-image: url('<?php echo htmlspecialchars($hall['image'] ?? 'assets/images/halls/default.jpg'); ?>');">
                        <?php if (!$hall['image']): ?>
                            <i class="fas fa-image"></i>
                        <?php endif; ?>
                    </div>
                    <h3 class="hall-title"><?php echo htmlspecialchars($hall['name']); ?></h3>
                    <div class="hall-details">
                        <span class="hall-detail-item">
                            <i class="fas fa-users"></i> до <?php echo $hall['capacity']; ?> чел
                        </span>
                        <span class="hall-detail-item">
                            <i class="fas fa-tag"></i> <?php echo number_format($hall['price_per_hour'], 0, '', ' '); ?> ₽/час
                        </span>
                        <span class="hall-detail-item">
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hall['address']); ?>
                        </span>
                    </div>
                    <a href="pages/booking.php?hall_id=<?php echo $hall['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-calendar-plus"></i> Забронировать
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Нижняя навигация -->
        <div class="bottom-nav">
            <a href="index.php" class="active"><i class="fas fa-home"></i></a>
            <a href="pages/profile.php"><i class="fas fa-user"></i></a>
            <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="pages/login.php"><i class="fas fa-sign-in-alt"></i></a>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="assets/js/slider.js"></script>
    <style>
        .bottom-nav {
            display: flex;
            justify-content: space-around;
            padding: 16px;
            background: white;
            border-top: 1px solid var(--light);
        }
        .bottom-nav a {
            color: var(--gray);
            font-size: 20px;
            transition: color 0.3s;
        }
        .bottom-nav a.active {
            color: var(--primary);
        }
        .hall-image {
            background-size: cover;
            background-position: center;
        }
    </style>
</body>
</html>