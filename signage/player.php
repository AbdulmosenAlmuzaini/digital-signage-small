<?php
require 'config.php';

$screen_id = isset($_GET['screen']) ? (int)$_GET['screen'] : 0;
if (!$screen_id) {
    die('no screen id');
}

$stmt = $pdo->prepare("SELECT * FROM screens WHERE id=?");
$stmt->execute([$screen_id]);
$screen = $stmt->fetch();
if (!$screen) {
    die('screen not found');
}

$orientation = $screen['orientation'];

$stmt = $pdo->prepare("
    SELECT m.id, m.title, m.filename, m.type, m.duration
    FROM playlists p
    JOIN media m ON m.id = p.media_id
    WHERE p.screen_id = ?
    ORDER BY p.sort_order ASC, p.id ASC
");
$stmt->execute([$screen_id]);
$items = $stmt->fetchAll();

$index = isset($_GET['index']) ? (int)$_GET['index'] : 0;
$has_items = !empty($items);

if (!$has_items) {
    $duration = 10;
    $next_index = 0;
    $current_item = null;
} else {
    if ($index < 0 || $index >= count($items)) {
        $index = 0;
    }
    $current_item = $items[$index];
    $next_index = $index + 1;
    if ($next_index >= count($items)) {
        $next_index = 0;
    }
    
    $duration = (int)$current_item['duration'];
    if ($duration <= 0) $duration = 10;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Player</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <!-- Fallback refresh in case JS crashes or is disabled -->
    <meta http-equiv="refresh" content="<?= $duration + 2 ?>;url=<?= $screen_id ?>?index=<?= $next_index ?>">
    <style>
        html,body{
            margin:0;
            width:100%;
            height:100%;
            background:#000;
            overflow:hidden;
        }
        body{
            direction:ltr; /* حتى لا يتأثر الفيديو بالنصوص العربية */
        }
        .wrapper{
            width:100vw;
            height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#000;
        }
        img,video{
            max-width:100vw;
            max-height:100vh;
            display:block;
            background:#000;
        }
        body.landscape img,
        body.landscape video{
            width:100vw;
            height:100vh;
            object-fit:cover;
        }
        body.portrait img,
        body.portrait video{
            width:100vw;
            height:100vh;
            object-fit:contain;
        }
    </style>
</head>
<body class="<?= $orientation === 'portrait' ? 'portrait' : 'landscape' ?>">
<div class="wrapper" id="player">
    <?php if (!$has_items): ?>
        <div style="color:#fff;font-family:Tahoma;">لا توجد عناصر في قائمة التشغيل.</div>
    <?php else: ?>
        <?php if ($current_item['type'] === 'image'): ?>
            <img src="uploads/<?= htmlspecialchars($current_item['filename'], ENT_QUOTES, 'UTF-8') ?>">
        <?php else: 
            $ext = strtolower(pathinfo($current_item['filename'], PATHINFO_EXTENSION));
            $mime = 'video/mp4';
            if ($ext === 'webm') $mime = 'video/webm';
            if ($ext === 'ogg') $mime = 'video/ogg';
        ?>
            <video autoplay muted playsinline webkit-playsinline preload="auto" style="width:100%; height:100%; object-fit:contain; background:#000;">
                <source src="uploads/<?= htmlspecialchars($current_item['filename'], ENT_QUOTES, 'UTF-8') ?>" type="<?= $mime ?>">
            </video>
        <?php endif; ?>
    <?php endif; ?>
</div>
<script>
<?php if ($has_items): ?>
    var type = <?= json_encode($current_item['type']) ?>;
    var duration = <?= $duration ?>;
    var nextUrl = "<?= $screen_id ?>?index=<?= $next_index ?>";

    if (type === 'image') {
        setTimeout(function() {
            window.location.href = nextUrl;
        }, duration * 1000);
    } else {
        var vid = document.querySelector('video');
        if (vid) {
            var triggered = false;
            var goNext = function() {
                if (!triggered) {
                    triggered = true;
                    window.location.href = nextUrl;
                }
            };
            vid.addEventListener('ended', goNext);
            vid.addEventListener('error', goNext);
            
            // محاولة تشغيل الفيديو برمجياً في حال منعت المتصفحات القديمة التشغيل التلقائي
            var playPromise = vid.play();
            if (playPromise !== undefined && typeof playPromise.catch === 'function') {
                playPromise.catch(function(error) {
                    console.log("Autoplay prevented or video error: " + error);
                });
            }
        } else {
            setTimeout(function() {
                window.location.href = nextUrl;
            }, duration * 1000);
        }
    }
<?php else: ?>
    setTimeout(function() {
        window.location.reload();
    }, 10000);
<?php endif; ?>
</script>
</body>
</html>
