<?php
session_start();
require_once '../../classes/Booking.php';
require_once '../../classes/Hall.php';
require_once '../../classes/User.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$bookingModel = new Booking();
$hallModel = new Hall();
$userModel = new User();

// Фильтры
$status = $_GET['status'] ?? '';
$hall_id = $_GET['hall_id'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$filters = [];
if ($status) $filters['status'] = $status;
if ($hall_id) $filters['hall_id'] = $hall_id;

$bookings = $bookingModel->getAll($filters, $limit, $offset);
$totalBookings = $bookingModel->getCount($filters);
$totalPages = ceil($totalBookings / $limit);

$halls = $hallModel->getAll();
$stats = [
    'total' => $bookingModel->getCount(),
    'new' => $bookingModel->getCount(['status' => 'Новая']),
    'today' => $bookingModel->getCount(['date' => date('Y-m-d')])
];

// Обработка изменения статуса (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $result = $bookingModel->updateStatus($_POST['id'], $_POST['status']);
    echo json_encode(['success' => $result]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Админ-панель - Банкетам.Нет</title>
    <link rel="stylesheet" href="../../assets/css/mobile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <div class="admin-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="color: white;">Админ-панель</h2>
                <span style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px;">
                    <?php echo $_SESSION['user_login']; ?>
                </span>
            </div>
            
            <!-- Статистика -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 20px;">
                <div class="stat-card">
                    <div style="font-size: 12px; opacity: 0.8;">Всего</div>
                    <div style="font-size: 24px; font-weight: bold;"><?php echo $stats['total']; ?></div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 12px; opacity: 0.8;">Новые</div>
                    <div style="font-size: 24px; font-weight: bold;"><?php echo $stats['new']; ?></div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 12px; opacity: 0.8;">Сегодня</div>
                    <div style="font-size: 24px; font-weight: bold;"><?php echo $stats['today']; ?></div>
                </div>
            </div>
        </div>
        
        <div class="content">
            <!-- Фильтры -->
            <div class="filters-bar">
                <select class="filter-select" name="status" onchange="applyFilter('status', this.value)">
                    <option value="">Все статусы</option>
                    <option value="Новая" <?php echo $status === 'Новая' ? 'selected' : ''; ?>>Новая</option>
                    <option value="Банкет назначен" <?php echo $status === 'Банкет назначен' ? 'selected' : ''; ?>>Банкет назначен</option>
                    <option value="Банкет завершен" <?php echo $status === 'Банкет завершен' ? 'selected' : ''; ?>>Банкет завершен</option>
                </select>
                
                <select class="filter-select" name="hall_id" onchange="applyFilter('hall_id', this.value)">
                    <option value="">Все залы</option>
                    <?php foreach ($halls as $hall): ?>
                    <option value="<?php echo $hall['id']; ?>" <?php echo $hall_id == $hall['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($hall['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" class="filter-select" placeholder="Поиск..." id="search-input">
            </div>
            
            <!-- Список заявок -->
            <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Нет заявок</p>
                </div>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                <div class="booking-item" id="booking-<?php echo $booking['id']; ?>">
                    <div class="booking-header">
                        <span class="booking-hall"><?php echo htmlspecialchars($booking['hall_name']); ?></span>
                        <select class="status-badge status-select" 
                                onchange="changeStatus(<?php echo $booking['id']; ?>, this.value)"
                                style="border: none; cursor: pointer;">
                            <option value="Новая" class="status-new" <?php echo $booking['status'] == 'Новая' ? 'selected' : ''; ?>>Новая</option>
                            <option value="Банкет назначен" class="status-confirmed" <?php echo $booking['status'] == 'Банкет назначен' ? 'selected' : ''; ?>>Банкет назначен</option>
                            <option value="Банкет завершен" class="status-completed" <?php echo $booking['status'] == 'Банкет завершен' ? 'selected' : ''; ?>>Банкет завершен</option>
                        </select>
                    </div>
                    
                    <div style="font-size: 14px; color: #666; margin-bottom: 8px;">
                        <i class="far fa-user"></i> <?php echo htmlspecialchars($booking['full_name']); ?> • 
                        <a href="tel:<?php echo $booking['phone']; ?>"><?php echo $booking['phone']; ?></a>
                    </div>
                    
                    <div class="booking-details">
                        <div class="booking-detail">
                            <span class="detail-label">Дата</span>
                            <span class="detail-value"><?php echo date('d.m.Y', strtotime($booking['booking_date'])); ?> <?php echo substr($booking['booking_time'], 0, 5); ?></span>
                        </div>
                        <div class="booking-detail">
                            <span class="detail-label">Гости</span>
                            <span class="detail-value"><?php echo $booking['guests_count']; ?> чел</span>
                        </div>
                        <div class="booking-detail">
                            <span class="detail-label">Оплата</span>
                            <span class="detail-value">
                                <?php 
                                $methods = ['cash' => '💰 Наличные', 'card' => '💳 Карта', 'online' => '🌐 Онлайн'];
                                echo $methods[$booking['payment_method']] ?? $booking['payment_method'];
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if (!empty($booking['comment'])): ?>
                    <div style="margin-top: 8px; padding: 8px; background: var(--light); border-radius: 8px; font-size: 14px;">
                        <i class="far fa-comment"></i> <?php echo nl2br(htmlspecialchars($booking['comment'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                
                <!-- Пагинация -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <span class="page-item" onclick="goToPage(<?php echo $page - 1; ?>)">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <span class="page-item <?php echo $i == $page ? 'active' : ''; ?>" 
                          onclick="goToPage(<?php echo $i; ?>)">
                        <?php echo $i; ?>
                    </span>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <span class="page-item" onclick="goToPage(<?php echo $page + 1; ?>)">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Нижняя навигация админа -->
        <div style="display: flex; justify-content: space-around; padding: 16px; background: white; border-top: 1px solid var(--light);">
            <a href="dashboard.php" style="color: var(--primary);"><i class="fas fa-calendar-check fa-xl"></i></a>
            <a href="halls.php" style="color: var(--gray);"><i class="fas fa-door-open fa-xl"></i></a>
            <a href="users.php" style="color: var(--gray);"><i class="fas fa-users fa-xl"></i></a>
            <a href="../logout.php" style="color: var(--gray);"><i class="fas fa-sign-out-alt fa-xl"></i></a>
        </div>
    </div>
    
    <!-- Уведомление -->
    <div id="notification" class="notification success" style="display: none;">
        <i class="fas fa-check-circle"></i>
        <span>Статус заявки изменен</span>
    </div>
    
    <script>
        function applyFilter(name, value) {
            const url = new URL(window.location.href);
            if (value) {
                url.searchParams.set(name, value);
            } else {
                url.searchParams.delete(name);
            }
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        }
        
        function goToPage(page) {
            const url = new URL(window.location.href);
            url.searchParams.set('page', page);
            window.location.href = url.toString();
        }
        
        function changeStatus(bookingId, status) {
            fetch('dashboard.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ajax=1&id=' + bookingId + '&status=' + encodeURIComponent(status)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notification = document.getElementById('notification');
                    notification.style.display = 'flex';
                    
                    // Обновляем цвет статуса
                    const select = document.querySelector(`#booking-${bookingId} .status-select`);
                    select.className = 'status-badge status-select';
                    if (status === 'Новая') select.classList.add('status-new');
                    else if (status === 'Банкет назначен') select.classList.add('status-confirmed');
                    else if (status === 'Банкет завершен') select.classList.add('status-completed');
                    
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 3000);
                }
            });
        }
        
        // Поиск с debounce
        let searchTimeout;
        document.getElementById('search-input').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const url = new URL(window.location.href);
                if (e.target.value) {
                    url.searchParams.set('search', e.target.value);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            }, 500);
        });
    </script>
    
    <style>
        .status-select {
            appearance: none;
            background: transparent;
            padding: 6px 24px 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 12px;
        }
        .status-new { background: #FFF3CD; color: #856404; }
        .status-confirmed { background: #D4EDDA; color: #155724; }
        .status-completed { background: #E2E3E5; color: #383D41; }
    </style>
</body>
</html>