<?php
require 'config.php';
check_auth();

$screens = $pdo->query("SELECT * FROM screens ORDER BY id ASC")->fetchAll();
$media   = $pdo->query("SELECT * FROM media ORDER BY id DESC")->fetchAll();

$screen_id = isset($_GET['screen']) ? (int)$_GET['screen'] : ( ($screens[0]['id'] ?? 0) );

// إضافة عنصر للقائمة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['screen_id'], $_POST['media_id'])) {
    $screen_id = (int)$_POST['screen_id'];
    $media_id  = (int)$_POST['media_id'];

    // نحسب الترتيب التالي
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order),0)+1 AS next_order FROM playlists WHERE screen_id=?");
    $stmt->execute([$screen_id]);
    $next = (int)$stmt->fetch()['next_order'];

    $stmt = $pdo->prepare("INSERT INTO playlists (screen_id, media_id, sort_order) VALUES (?,?,?)");
    $stmt->execute([$screen_id, $media_id, $next]);

    header("Location: playlist.php?screen=".$screen_id);
    exit;
}

// حذف عنصر
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM playlists WHERE id=?")->execute([$id]);
    header("Location: playlist.php?screen=".$screen_id);
    exit;
}

// جلب القائمة الحالية
$playlist = [];
if ($screen_id) {
    $stmt = $pdo->prepare("
        SELECT p.id, p.sort_order, m.title, m.type, m.filename, m.duration
        FROM playlists p
        JOIN media m ON m.id = p.media_id
        WHERE p.screen_id = ?
        ORDER BY p.sort_order ASC, p.id ASC
    ");
    $stmt->execute([$screen_id]);
    $playlist = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة قوائم التشغيل</title>
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
            max-width:1000px;
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
        select,input{
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
        .btn-danger{background:var(--danger); padding:4px 8px; border-radius:4px; text-decoration:none; color:#fff;}
        .btn-view{background:#10b981; padding:4px 8px; border-radius:4px; text-decoration:none; color:#fff; display:inline-flex; align-items:center; vertical-align:middle;}
        .btn-view:hover{background:#059669;}
        .btn-danger{display:inline-flex; align-items:center; vertical-align:middle;}
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
        .confirm-modal { background: var(--card-bg); padding: 25px; width: 350px; text-align: center; color: #fff; }
        .confirm-modal h3 { margin-top: 0; font-size: 18px; }
        .modal-actions { margin-top: 20px; display: flex; justify-content: center; gap: 10px; }
        .btn-cancel { background: #4b5563; padding: 8px 16px; border-radius: 6px; color: #fff; cursor: pointer; border: none; font-size: 14px; }
        .btn-cancel:hover { background: #374151; }
        button.btn-danger { padding: 8px 16px; font-size: 14px; border: none; cursor: pointer; }
    </style>
</head>
<body>
<header>
    <a href="dashboard.php">عودة للوحة التحكم</a>
</header>
<main>
    <h1>قوائم التشغيل</h1>

    <div class="card">
        <form method="get" style="margin-bottom:14px;">
            <label>اختر الشاشة:</label>
            <select name="screen" onchange="this.form.submit()">
                <?php foreach ($screens as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $s['id']==$screen_id?'selected':'' ?>>
                        <?= htmlspecialchars($s['name'],ENT_QUOTES,'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($screen_id): ?>
        <form method="post" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
            <input type="hidden" name="screen_id" value="<?= $screen_id ?>">
            <div>
                <label>إضافة وسيط إلى القائمة:</label><br>
                <select name="media_id">
                    <?php foreach ($media as $m): ?>
                        <option value="<?= $m['id'] ?>">
                            <?= $m['id'] ?> - <?= htmlspecialchars($m['title'],ENT_QUOTES,'UTF-8') ?> (<?= $m['type']==='video'?'فيديو':'صورة' ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit">إضافة</button>
            </div>
        </form>
        <?php else: ?>
            <p>الرجاء إضافة شاشة أولاً.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2 style="margin-top:0;font-size:16px;">عناصر قائمة التشغيل للشاشة المختارة</h2>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>الترتيب</th>
                <th>العنوان</th>
                <th>النوع</th>
                <th>الملف</th>
                <th>المدة (ث)</th>
                <th>إجراءات</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($playlist as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= $p['sort_order'] ?></td>
                    <td><?= htmlspecialchars($p['title'],ENT_QUOTES,'UTF-8') ?></td>
                    <td><?= $p['type']==='video'?'فيديو':'صورة' ?></td>
                    <td><?= htmlspecialchars($p['filename'],ENT_QUOTES,'UTF-8') ?></td>
                    <td><?= $p['duration'] ?></td>
                    <td>
                        <a class="btn-view" href="javascript:void(0)" onclick="openModal('uploads/<?= htmlspecialchars($p['filename'],ENT_QUOTES,'UTF-8') ?>', '<?= $p['type'] ?>')" title="عرض">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <a class="btn-danger" href="javascript:void(0)" 
                           onclick="confirmDelete('playlist.php?screen=<?= $screen_id ?>&delete=<?= $p['id'] ?>')">حذف</a>
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

<!-- Delete Confirm Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content confirm-modal">
        <h3>تأكيد الحذف</h3>
        <p>هل أنت متأكد من حذف هذا العنصر من القائمة؟</p>
        <div class="modal-actions">
            <button class="btn-danger" id="confirmDeleteBtn">حسناً</button>
            <button class="btn-cancel" onclick="closeDeleteModal()">إلغاء</button>
        </div>
    </div>
</div>

<script>
let deleteTargetUrl = '';

function confirmDelete(url) {
    deleteTargetUrl = url;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

document.getElementById('confirmDeleteBtn').onclick = function() {
    if (deleteTargetUrl) {
        window.location.href = deleteTargetUrl;
    }
};

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
    modalBody.innerHTML = ''; // Stop video and clear
}

window.onclick = function(event) {
    const mediaModal = document.getElementById('mediaModal');
    const deleteModal = document.getElementById('deleteModal');
    if (event.target == mediaModal) {
        closeModal();
    }
    if (event.target == deleteModal) {
        closeDeleteModal();
    }
}
</script>
</body>
</html>
