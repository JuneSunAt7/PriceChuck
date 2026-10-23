<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDbConnection();

$product_id = (int)($_GET['id'] ?? 0);
if ($product_id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    die("Товар не найден");
}

$stmt = $pdo->prepare("SELECT price, checked_at FROM price_history WHERE product_id = ? ORDER BY checked_at ASC");
$stmt->execute([$product_id]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>История цен - <?= htmlspecialchars($product['title'] ?? 'Товар') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <h1> История цен</h1>
        <nav>
            <a href="index.php">Товары</a>
            <a href="notifications.php">Уведомления</a>
        </nav>
    </header>
    
    <div class="container">
        <a href="index.php" class="back-link">← Назад к списку</a>
        
        <div class="card">
            <h2><?= htmlspecialchars($product['title'] ?? 'Товар') ?></h2>
            <p style="color: #666; font-size: 13px; word-break: break-all;">
                <?= htmlspecialchars($product['url']) ?>
            </p>
        </div>
        
        <div class="card">
            <?php if (empty($history)): ?>
                <p class="empty">История цен пока пуста. Запустите Python-парсер.</p>
            <?php else: ?>
                <canvas id="priceChart" height="100"></canvas>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($history)): ?>
        <div class="card">
            <h3>Таблица изменений</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f4f6f9;">
                        <th style="padding: 10px; text-align: left;">Дата</th>
                        <th style="padding: 10px; text-align: right;">Цена</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($history) as $row): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;"><?= $row['checked_at'] ?></td>
                        <td style="padding: 10px; text-align: right; font-weight: bold;">
                            <?= number_format($row['price'], 2, ',', ' ') ?> ₽
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($history)): ?>
    <script>
        const historyData = <?= json_encode($history) ?>;
        const ctx = document.getElementById('priceChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: historyData.map(d => d.checked_at.substring(0, 16)),
                datasets: [{
                    label: 'Цена (₽)',
                    data: historyData.map(d => d.price),
                    borderColor: '#2c5aa0',
                    backgroundColor: 'rgba(44, 90, 160, 0.1)',
                    tension: 0.2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: false }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>