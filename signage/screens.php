<?php
require 'config.php';
check_auth();

// إضافة شاشة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $orientation = $_POST['orientation'] === 'portrait' ? 'portrait' : 'landscape';

    if ($name !== '') {
        $stmt = $pdo->prepare("INSERT INTO screens (name, orientation) VALUES (?, ?)");
        $stmt->execute([$name, $orientation]);
    }
    header("Location: screens.php");
    exit;
}

// حذف شاشة
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM screens WHERE id=?")->execute([$id]);
    header("Location: screens.php");
    exit;
}

$screens = $pdo->query("SELECT * FROM screens ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الشاشات</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root{
            --bg:#050b18;
            --card-bg:#141c2c;
            --btn:#0597d4;
            --btn-hover:#0477a8;
            --danger:#dc2626;
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
            font-size:14px;
        }
        header a{color:#38bdf8;text-decoration:none;margin-left:10px;}
        main{
            max-width:900px;
            margin:26px auto 40px;
            padding:0 16px;
        }
        h1{margin:0 0 16px;font-size:20px;}
        .card{
            background:var(--card-bg);
            padding:18px;
            border-radius:10px;
            box-shadow:0 10px 25px rgba(0,0,0,.4);
            margin-bottom:18px;
        }
        label{font-size:14px;}
        input,select{
            padding:8px 10px;
            border-radius:6px;
            border:1px solid #374151;
            background:#020617;
            color:#e5e7eb;
            font-size:14px;
        }
        button{
            padding:8px 14px;
            border-radius:6px;
            border:none;
            background:var(--btn);
            color:#fff;
            cursor:pointer;
            font-size:14px;
        }
        button:hover{background:var(--btn-hover);}
        table{
            width:100%;
            border-collapse:collapse;
            margin-top:8px;
            font-size:14px;
        }
        th,td{
            border-bottom:1px solid #1f2937;
            padding:8px 6px;
            text-align:center;
        }
        th{color:#9ca3af;font-weight:normal;}
        .badge{
            display:inline-block;
            padding:3px 8px;
            border-radius:999px;
            font-size:12px;
            background:#111827;
        }
        .badge.portrait{background:#0369a1;}
        .badge.landscape{background:#15803d;}
        .btn-danger{
            background:var(--danger);
        }
    </style>
</head>
<body>
<header>
    <a href="dashboard.php">عودة للوحة التحكم</a>
</header>
<main>
    <h1>إدارة الشاشات</h1>

    <div class="card">
        <form method="post" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
            <div>
                <label>الاسم:</label><br>
                <input type="text" name="name" placeholder="مثال: شاشة كلية العلوم" required>
            </div>
            <div>
                <label>الاتجاه:</label><br>
                <select name="orientation">
                    <option value="landscape">عرضي</option>
                    <option value="portrait">طولي</option>
                </select>
            </div>
            <div>
                <button type="submit">حفظ</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2 style="margin-top:0;font-size:16px;">الشاشات المسجلة</h2>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>الاتجاه</th>
                <th>رابط العرض</th>
                <th>إجراءات</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($screens as $s): ?>
                <tr>
                    <td><?= $s['id'] ?></td>
                    <td><?= htmlspecialchars($s['name'],ENT_QUOTES,'UTF-8') ?></td>
                    <td>
                        <span class="badge <?= $s['orientation'] ?>">
                            <?= $s['orientation'] === 'portrait' ? 'طولي' : 'عرضي' ?>
                        </span>
                    </td>
                    <td style="direction:ltr;">
                        <?php
                            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                            $host = $_SERVER['HTTP_HOST'];
                            $path = dirname($_SERVER['PHP_SELF']);
                            if ($path == '/' || $path == '\\') $path = '';
                            $full_url = $protocol . $host . $path . "/" . $s['id'];
                        ?>
                        <a href="<?= $full_url ?>" target="_blank" style="color:#38bdf8; text-decoration:none; word-break: break-all;">
                            <?= $full_url ?>
                        </a>
                    </td>
                    <td>
                        <form method="get" onsubmit="return confirm('حذف الشاشة مع قوائمها؟');">
                            <input type="hidden" name="delete" value="<?= $s['id'] ?>">
                            <button class="btn-danger" type="submit">حذف</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
