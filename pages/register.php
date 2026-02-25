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
    <!-- Подключаем единый стиль -->
    <link rel="stylesheet" href="../css/style.css">
    <!-- Иконки Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <h2>Регистрация</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
                <a href="login.php" class="btn btn-primary btn-block">Войти</a>
            <?php else: ?>
                <form method="POST" id="register-form">
                    <div class="form-group">
                        <label class="form-label">Логин</label>
                        <input type="text" class="form-control" name="login" 
                               value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>" 
                               required minlength="6" pattern="[a-zA-Z0-9]+">
                        <small class="form-text">Только латинские буквы и цифры, минимум 6 символов</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Пароль</label>
                        <input type="password" class="form-control" name="password" 
                               required minlength="8">
                        <small class="form-text">Минимум 8 символов</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">ФИО</label>
                        <input type="text" class="form-control" name="full_name" 
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Телефон</label>
                        <input type="tel" class="form-control" name="phone" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                               placeholder="+7 (999) 123-45-67" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i> Зарегистрироваться
                    </button>
                    
                    <p class="text-center mt-3">
                        Уже есть аккаунт? <a href="login.php">Войти</a>
                    </p>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Скрипт для маски телефона (можно вынести в отдельный файл) -->
    <script>
        document.querySelector('input[name="phone"]')?.addEventListener('input', function(e) {
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