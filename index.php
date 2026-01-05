<?php
/**
 * Login Page
 * Customer Tracking & Billing Management System
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect(BASE_URL . '/pages/dashboard.php');
}

$error = '';
$success = '';

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = processLogin();

    if (isset($result['success'])) {
        redirect(BASE_URL . '/pages/dashboard.php');
    } elseif (isset($result['error'])) {
        $error = $result['error'];
    }
}

// Get flash message
$flash = getFlashMessage();
if ($flash) {
    if ($flash['type'] === 'error') {
        $error = $flash['message'];
    } else {
        $success = $flash['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-dark: #0b5ed7;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .login-header {
            background: var(--bg-gradient);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .login-header p {
            opacity: 0.9;
            margin: 0;
            font-size: 14px;
        }

        .login-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .login-icon i {
            font-size: 36px;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-floating .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            height: 55px;
            padding: 15px 15px 15px 45px;
        }

        .form-floating .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .form-floating label {
            padding-left: 45px;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 5;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            background: var(--bg-gradient);
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .alert-success {
            background: #d1fae5;
            color: #059669;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 5;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 15px;
            }

            .login-header {
                padding: 30px 20px;
            }

            .login-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h1><?= APP_NAME ?></h1>
                <p>Customer Tracking & Billing System</p>
            </div>

            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" autocomplete="off">
                    <div class="form-floating position-relative">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text"
                               class="form-control"
                               id="username"
                               name="username"
                               placeholder="Username"
                               required
                               autofocus
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                        <label for="username">Username</label>
                    </div>

                    <div class="form-floating position-relative">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               placeholder="Password"
                               required>
                        <label for="password">Password</label>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-white mt-4 mb-0">
            <small>&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</small>
        </p>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
