<?php
require 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'الرجاء إدخال اسم المستخدم وكلمة المرور.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $user['password'] === md5($password)) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['role']      = $user['role'];

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'بيانات الدخول غير صحيحة.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول - منصة اللافتات الرقمية</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --bg: #050b18;
            --card-bg: #141c2c;
            --btn: #0597d4;
            --btn-hover: #0477a8;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            font-family: Tahoma, Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .layout {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
        }

        .logo-top img {
            max-width: 260px;
            height: auto;
            display: block;
        }

        .card {
            width: 100%;
            max-width: 430px;
            background: var(--card-bg);
            border-radius: 10px;
            padding: 32px 28px 28px;
            box-shadow: 0 20px 40px rgba(0,0,0,.45);
            text-align: center;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 22px;
        }

        .subtitle {
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 20px;
        }

        .field {
            text-align: right;
            margin-bottom: 14px;
        }

        .field label {
            display: block;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .field input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #374151;
            background: #020617;
            color: #e5e7eb;
            font-size: 14px;
        }

        .field input:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 1px rgba(14,165,233,.4);
        }

        .error {
            background: rgba(239,68,68,.1);
            border: 1px solid #ef4444;
            color: #fecaca;
            font-size: 13px;
            padding: 8px 10px;
            border-radius: 6px;
            margin-bottom: 14px;
            text-align: right;
        }

        button {
            width: 100%;
            padding: 11px;
            border-radius: 6px;
            border: none;
            background: var(--btn);
            color: #fff;
            font-size: 15px;
            cursor: pointer;
        }

        button:hover {
            background: var(--btn-hover);
        }

        @media (max-width: 480px) {
            .card {
                margin: 16px;
                padding: 24px 18px 20px;
            }
            .logo-top img {
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
<div class="layout">
    <div class="logo-top">
        <!-- ضع شعارك هنا -->
        <img src="logo-iu.png" alt="شعار الكلية">
    </div>

    <div class="card">
        <h1>تسجيل الدخول</h1>
        <div class="subtitle">منصة اللافتات الرقمية</div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="field">
                <label for="username">اسم المستخدم</label>
                <input type="text" id="username" name="username" autocomplete="username">
            </div>

            <div class="field">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" autocomplete="current-password">
            </div>

            <button type="submit">دخول</button>
        </form>
    </div>
</div>
</body>
</html>
