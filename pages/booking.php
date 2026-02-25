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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Новая заявка - Банкетам.Нет</title>
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
            <h2 style="margin-bottom: 20px;">Новая заявка</h2>
            
            <?php if ($error): ?>
                <div class="notification error" style="display: flex; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="notification success" style="display: flex; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                </div>
                <a href="profile.php" class="btn btn-primary">Мои заявки</a>
            <?php else: ?>
                <form id="booking-form" method="POST" data-validate>
                    <!-- Выбор зала (выпадающий список) -->
                    <div class="form-group">
                        <label class="form-label">Выберите помещение</label>
                        <select class="form-control" name="hall_id" required data-validate="required">
                            <option value="">-- Выберите зал --</option>
                            <?php foreach ($halls as $hall): ?>
                            <option value="<?php echo $hall['id']; ?>" 
                                <?php echo ($selectedHall && $selectedHall['id'] == $hall['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($hall['name']); ?> (до <?php echo $hall['capacity']; ?> чел.)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Дата (формат ДД.ММ.ГГГГ) -->
                    <div class="form-group">
                        <label class="form-label">Дата мероприятия</label>
                        <input type="text" class="form-control" name="booking_date" 
                               placeholder="ДД.ММ.ГГГГ" required data-validate="required"
                               pattern="\d{2}\.\d{2}\.\d{4}" 
                               value="<?php echo date('d.m.Y'); ?>">
                    </div>
                    
                    <!-- Время -->
                    <div class="form-group">
                        <label class="form-label">Время начала</label>
                        <input type="time" class="form-control" name="booking_time" 
                               required data-validate="required" value="18:00">
                    </div>
                    
                    <!-- Количество гостей -->
                    <div class="form-group">
                        <label class="form-label">Количество гостей</label>
                        <input type="number" class="form-control" name="guests_count" 
                               min="1" max="200" required data-validate="required" value="50">
                    </div>
                    
                    <!-- Способ оплаты (выпадающий список) -->
                    <div class="form-group">
                        <label class="form-label">Способ оплаты</label>
                        <select class="form-control" name="payment_method" required data-validate="required">
                            <option value="">-- Выберите способ --</option>
                            <option value="cash">Наличные</option>
                            <option value="card">Банковская карта</option>
                            <option value="online">Онлайн-оплата</option>
                        </select>
                    </div>
                    
                    <!-- Комментарий -->
                    <div class="form-group">
                        <label class="form-label">Комментарий (необязательно)</label>
                        <textarea class="form-control" name="comment" rows="3" 
                                  placeholder="Дополнительные пожелания..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Отправить заявку
                    </button>
                    
                    <button type="button" class="btn btn-outline" style="margin-top: 8px;" onclick="history.back()">
                        <i class="fas fa-arrow-left"></i> Назад
                    </button>
                </form>
                
                <!-- Подсказки по заполнению -->
                <div style="margin-top: 24px; padding: 16px; background: var(--light); border-radius: 12px;">
                    <h4 style="margin-bottom: 8px;"><i class="fas fa-info-circle" style="color: var(--primary);"></i> Подсказка</h4>
                    <p style="font-size: 14px; color: #666;">Дату необходимо вводить в формате ДД.ММ.ГГГГ. Например: 25.12.2025</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/validation.js"></script>
    <script>
        // Автоматическая маска для даты
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