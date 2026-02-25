<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Проверяем наличие необходимых файлов
if (!file_exists('classes/Database.php')) {
    die('Ошибка: файл classes/Database.php не найден. Проверьте структуру проекта.');
}
if (!file_exists('classes/Hall.php') && !file_exists('classes/Halls.php')) {
    die('Ошибка: файл класса Hall не найден (нужен Hall.php или Halls.php).');
}


require_once 'classes/Database.php';


if (file_exists('classes/Hall.php')) {
    require_once 'classes/Hall.php';
} elseif (file_exists('classes/Halls.php')) {
    require_once 'classes/Halls.php';
    // Если класс называется Halls, а не Hall, создадим алиас или проверим
    if (!class_exists('Hall') && class_exists('Halls')) {
        class_alias('Halls', 'Hall');
    }
}

// Проверяем существование класса Hall
if (!class_exists('Hall')) {
    die('Ошибка: класс Hall не определён. Проверьте имя класса в файле.');
}

try {
    $hallModel = new Hall();
    $halls = $hallModel->getAll();
} catch (Exception $e) {
    die('Ошибка при загрузке залов: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Банкетам.Нет — Главная</title>
    <!-- Единый файл стилей -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Иконки Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- Шапка с навигацией -->
<header class="header">
    <div class="container">
        <h1>Банкетам.Нет</h1>
        <p class="subtitle">Бронирование помещений для банкетов</p>

        <nav class="nav">
            <a href="pages/login.php">Войти</a>
            <a href="pages/register.php">Регистрация</a>
            <a href="pages/booking.php">Оформить заявку</a>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="pages/admin/dashboard.php">Панель администратора</a>
            <?php else: ?>
                <a href="pages/admin/dashboard.php">Админка</a> <!-- ссылка для входа админа -->
            <?php endif; ?>
        </nav>
    </div>
</header>

<!-- Слайдер (JS-версия с кнопками и точками) -->
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

<!-- Основной контент -->
<main class="container">
    <!-- Секция "О нас" -->
    <section>
        <h2>О нас</h2>
        <p>Банкетам.Нет — сервис для бронирования помещений для банкетов: зал, ресторан, летняя веранда, закрытая веранда. Мы помогаем организовать ваше мероприятие быстро и удобно.</p>
    </section>

    <!-- Преимущества -->
    <section>
        <h3>Наши преимущества</h3>
        <ul>
            <li>Широкий выбор банкетных залов</li>
            <li>Гибкий выбор дат и времени</li>
            <li>Разные способы оплаты (наличные, карта, онлайн)</li>
            <li>Контроль заявок через личный кабинет</li>
            <li>Отзывы от реальных гостей</li>
        </ul>
    </section>

    <!-- Список залов -->
    <section>
        <h3>Популярные залы</h3>
        <div class="halls-grid">
            <?php if (empty($halls)): ?>
                <p>Пока нет доступных залов. Добавьте их через админ-панель.</p>
            <?php else: ?>
                <?php foreach ($halls as $hall): ?>
                <div class="hall-card">
                    <div class="hall-image" style="background-image: url('<?php echo htmlspecialchars($hall['image'] ?? 'assets/images/halls/default.jpg'); ?>');">
                        <?php if (empty($hall['image'])): ?>
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
            <?php endif; ?>
        </div>
    </section>
</main>

<!-- Футер -->
<footer class="footer">
    <p class="help-text">© 2026 Банкетам.Нет. Все права защищены.</p>
</footer>

<!-- Скрипт слайдера -->
<script src="assets/js/slider.js"></script>
<!-- Дополнительный скрипт для инициализации, если нужно -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ImageSlider !== 'undefined' && document.getElementById('main-slider')) {
            new ImageSlider('main-slider');
        }
    });
</script>
</body>
</html>