<?php
session_start();
require_once '../classes/User.php';
require_once '../classes/Booking.php';
require_once '../classes/Review.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userModel = new User();
$bookingModel = new Booking();
$reviewModel = new Review();

$user = $userModel->getById($_SESSION['user_id']);
$bookings = $bookingModel->getUserBookings($_SESSION['user_id']);

// Обработка отправки отзыва
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review'])) {
    $result = $reviewModel->create([
        'user_id' => $_SESSION['user_id'],
        'booking_id' => $_POST['booking_id'],
        'rating' => $_POST['rating'],
        'comment' => $_POST['comment']
    ]);
    
    if ($result['success']) {
        header('Location: profile.php?review_added=1');
        exit;
    } else {
        $review_error = $result['message'];
    }
}

// Подсчет статистики
$totalBookings = count($bookings);
$completedBookings = count(array_filter($bookings, fn($b) => $b['status'] === 'Банкет завершен'));
$reviewsCount = count(array_filter($bookings, fn($b) => !empty($b['review_id'])));
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Личный кабинет - Банкетам.Нет</title>
    <link rel="stylesheet" href="../assets/css/mobile.css">
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
                    <div class="slide"><img src="../assets/images/slider/slide1.jpg" alt="Банкетный зал"></div>
                    <div class="slide"><img src="../assets/images/slider/slide2.jpg" alt="Ресторан"></div>
                    <div class="slide"><img src="../assets/images/slider/slide3.jpg" alt="Летняя веранда"></div>
                    <div class="slide"><img src="../assets/images/slider/slide4.jpg" alt="Закрытая веранда"></div>
                </div>
                <button class="slider-btn prev"><i class="fas fa-chevron-left"></i></button>
                <button class="slider-btn next"><i class="fas fa-chevron-right"></i></button>
                <div class="slider-dots"></div>
            </div>
            
            <!-- Профиль пользователя -->
            <div class="profile-header" style="text-align: center; margin-bottom: 24px;">
                <div style="width: 80px; height: 80px; background: linear-gradient(45deg, var(--primary), var(--secondary)); border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user" style="font-size: 40px; color: white;"></i>
                </div>
                <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p style="color: #666;"><?php echo htmlspecialchars($user['phone']); ?></p>
            </div>
            
            <?php if (isset($_GET['review_added'])): ?>
                <div class="notification success" style="display: flex; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i>
                    <span>Отзыв успешно добавлен!</span>
                </div>
            <?php endif; ?>
            
            <!-- Статистика -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px;">
                <div style="background: var(--light); padding: 12px; border-radius: 12px; text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: var(--primary);"><?php echo $totalBookings; ?></div>
                    <div style="font-size: 12px; color: #666;">Заявки</div>
                </div>
                <div style="background: var(--light); padding: 12px; border-radius: 12px; text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: var(--success);"><?php echo $completedBookings; ?></div>
                    <div style="font-size: 12px; color: #666;">Завершено</div>
                </div>
                <div style="background: var(--light); padding: 12px; border-radius: 12px; text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: var(--warning);"><?php echo $reviewsCount; ?></div>
                    <div style="font-size: 12px; color: #666;">Отзывы</div>
                </div>
            </div>
            
            <!-- Фильтры заявок -->
            <div style="display: flex; gap: 8px; margin-bottom: 16px;">
                <select class="filter-select" id="status-filter">
                    <option value="all">Все статусы</option>
                    <option value="Новая">Новые</option>
                    <option value="Банкет назначен">Подтвержденные</option>
                    <option value="Банкет завершен">Завершенные</option>
                </select>
                <select class="filter-select" id="sort-filter">
                    <option value="desc">Сначала новые</option>
                    <option value="asc">Сначала старые</option>
                </select>
            </div>
            
            <!-- Список заявок -->
            <h3 style="margin-bottom: 16px;">Мои заявки</h3>
            
            <div id="bookings-list">
                <?php if (empty($bookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>У вас пока нет заявок</p>
                        <a href="booking.php" class="btn btn-primary" style="margin-top: 16px;">Создать заявку</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                    <div class="booking-item" data-status="<?php echo $booking['status']; ?>" data-date="<?php echo $booking['booking_date']; ?>">
                        <div class="booking-header">
                            <span class="booking-hall"><?php echo htmlspecialchars($booking['hall_name']); ?></span>
                            <span class="status-badge 
                                <?php 
                                echo $booking['status'] == 'Новая' ? 'status-new' : 
                                    ($booking['status'] == 'Банкет назначен' ? 'status-confirmed' : 'status-completed'); 
                                ?>">
                                <?php echo $booking['status']; ?>
                            </span>
                        </div>
                        <div class="booking-date">
                            <i class="far fa-calendar"></i> 
                            <?php echo date('d.m.Y', strtotime($booking['booking_date'])); ?> в 
                            <?php echo substr($booking['booking_time'], 0, 5); ?>
                        </div>
                        <div class="booking-details">
                            <div class="booking-detail">
                                <span class="detail-label">Гостей</span>
                                <span class="detail-value"><?php echo $booking['guests_count']; ?> чел</span>
                            </div>
                            <div class="booking-detail">
                                <span class="detail-label">Оплата</span>
                                <span class="detail-value">
                                    <?php 
                                    $methods = ['cash' => 'Наличные', 'card' => 'Картой', 'online' => 'Онлайн'];
                                    echo $methods[$booking['payment_method']] ?? $booking['payment_method'];
                                    ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if ($booking['status'] == 'Банкет завершен' && empty($booking['review_id'])): ?>
                            <button class="btn btn-outline" style="margin-top: 12px;" onclick="openReviewModal(<?php echo $booking['id']; ?>)">
                                <i class="far fa-star"></i> Оставить отзыв
                            </button>
                        <?php endif; ?>
                        
                        <?php if (!empty($booking['review_id'])): ?>
                            <div style="margin-top: 12px; padding: 12px; background: var(--light); border-radius: 8px;">
                                <div style="display: flex; align-items: center; gap: 4px; margin-bottom: 4px;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="<?php echo $i <= $booking['review_rating'] ? 'fas' : 'far'; ?> fa-star" style="color: #FFD700;"></i>
                                    <?php endfor; ?>
                                </div>
                                <span style="font-size: 14px;"><?php echo nl2br(htmlspecialchars($booking['review_comment'])); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Нижняя навигация -->
        <div style="display: flex; justify-content: space-around; padding: 16px; background: white; border-top: 1px solid var(--light);">
            <a href="../index.php" style="color: var(--gray);"><i class="fas fa-home fa-xl"></i></a>
            <a href="booking.php" style="color: var(--gray);"><i class="fas fa-plus-circle fa-xl"></i></a>
            <a href="#" style="color: var(--primary);"><i class="fas fa-user fa-xl"></i></a>
            <a href="logout.php" style="color: var(--gray);"><i class="fas fa-sign-out-alt fa-xl"></i></a>
        </div>
    </div>
    
    <!-- Модальное окно отзыва -->
    <div id="reviewModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: flex-end;">
        <div style="background: white; border-radius: 30px 30px 0 0; padding: 24px; width: 100%; max-width: 390px; margin: 0 auto;">
            <h3 style="margin-bottom: 20px;">Оставить отзыв</h3>
            <form method="POST" id="review-form">
                <input type="hidden" name="booking_id" id="review-booking-id">
                <div class="form-group">
                    <label class="form-label">Оценка</label>
                    <div class="rating-stars">
                        <i class="far fa-star" data-rating="1"></i>
                        <i class="far fa-star" data-rating="2"></i>
                        <i class="far fa-star" data-rating="3"></i>
                        <i class="far fa-star" data-rating="4"></i>
                        <i class="far fa-star" data-rating="5"></i>
                    </div>
                    <input type="hidden" name="rating" id="rating-value" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Комментарий</label>
                    <textarea class="form-control" name="comment" rows="4" placeholder="Поделитесь впечатлениями..."></textarea>
                </div>
                <button type="submit" name="add_review" class="btn btn-primary">Отправить отзыв</button>
                <button type="button" class="btn btn-outline" style="margin-top: 8px;" onclick="closeReviewModal()">Закрыть</button>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/slider.js"></script>
    <script>
        // Фильтрация и сортировка
        document.getElementById('status-filter').addEventListener('change', filterBookings);
        document.getElementById('sort-filter').addEventListener('change', filterBookings);
        
        function filterBookings() {
            const status = document.getElementById('status-filter').value;
            const sort = document.getElementById('sort-filter').value;
            const bookings = document.querySelectorAll('.booking-item');
            
            let visibleBookings = [];
            
            bookings.forEach(booking => {
                const bookingStatus = booking.dataset.status;
                if (status === 'all' || bookingStatus === status) {
                    booking.style.display = 'block';
                    visibleBookings.push(booking);
                } else {
                    booking.style.display = 'none';
                }
            });
            
            // Сортировка
            if (sort === 'desc') {
                visibleBookings.sort((a, b) => new Date(b.dataset.date) - new Date(a.dataset.date));
            } else {
                visibleBookings.sort((a, b) => new Date(a.dataset.date) - new Date(b.dataset.date));
            }
            
            const container = document.getElementById('bookings-list');
            visibleBookings.forEach(booking => container.appendChild(booking));
        }
        
        // Модальное окно отзыва
        function openReviewModal(bookingId) {
            document.getElementById('review-booking-id').value = bookingId;
            document.getElementById('reviewModal').style.display = 'flex';
        }
        
        function closeReviewModal() {
            document.getElementById('reviewModal').style.display = 'none';
        }
        
        // Звезды рейтинга
        document.querySelectorAll('.rating-stars i').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                document.getElementById('rating-value').value = rating;
                
                document.querySelectorAll('.rating-stars i').forEach((s, i) => {
                    if (i < rating) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                    }
                });
            });
        });
        
        // Закрытие модального окна по клику вне его
        document.getElementById('reviewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReviewModal();
            }
        });
    </script>
</body>
</html>