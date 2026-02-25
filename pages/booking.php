<?php
session_start();
require_once '../classes/Halls.php';
require_once '../classes/Booking.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$hallModel = new Hall();
$bookingModel = new Booking();

$selectedHall = null;
if (isset($_GET['hall_id'])) {
    $selectedHall = $hallModel->getById($_GET['hall_id']);
}

$halls = $hallModel->getAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Преобразуем дату из ДД.ММ.ГГГГ в ГГГГ-ММ-ДД
    $dateParts = explode('.', $_POST['booking_date']);
    $bookingDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
    
    $result = $bookingModel->create([
        'user_id' => $_SESSION['user_id'],
        'hall_id' => $_POST['hall_id'],
        'booking_date' => $bookingDate,
        'booking_time' => $_POST['booking_time'],
        'guests_count' => $_POST['guests_count'],
        'payment_method' => $_POST['payment_method'],
        'comment' => $_POST['comment'] ?? ''
    ]);
    
    if ($result['success']) {
        $success = 'Заявка успешно создана! Ожидайте подтверждения администратора.';
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новая заявка - Банкетам.Нет</title>
    <!-- Единый файл стилей -->
    <link rel="stylesheet" href="../css/style.css">
    <!-- Иконки Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header" style="margin-bottom: 20px;">
            <h1>Новая заявка на бронирование</h1>
            <nav class="nav">
                <a href="../index.php">Главная</a>
                <a href="profile.php">Личный кабинет</a>
                <a href="logout.php">Выход</a>
            </nav>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <a href="profile.php" class="btn btn-primary">Перейти к моим заявкам</a>
        <?php else: ?>
            <div class="form-wrapper">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Выберите помещение</label>
                        <select class="form-control" name="hall_id" required>
                            <option value="">-- Выберите зал --</option>
                            <?php foreach ($halls as $hall): ?>
                                <option value="<?php echo $hall['id']; ?>" 
                                    <?php echo ($selectedHall && $selectedHall['id'] == $hall['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($hall['name']); ?> (до <?php echo $hall['capacity']; ?> чел.)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Дата мероприятия</label>
                        <input type="text" class="form-control" name="booking_date" 
                               placeholder="ДД.ММ.ГГГГ" required 
                               pattern="\d{2}\.\d{2}\.\d{4}" 
                               value="<?php echo date('d.m.Y'); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Время начала</label>
                        <input type="time" class="form-control" name="booking_time" required value="18:00">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Количество гостей</label>
                        <input type="number" class="form-control" name="guests_count" 
                               min="1" max="200" required value="50">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Способ оплаты</label>
                        <select class="form-control" name="payment_method" required>
                            <option value="">-- Выберите способ --</option>
                            <option value="cash">Наличные</option>
                            <option value="card">Банковская карта</option>
                            <option value="online">Онлайн-оплата</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Комментарий (необязательно)</label>
                        <textarea class="form-control" name="comment" rows="3" 
                                  placeholder="Дополнительные пожелания..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-check"></i> Отправить заявку
                    </button>
                </form>
            </div>

            <div style="margin-top: 20px; text-align: center;">
                <a href="javascript:history.back()" class="btn btn-outline">← Назад</a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>© 2026 Банкетам.Нет</p>
    </footer>

    <script>
        // Маска для даты ДД.ММ.ГГГГ
        document.querySelector('input[name="booking_date"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2 && value.length < 4) {
                value = value.slice(0, 2) + '.' + value.slice(2);
            } else if (value.length >= 4) {
                value = value.slice(0, 2) + '.' + value.slice(2, 4) + '.' + value.slice(4, 8);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>