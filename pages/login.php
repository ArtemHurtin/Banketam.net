<?php
session_start();
require_once '../classes/User.php';

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    $result = $user->login($_POST['login'], $_POST['password']);
    
    if ($result['success']) {
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['user_login'] = $result['user']['login'];
        $_SESSION['user_role'] = $result['user']['role'];
        
        if ($result['user']['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: profile.php');
        }
        exit;
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
    <title>Вход - Банкетам.Нет</title>
    <!-- Подключаем единый стиль -->
    <link rel="stylesheet" href="../css/style.css">
    <!-- Иконки Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Можно оставить app-container, если он используется в style.css, либо заменить на обычную обёртку -->
    <div class="container">
        <div class="form-wrapper">
            <h2>Вход в систему</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Логин</label>
                    <input type="text" class="form-control" name="login" 
                           value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Пароль</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Войти
                </button>
                
                <p class="text-center mt-3">
                    Еще не зарегистрированы? <a href="register.php">Регистрация</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>