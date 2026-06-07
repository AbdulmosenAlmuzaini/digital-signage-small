<?php
require 'config.php';
check_auth();

$message = '';

// حذف الوسائط
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("SELECT filename FROM media WHERE id=?");
    $stmt->execute([$id]);
    $file = $stmt->fetchColumn();
    
    if ($file) {
        $path = __DIR__ . '/uploads/' . $file;
        if (file_exists($path)) {
            @unlink($path);
        }
        $pdo->prepare("DELETE FROM media WHERE id=?")->execute([$id]);
    }
    
    header("Location: media_add.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $message = 'حدث خطأ في رفع الملف.';
    } else {
        $title = trim($_POST['title'] ?? '');
        if ($title === '') $title = $_FILES['file']['name'];

        $tmp = $_FILES['file']['tmp_name'];
        $name = basename($_FILES['file']['name']);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        $type = 'image';
        $allowed_img  = ['jpg','jpeg','png','gif','webp'];
        $allowed_vid  = ['mp4','webm','ogg'];

        if (in_array($ext, $allowed_vid)) {
            $type = 'video';
        } elseif (!in_array($ext, $allowed_img)) {
            $message = 'نوع الملف غير مدعوم.';
        }

        if ($message === '') {
            $newName = uniqid('m_') . '.' . $ext;
            $dest = __DIR__ . '/uploads/' . $newName;
            if (!move_uploaded_file($tmp, $dest)) {
                $message = 'تعذّر نقل الملف.';
            } else {
                $duration = (int)($_POST['duration'] ?? 10);
                if ($duration <= 0) $duration = 10;

                $stmt = $pdo->prepare("INSERT INTO media (title, filename, type, duration) VALUES (?,?,?,?)");
                $stmt->execute([$title, $newName, $type, $duration]);
                $message = 'تم حفظ الوسيط بنجاح.';
            }
        }
    }
}

$media = $pdo->query("SELECT * FROM media ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>رفع الوسائط</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root{
            --bg:#050b18;
            --card-bg:#141c2c;
            --btn:#0597d4;
            --btn-hover:#0477a8;
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
        input[type=text],input[type=number],input[type=file]{
            width:100%;
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
        .msg{
            margin-bottom:10px;
            font-size:13px;
            color:#bfdbfe;
        }
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
        .btn-danger{background:#dc2626; padding:4px 8px; border-radius:4px; text-decoration:none; color:#fff; display:inline-flex; align-items:center; vertical-align:middle; font-size: 13px;}
        .btn-danger:hover{background:#b91c1c;}
        .btn-view{background:#10b981; padding:4px 8px; border-radius:4px; text-decoration:none; color:#fff; display:inline-flex; align-items:center; vertical-align:middle; font-size: 13px; margin-right: 5px;}
        .btn-view:hover{background:#059669;}
        /* Modal */
        .modal {
            display: none; position: fixed; z-index: 1000; left: 0; top: 0;
            width: 100%; height: 100%; overflow: auto;
            background-color: rgba(0,0,0,0.8); backdrop-filter: blur(5px);
        }
        .modal-content {
            margin: auto; display: block; max-width: 90%; max-height: 85vh;
            border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
        }
        .close-modal {
            position: absolute; top: 15px; right: 25px; color: #f1f1f1;
            font-size: 35px; font-weight: bold; cursor: pointer; z-index: 1001;
        }
        .close-modal:hover { color: #bbb; text-decoration: none; }
    </style>
</head>
<body>
<header>
    <a href="dashboard.php">عودة للوحة التحكم</a>
</header>
<main>
    <h1>رفع الوسائط (صور / فيديو)</h1>

    <div class="card">
        <?php if ($message): ?>
            <div class="msg"><?= htmlspecialchars($message,ENT_QUOTES,'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div style="margin-bottom:10px;">
                <label>العنوان (اختياري):</label><br>
                <input type="text" name="title">
            </div>
            <div style="margin-bottom:10px;">
                <label>مدة العرض (بالثواني) - للفيديو سيتم استخراجها تلقائياً:</label><br>
                <input type="number" name="duration" value="10" min="1" id="durationInput">
            </div>
            <div style="margin-bottom:10px;">
                <label>الملف:</label><br>
                <input type="file" name="file" required id="fileInput">
            </div>
            <button type="submit">رفع</button>
        </form>
    </div>

    <div class="card">
        <h2 style="margin-top:0;font-size:16px;">الوسائط المسجّلة</h2>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>العنوان</th>
                <th>النوع</th>
                <th>الملف</th>
                <th>المدة (ث)</th>
                <th>إجراءات</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($media as $m): ?>
                <tr>
                    <td><?= $m['id'] ?></td>
                    <td><?= htmlspecialchars($m['title'],ENT_QUOTES,'UTF-8') ?></td>
                    <td><?= $m['type']==='video'?'فيديو':'صورة' ?></td>
                    <td><?= htmlspecialchars($m['filename'],ENT_QUOTES,'UTF-8') ?></td>
                    <td><?= $m['duration'] ?></td>
                    <td>
                        <a class="btn-danger" href="media_add.php?delete=<?= $m['id'] ?>" onclick="return confirm('هل أنت متأكد من حذف هذا الوسيط من النظام نهائياً؟ سيتم حذفه أيضاً من جميع الشاشات المرتبط بها.');">حذف</a>
                        <a class="btn-view" href="javascript:void(0)" onclick="openModal('uploads/<?= htmlspecialchars($m['filename'],ENT_QUOTES,'UTF-8') ?>', '<?= $m['type'] ?>')" title="عرض">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Media Modal Overlay -->
<div id="mediaModal" class="modal">
    <span class="close-modal" onclick="closeModal()">&times;</span>
    <div id="modalBody" style="width:100%; height:100%;"></div>
</div>

<script>
document.getElementById('fileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const durationInput = document.getElementById('durationInput');
    
    if (file.type.startsWith('video/')) {
        const vid = document.createElement('video');
        vid.preload = 'metadata';
        vid.onloadedmetadata = function() {
            window.URL.revokeObjectURL(vid.src);
            const duration = Math.ceil(vid.duration);
            durationInput.value = duration;
            durationInput.setAttribute('readonly', 'readonly');
            durationInput.style.backgroundColor = '#1f2937';
        }
        vid.src = URL.createObjectURL(file);
    } else {
        durationInput.removeAttribute('readonly');
        durationInput.style.backgroundColor = '#020617';
    }
});

function openModal(src, type) {
    const modal = document.getElementById('mediaModal');
    const modalBody = document.getElementById('modalBody');
    modalBody.innerHTML = ''; 
    
    if (type === 'image') {
        const img = document.createElement('img');
        img.src = src;
        img.className = 'modal-content';
        modalBody.appendChild(img);
    } else if (type === 'video') {
        const vid = document.createElement('video');
        vid.src = src;
        vid.controls = true;
        vid.autoplay = true;
        vid.className = 'modal-content';
        modalBody.appendChild(vid);
    }
    modal.style.display = "block";
}

function closeModal() {
    const modal = document.getElementById('mediaModal');
    const modalBody = document.getElementById('modalBody');
    modal.style.display = "none";
    modalBody.innerHTML = ''; 
}

window.onclick = function(event) {
    const mediaModal = document.getElementById('mediaModal');
    if (event.target == mediaModal) {
        closeModal();
    }
}
</script>
</body>
</html>
