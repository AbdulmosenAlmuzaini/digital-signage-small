<?php
require 'config.php';
check_auth();

if (!is_admin()) {
    // رسالة بسيطة إذا حاول مستخدم عادي الدخول مباشرة
    echo "<!DOCTYPE html><html lang='ar' dir='rtl'><head><meta charset='UTF-8'><title>صلاحية غير كافية</title></head><body style='background:#050b18;color:#fff;font-family:Tahoma;display:flex;align-items:center;justify-content:center;min-height:100vh;'><div>صلاحية غير كافية، هذه الصفحة متاحة للمشرفين فقط. <a href='dashboard.php' style='color:#38bdf8;'>عودة للوحة التحكم</a></div></body></html>";
    exit;
}

$error = '';
$success = '';

// إضافة مستخدم جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';

    if ($username === '' || $password === '') {
        $error = 'الرجاء إدخال اسم المستخدم وكلمة المرور.';
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{6,}$/u', $password)) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل وتحتوي على أرقام وحروف.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username,password,role) VALUES (?,?,?)");
            $stmt->execute([$username, md5($password), $role]);
            $success = 'تم إضافة المستخدم بنجاح.';
        } catch (Exception $e) {
            $error = 'اسم المستخدم مستخدم مسبقاً.';
        }
    }
}

// حذف مستخدم
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== ($_SESSION['user_id'] ?? 0)) {
        $pdo->prepare("DELETE FROM users WHERE id=? AND username!='admin'")->execute([$id]);
        $success = 'تم حذف المستخدم.';
    } else {
        $error = 'لا يمكن حذف حسابك الحالي.';
    }
}

// تغيير كلمة مرور
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'password') {
    $id = (int)($_POST['id'] ?? 0);
    $newpass = trim($_POST['newpass'] ?? '');

    if ($newpass === '') {
        $error = 'الرجاء إدخال كلمة مرور جديدة.';
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{6,}$/u', $newpass)) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل وتحتوي على أرقام وحروف.';
    } else {
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([md5($newpass), $id]);
        $success = 'تم تحديث كلمة المرور.';
    }
}

$users = $pdo->query("SELECT id,username,role FROM users ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة المستخدمين</title>
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
            padding:6px;
            text-align:center;
        }
        th{color:#9ca3af;font-weight:normal;}
        .msg{font-size:13px;margin-bottom:8px;}
        .msg.error{color:#fecaca;}
        .msg.success{color:#bbf7d0;}
        .btn-danger{background:var(--danger);}
    </style>
</head>
<body>
<header>
    <a href="dashboard.php">عودة للوحة التحكم</a>
</header>
<main>
    <h1>إدارة المستخدمين</h1>

    <div class="card">
        <?php if ($error): ?>
            <div class="msg error"><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="msg success"><?= htmlspecialchars($success,ENT_QUOTES,'UTF-8') ?></div>
        <?php endif; ?>

        <h2 style="margin-top:0;font-size:16px;">إضافة مستخدم جديد</h2>
        <form method="post" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
            <input type="hidden" name="action" value="add">
            <div>
                <label>اسم المستخدم:</label><br>
                <input type="text" name="username" required>
            </div>
            <div>
                <label>كلمة المرور:</label><br>
                <input type="password" name="password" required>
            </div>
            <div>
                <label>الدور:</label><br>
                <select name="role">
                    <option value="user">مستخدم عادي</option>
                    <option value="admin">مشرف</option>
                </select>
            </div>
            <div>
                <button type="submit">إضافة</button>
            </div>
        </form>
        <p style="font-size:12px;color:#9ca3af;margin-top:8px;">
            * كلمة المرور يجب أن تحتوي على حروف وأرقام وطول 6 أحرف على الأقل.
        </p>
    </div>

    <div class="card">
        <h2 style="margin-top:0;font-size:16px;">قائمة المستخدمين</h2>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>اسم المستخدم</th>
                <th>الدور</th>
                <th>تغيير كلمة المرور</th>
                <th>حذف</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username'],ENT_QUOTES,'UTF-8') ?></td>
                    <td><?= $u['role'] === 'admin' ? 'مشرف' : 'مستخدم' ?></td>
                    <td>
                        <form method="post" style="display:flex;gap:6px;justify-content:center;">
                            <input type="hidden" name="action" value="password">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <input type="password" name="newpass" placeholder="كلمة مرور جديدة">
                            <button type="submit">تحديث</button>
                        </form>
                    </td>
                    <td>
                        <?php if ($u['username'] !== 'admin'): ?>
                            <a class="btn-danger" href="users.php?delete=<?= $u['id'] ?>"
                               onclick="return confirm('حذف هذا المستخدم؟');">حذف</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
