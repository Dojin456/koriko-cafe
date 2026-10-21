<?php

declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_start();

$menuItems = [
    ['id' => 'strawberry-bingsu', 'name' => 'Strawberry Cloud Bingsu', 'korean' => '딸기 빙수', 'category' => 'dessert', 'description' => 'Shaved milk ice, fresh strawberries, sweet cream, and a little crunch.', 'price' => 1250, 'badge' => 'Guest favorite', 'image' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?auto=format&fit=crop&w=900&q=85', 'alt' => 'Strawberry ice cream dessert'],
    ['id' => 'dal-latte', 'name' => 'Dalgona Cloud Latte', 'korean' => '달고나 라떼', 'category' => 'drink', 'description' => 'Chilled milk, double espresso, and whipped honeycomb coffee.', 'price' => 650, 'badge' => 'Cafe signature', 'image' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=900&q=85', 'alt' => 'Iced latte in a glass'],
    ['id' => 'matcha-latte', 'name' => 'Jeju Matcha Latte', 'korean' => '제주 말차 라떼', 'category' => 'drink', 'description' => 'Stone-ground Jeju green tea with silky milk, hot or iced.', 'price' => 600, 'badge' => 'Plant favorite', 'image' => 'https://images.unsplash.com/photo-1515823064-d6e0c04616a7?auto=format&fit=crop&w=900&q=85', 'alt' => 'Bright green matcha drink'],
    ['id' => 'strawberry-cake', 'name' => 'Strawberry Cream Cake', 'korean' => '딸기 생크림 케이크', 'category' => 'dessert', 'description' => 'Soft vanilla layers, cloud-light cream, and seasonal berries.', 'price' => 850, 'badge' => 'Baked today', 'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=900&q=85', 'alt' => 'Layered strawberry celebration cake'],
    ['id' => 'tteokbokki', 'name' => 'Rose Tteokbokki', 'korean' => '로제 떡볶이', 'category' => 'savory', 'description' => 'Chewy rice cakes in a cozy rose cream sauce with sesame.', 'price' => 1100, 'badge' => 'A little spicy', 'image' => 'https://images.unsplash.com/photo-1555126634-323283e090fa?auto=format&fit=crop&w=900&q=85', 'alt' => 'Korean rice cake dish with sauce'],
    ['id' => 'croffle', 'name' => 'Honey Butter Croffle', 'korean' => '허니 버터 크로플', 'category' => 'dessert', 'description' => 'Crisp croissant-waffle, whipped butter, and local blossom honey.', 'price' => 750, 'badge' => 'Warm & flaky', 'image' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=900&q=85', 'alt' => 'Freshly baked flaky pastry'],
    ['id' => 'yuzu-ade', 'name' => 'Yuzu Garden Ade', 'korean' => '유자 에이드', 'category' => 'drink', 'description' => 'Sparkling citrus, fragrant yuzu marmalade, and fresh mint.', 'price' => 550, 'badge' => 'Bright & bubbly', 'image' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=900&q=85', 'alt' => 'A refreshing tea served over ice'],
    ['id' => 'milk-bread', 'name' => 'Cloud Milk Bread', 'korean' => '우유 식빵', 'category' => 'savory', 'description' => 'Pillowy Japanese-Korean milk bread, baked in small batches.', 'price' => 500, 'badge' => 'Oven fresh', 'image' => 'https://images.unsplash.com/photo-1586444248902-2f64eddc13df?auto=format&fit=crop&w=900&q=85', 'alt' => 'Freshly baked milk bread loaf'],
    ['id' => 'sesame-cookies', 'name' => 'Black Sesame Shortbread', 'korean' => '흑임자 쿠키', 'category' => 'dessert', 'description' => 'Buttery, nutty, and just sweet enough with toasted sesame.', 'price' => 400, 'badge' => 'Little treat', 'image' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?auto=format&fit=crop&w=900&q=85', 'alt' => 'Fresh baked cookies'],
];

function h(string|int|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_valid(mixed $token): bool
{
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function data_path(string $filename): string
{
    $directory = __DIR__ . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('The order data folder could not be created.');
    }
    return $directory . DIRECTORY_SEPARATOR . $filename;
}

function read_orders(): array
{
    $path = data_path('orders.json');
    if (!is_file($path)) {
        return [];
    }
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        return [];
    }
    flock($handle, LOCK_SH);
    $contents = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    $orders = json_decode($contents ?: '[]', true);
    return is_array($orders) ? $orders : [];
}

function append_order(array $order): void
{
    $handle = fopen(data_path('orders.json'), 'c+');
    if ($handle === false) {
        throw new RuntimeException('Orders could not be saved.');
    }
    flock($handle, LOCK_EX);
    $contents = stream_get_contents($handle);
    $orders = json_decode($contents ?: '[]', true);
    if (!is_array($orders)) {
        $orders = [];
    }
    array_unshift($orders, $order);
    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
}

function update_order_status(string $orderId, string $status): bool
{
    $handle = fopen(data_path('orders.json'), 'c+');
    if ($handle === false) {
        return false;
    }
    flock($handle, LOCK_EX);
    $contents = stream_get_contents($handle);
    $orders = json_decode($contents ?: '[]', true);
    $updated = false;
    if (is_array($orders)) {
        foreach ($orders as &$order) {
            if (is_array($order) && ($order['id'] ?? null) === $orderId) {
                $order['status'] = $status;
                $updated = true;
                break;
            }
        }
        unset($order);
    } else {
        $orders = [];
    }
    if ($updated) {
        rewind($handle);
        ftruncate($handle, 0);
        fwrite($handle, json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        fflush($handle);
    }
    flock($handle, LOCK_UN);
    fclose($handle);
    return $updated;
}

function read_admin_hash(): ?string
{
    $path = data_path('admin.json');
    if (!is_file($path)) {
        return null;
    }
    $config = json_decode((string) file_get_contents($path), true);
    return is_array($config) && is_string($config['password_hash'] ?? null) ? $config['password_hash'] : null;
}

function save_admin_hash(string $hash): void
{
    $path = data_path('admin.json');
    $handle = fopen($path, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Admin settings could not be saved.');
    }
    flock($handle, LOCK_EX);
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode(['password_hash' => $hash], JSON_THROW_ON_ERROR));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
}
