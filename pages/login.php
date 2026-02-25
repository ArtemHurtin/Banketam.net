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
            <h2 style="margin-bottom: 20px;">Вход в систему</h2>
            
            <?php if ($error): ?>
                <div class="notification error" style="display: flex; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form id="login-form" method="POST" data-validate>
                <div class="form-group">
                    <label class="form-label">Логин</label>
                    <input type="text" class="form-control" name="login" 
                           data-validate="required" 
                           value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Пароль</label>
                    <input type="password" class="form-control" name="password" 
                           data-validate="required" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Войти
                </button>
                
                <p style="text-align: center; margin-top: 20px;">
                    Еще не зарегистрированы? <a href="register.php">Регистрация</a>
                </p>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/validation.js"></script>
</body>
</html>