<?php
session_start();
require_once '../classes/User.php';

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    $result = $user->register([
        'login' => $_POST['login'],
        'password' => $_POST['password'],
        'full_name' => $_POST['full_name'],
        'phone' => $_POST['phone'],
        'email' => $_POST['email']
    ]);
    
    if ($result['success']) {
        $success = 'Регистрация успешна! Теперь вы можете войти.';
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
    <title>Регистрация - Банкетам.Нет</title>
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
            <h2 style="margin-bottom: 20px;">Регистрация</h2>
            
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
                <a href="login.php" class="btn btn-primary">Войти</a>
            <?php else: ?>
                <form id="register-form" method="POST" data-validate>
                    <div class="form-group">
                        <label class="form-label">Логин</label>
                        <input type="text" class="form-control" name="login" 
                               data-validate="required minlength:6 login" 
                               value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>" required>
                        <div class="form-text">Только латинские буквы и цифры, минимум 6 символов</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Пароль</label>
                        <input type="password" class="form-control" name="password" 
                               data-validate="required minlength:8 password" required>
                        <div class="form-text">Минимум 8 символов</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">ФИО</label>
                        <input type="text" class="form-control" name="full_name" 
                               data-validate="required" 
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Телефон</label>
                        <input type="tel" class="form-control" name="phone" 
                               data-validate="required phone" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                               placeholder="+7 (999) 123-45-67" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" 
                               data-validate="required email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Зарегистрироваться
                    </button>
                    
                    <p style="text-align: center; margin-top: 20px;">
                        Уже есть аккаунт? <a href="login.php">Войти</a>
                    </p>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/validation.js"></script>
    <script>
        // Маска для телефона
        document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 1) {
                    value = '+7' + value;
                } else if (value.length <= 4) {
                    value = '+7 (' + value.slice(1, 4);
                } else if (value.length <= 7) {
                    value = '+7 (' + value.slice(1, 4) + ') ' + value.slice(4, 7);
                } else if (value.length <= 9) {
                    value = '+7 (' + value.slice(1, 4) + ') ' + value.slice(4, 7) + '-' + value.slice(7, 9);
                } else {
                    value = '+7 (' + value.slice(1, 4) + ') ' + value.slice(4, 7) + '-' + value.slice(7, 9) + '-' + value.slice(9, 11);
                }
            }
            e.target.value = value;
        });
    </script>
</body>
</html>