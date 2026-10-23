<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url'])) {
    $url = trim($_POST['url']);
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $stmt = $pdo->prepare("INSERT INTO products (url) VALUES (?)");
        $stmt->execute([$url]);
        header("Location: index.php");
        exit;
    } else {
        $error = "Некорректный URL";
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: index.php");
    exit;
}

$products = $pdo->query("
    SELECT p.id, p.url, p.title, 
           (SELECT price FROM price_history 
            WHERE product_id = p.id 
            ORDER BY checked_at DESC LIMIT 1) as current_price,
           (SELECT checked_at FROM price_history 
            WHERE product_id = p.id 
            ORDER BY checked_at DESC LIMIT 1) as last_check
    FROM products p
    ORDER BY p.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>PriceChuck - Главная</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1> PriceChuck</h1>
        <nav>
            <a href="index.php">Товары</a>
            <a href="notifications.php">Уведомления</a>
        </nav>
    </header>
    
    <div class="container">
        <div class="card">
            <h2>Добавить товар для отслеживания</h2>
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form method="POST" class="form-group">
                <input type="url" name="url" placeholder="https://example.com/product/123" required>
                <button type="submit">Добавить</button>
            </form>
        </div>
        
        <h2>Отслеживаемые товары (<?= count($products) ?>)</h2>
        
        <?php if (empty($products)): ?>
            <div class="card empty">
                <p>Список пуст. Добавьте первый товар для отслеживания.</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <h3><?= htmlspecialchars($product['title'] ?? 'Без названия') ?></h3>
                    <p class="url">
                        <a href="<?= htmlspecialchars($product['url']) ?>" target="_blank">
                            <?= htmlspecialchars($product['url']) ?>
                        </a>
                    </p>
                    <?php if ($product['current_price'] !== null): ?>
                        <p class="price"><?= number_format($product['current_price'], 2, ',', ' ') ?> ₽</p>
                        <small>Последняя проверка: <?= $product['last_check'] ?></small>
                    <?php else: ?>
                        <p class="price unknown">Цена еще не проверена</p>
                    <?php endif; ?>
                    <p style="margin-top: 10px;">
                        <a href="history.php?id=<?= $product['id'] ?>" class="btn">📊 История цен</a>
                        <a href="?delete=<?= $product['id'] ?>" class="btn delete-btn" 
                           style="background: #dc3545;">🗑 Удалить</a>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <script src="assets/js/app.js"></script>
</body>
</html>