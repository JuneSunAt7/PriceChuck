<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $pdo->exec("UPDATE notifications SET notified = 1 WHERE notified = 0");
    header("Location: notifications.php");
    exit;
}

$notifications = $pdo->query("
    SELECT n.*, p.url, p.title 
    FROM notifications n
    JOIN products p ON n.product_id = p.id
    ORDER BY n.created_at DESC
    LIMIT 50
")->fetchAll();

$unread_count = count(array_filter($notifications, fn($n) => !$n['notified']));
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Уведомления</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1> Уведомления</h1>
        <nav>
            <a href="index.php">Товары</a>
            <a href="notifications.php">Уведомления</a>
        </nav>
    </header>
    
    <div class="container">
        <a href="index.php" class="back-link">← На главную</a>
        
        <div class="card">
            <h2>Непрочитанных: <?= $unread_count ?></h2>
            
            <?php if (!empty($notifications) && $unread_count > 0): ?>
                <form method="POST" style="margin-top: 10px;">
                    <button type="submit" name="mark_read">✓ Пометить все как прочитанные</button>
                </form>
            <?php endif; ?>
        </div>
        
        <?php if (empty($notifications)): ?>
            <div class="card empty">
                <p>Уведомлений пока нет. Как только цена на товар снизится — вы увидите это здесь.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $n): ?>
                <div class="notification" style="<?= $n['notified'] ? 'opacity: 0.6;' : '' ?>">
                    <h3><?= htmlspecialchars($n['title'] ?? 'Товар') ?></h3>
                    <p>
                        Цена снизилась: 
                        <span class="price-drop">
                            <?= number_format($n['old_price'], 2, ',', ' ') ?> ₽ 
                            → 
                            <?= number_format($n['new_price'], 2, ',', ' ') ?> ₽
                        </span>
                    </p>
                    <p>Экономия: <strong><?= number_format($n['old_price'] - $n['new_price'], 2, ',', ' ') ?> ₽</strong></p>
                    <p style="font-size: 12px; color: #666; margin-top: 5px;">
                        <?= $n['created_at'] ?>
                        <?= $n['notified'] ? '• Прочитано' : '• Новое' ?>
                    </p>
                    <a href="<?= htmlspecialchars($n['url']) ?>" target="_blank" class="btn">
                        Перейти к товару
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>