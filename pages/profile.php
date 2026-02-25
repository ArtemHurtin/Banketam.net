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

// Подсчет статистики
$totalBookings = count($bookings);
$completedBookings = count(array_filter($bookings, fn($b) => $b['status'] === 'Банкет завершен'));
$reviewsCount = count(array_filter($bookings, fn($b) => !empty($b['review_id'])));

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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - Банкетам.Нет</title>
    <!-- Подключаем единый стиль -->
    <link rel="stylesheet" href="../css/style.css">
    <!-- Иконки Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header" style="margin-bottom: 20px;">
            <h1>Личный кабинет</h1>
            <nav class="nav">
                <a href="../index.php">Главная</a>
                <a href="booking.php">Новая заявка</a>
                <a href="logout.php">Выход</a>
            </nav>
        </header>

        <?php if (isset($_GET['review_added'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Отзыв успешно добавлен!
            </div>
        <?php endif; ?>

        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user-circle fa-4x"></i>
            </div>
            <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p><?php echo htmlspecialchars($user['phone']); ?> • <?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalBookings; ?></div>
                <div class="stat-label">Всего заявок</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $completedBookings; ?></div>
                <div class="stat-label">Завершено</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $reviewsCount; ?></div>
                <div class="stat-label">Отзывов</div>
            </div>
        </div>

        <h2>Мои заявки</h2>

        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times fa-3x"></i>
                <p>У вас пока нет заявок</p>
                <a href="booking.php" class="btn btn-primary">Создать заявку</a>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <h3><?php echo htmlspecialchars($booking['hall_name']); ?></h3>
                        <span class="status-badge status-<?php 
                            echo $booking['status'] == 'Новая' ? 'new' : 
                                ($booking['status'] == 'Банкет назначен' ? 'confirmed' : 'completed'); 
                        ?>"><?php echo $booking['status']; ?></span>
                    </div>
                    <div class="booking-details">
                        <div class="detail-item">
                            <i class="far fa-calendar-alt"></i> <?php echo date('d.m.Y', strtotime($booking['booking_date'])); ?> в <?php echo substr($booking['booking_time'], 0, 5); ?>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i> <?php echo $booking['guests_count']; ?> чел.
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-credit-card"></i> 
                            <?php 
                            $methods = ['cash' => 'Наличные', 'card' => 'Карта', 'online' => 'Онлайн'];
                            echo $methods[$booking['payment_method']] ?? $booking['payment_method'];
                            ?>
                        </div>
                    </div>

                    <?php if ($booking['status'] == 'Банкет завершен' && empty($booking['review_id'])): ?>
                        <button class="btn btn-outline btn-sm" onclick="openReviewModal(<?php echo $booking['id']; ?>)">
                            <i class="far fa-star"></i> Оставить отзыв
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($booking['review_id'])): ?>
                        <div class="review">
                            <div class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?php echo $i <= $booking['review_rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($booking['review_comment'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Модальное окно отзыва -->
    <div id="reviewModal" class="modal-overlay" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Оставить отзыв</h3>
                <button class="modal-close" onclick="closeReviewModal()">&times;</button>
            </div>
            <form method="POST" class="modal-body">
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
                    <textarea name="comment" class="form-control" rows="4"></textarea>
                </div>
                <button type="submit" name="add_review" class="btn btn-primary">Отправить отзыв</button>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>© 2026 Банкетам.Нет</p>
    </footer>

    <script>
        function openReviewModal(bookingId) {
            document.getElementById('review-booking-id').value = bookingId;
            document.getElementById('reviewModal').style.display = 'flex';
        }
        function closeReviewModal() {
            document.getElementById('reviewModal').style.display = 'none';
        }

        // Звёзды рейтинга
        document.querySelectorAll('.rating-stars i').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                document.getElementById('rating-value').value = rating;
                document.querySelectorAll('.rating-stars i').forEach((s, i) => {
                    s.className = i < rating ? 'fas fa-star' : 'far fa-star';
                });
            });
        });
    </script>
</body>
</html>