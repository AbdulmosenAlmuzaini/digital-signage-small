<?php
require 'config.php';
check_auth();

$username = $_SESSION['username'] ?? '';
$example_screen_id = 1;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم - منصة اللافتات الرقمية</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --bg: #050b18;
            --card-bg: #141c2c;
            --accent: #1f2937;
            --btn: #0597d4;
            --btn-hover: #0477a8;
        }
        *{box-sizing:border-box;}
        body{
            margin:0;
            min-height:100vh;
            background:var(--bg);
            font-family:Tahoma,Arial,sans-serif;
            color:#e5e7eb;
        }
        header{
            padding:12px 20px;
            background:#020617;
            display:flex;
            align-items:center;
            justify-content:space-between;
            font-size:14px;
        }
        header .right span{
            margin-left:6px;
        }
        header a{
            color:#38bdf8;
            text-decoration:none;
            margin-right:10px;
        }
        main{
            max-width:1100px;
            margin:30px auto;
            padding:0 16px 40px;
        }
        h1{
            font-size:22px;
            margin:0 0 6px;
        }
        .sub{
            font-size:13px;
            color:#9ca3af;
            margin-bottom:24px;
        }
        .grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
            gap:18px;
        }
        .card{
            background:var(--card-bg);
            border-radius:10px;
            padding:18px 18px 16px;
            box-shadow:0 16px 30px rgba(0,0,0,.35);
        }
        .card h2{
            margin:0 0 10px;
            font-size:18px;
        }
        .card p{
            margin:0 0 14px;
            font-size:13px;
            color:#9ca3af;
        }
        .card a.btn{
            display:inline-block;
            padding:8px 14px;
            border-radius:6px;
            background:var(--btn);
            color:#fff;
            font-size:14px;
            text-decoration:none;
        }
        .card a.btn:hover{
            background:var(--btn-hover);
        }
        .player-box{
            margin-top:26px;
            background:#020617;
            border-radius:10px;
            padding:14px 18px;
            font-size:13px;
            color:#d1d5db;
        }
        .player-box code{
            background:#111827;
            padding:4px 6px;
            border-radius:4px;
            direction:ltr;
            display:inline-block;
            font-size:12px;
        }
    </style>
</head>
<body>
<header>
    <div class="right">
        <span>منصة اللافتات الرقمية</span>
    </div>
    <div class="left">
        <?php if (is_admin()): ?>
            <a href="users.php">المستخدمون</a> |
        <?php endif; ?>
        <span>مرحباً، <?= htmlspecialchars($username,ENT_QUOTES,'UTF-8'); ?></span> |
        <a href="logout.php">تسجيل خروج</a>
    </div>
</header>

<main>
    <h1>لوحة التحكم</h1>
    <div class="sub">اختر المهمة المطلوبة:</div>

    <div class="grid">
        <div class="card">
            <h2>رفع صورة / فيديو</h2>
            <p>إضافة ملفات الوسائط التي ستُعرض على الشاشات (صور أو فيديوهات).</p>
            <a class="btn" href="media_add.php">رفع الوسائط</a>
        </div>

        <div class="card">
            <h2>الشاشات</h2>
            <p>إضافة شاشة جديدة (طولي / عرضي) والحصول على رابط التشغيل لكل شاشة.</p>
            <a class="btn" href="screens.php">إدارة الشاشات</a>
        </div>

        <div class="card">
            <h2>قوائم التشغيل</h2>
            <p>ربط الوسائط بالشاشات وترتيبها في قائمة تشغيل.</p>
            <a class="btn" href="playlist.php">إدارة القوائم</a>
        </div>
    </div>

    <div class="player-box">
        <strong>طريقة العرض على الشاشة</strong>
        <p>افتح الرابط التالي على الشاشة المطلوبة (متصفح فل سكرين / Kiosk):</p>
        <code>http://YOUR-HOST/signage/player.php?screen=<?= $example_screen_id ?></code>
        <p>غيّر رقم <code>screen=<?= $example_screen_id ?></code> حسب رقم الشاشة المسجّل في النظام.</p>
    </div>
</main>
</body>
</html>
