<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['admin'])) {

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $login = trim($_POST['login'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($login === 'Admin26' && $password === 'Demo20') {
            $_SESSION['admin'] = true;
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Неверный логин или пароль администратора';
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Вход администратора</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <div class="form-wrapper">
            <h2>Вход администратора</h2>

            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label>Логин</label>
                    <input type="text" name="login" required>
                </div>
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" name="password" required>
                </div>
                <button class="btn">Войти</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if (!empty($_POST['status']) && !empty($_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['id']]);
}

$requests = $pdo->query("
    SELECT r.*, u.full_name 
    FROM requests r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #DAA520; text-align: center; }
        select { padding: 5px; }
        .btn { padding: 6px 12px; background: #DAA520; border: none; cursor: pointer; color: #fff; }
        .container { max-width: 1200px; margin: 30px auto; }
        h2 { color: #DC143C; }
    </style>
</head>
<body>
<div class="container">
    <h2>Панель администратора</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Зал</th>
            <th>Дата</th>
            <th>Оплата</th>
            <th>Статус</th>
            <th>Изменить</th>
        </tr>

        <?php foreach ($requests as $r): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['full_name']) ?></td>
            <td><?= htmlspecialchars($r['room']) ?></td>
            <td><?= htmlspecialchars($r['start_date']) ?></td>
            <td><?= htmlspecialchars($r['payment_method']) ?></td>
            <td><?= htmlspecialchars($r['status']) ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <select name="status">
                        <option <?= $r['status']=='Новая'?'selected':'' ?>>Новая</option>
                        <option <?= $r['status']=='Банкет назначен'?'selected':'' ?>>Банкет назначен</option>
                        <option <?= $r['status']=='Банкет завершен'?'selected':'' ?>>Банкет завершен</option>
                    </select>
                    <button class="btn">OK</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>