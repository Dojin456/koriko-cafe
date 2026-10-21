<?php
require __DIR__ . '/bootstrap.php';

function order_error(string $message, int $status = 400): never
{
    http_response_code($status);
    $safeMessage = h($message);
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Order not placed | Koriko Cafe</title><body style="margin:0;padding:40px;background:#fbf6ef;color:#382721;font:16px sans-serif"><main style="max-width:520px;margin:10vh auto;padding:32px;background:#f4e9df"><p>코리코 카페 · KORIKO CAFE</p><h1>We couldn’t place that order</h1><p role="alert">' . $safeMessage . '</p><a href="index.php#menu">Back to the menu</a></main></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php', true, 303);
    exit;
}

if (!csrf_valid($_POST['csrf_token'] ?? null)) {
    order_error('Your order form expired. Please refresh the menu and try again.', 403);
}

$name = trim(is_string($_POST['customer_name'] ?? null) ? $_POST['customer_name'] : '');
$phone = trim(is_string($_POST['customer_phone'] ?? null) ? $_POST['customer_phone'] : '');
$pickupTime = $_POST['pickup_time'] ?? '';
$note = trim(is_string($_POST['order_note'] ?? null) ? $_POST['order_note'] : '');
$allowedPickupTimes = ['As soon as possible', 'In 20 minutes', 'In 40 minutes'];

if ($name === '' || mb_strlen($name) > 80) {
    order_error('Please enter your name (up to 80 characters).');
}
if (!preg_match('/^[0-9+().\-\s]{7,24}$/D', $phone)) {
    order_error('Please enter a valid phone number.');
}
if (!is_string($pickupTime) || !in_array($pickupTime, $allowedPickupTimes, true)) {
    order_error('Please choose one of the listed pickup times.');
}
if (mb_strlen($note) > 240) {
    order_error('Please keep your cafe note under 240 characters.');
}

$submittedItems = json_decode(is_string($_POST['items'] ?? null) ? $_POST['items'] : '', true);
if (!is_array($submittedItems) || $submittedItems === [] || count($submittedItems) > count($menuItems)) {
    order_error('Your bag is empty or invalid. Please add menu items and try again.');
}

$catalog = [];
foreach ($menuItems as $menuItem) {
    $catalog[$menuItem['id']] = $menuItem;
}
$items = [];
$totalCents = 0;
foreach ($submittedItems as $submittedItem) {
    if (!is_array($submittedItem) || !is_string($submittedItem['id'] ?? null) || !is_int($submittedItem['quantity'] ?? null)) {
        order_error('One of the items in your bag is invalid.');
    }
    $id = $submittedItem['id'];
    $quantity = $submittedItem['quantity'];
    if (!isset($catalog[$id]) || $quantity < 1 || $quantity > 20 || isset($items[$id])) {
        order_error('One of the items in your bag is unavailable.');
    }
    $menuItem = $catalog[$id];
    $lineTotal = $menuItem['price'] * $quantity;
    $items[$id] = [
        'id' => $id,
        'name' => $menuItem['name'],
        'korean' => $menuItem['korean'],
        'quantity' => $quantity,
        'unit_price' => $menuItem['price'],
        'line_total' => $lineTotal,
    ];
    $totalCents += $lineTotal;
}

$orderId = strtoupper(bin2hex(random_bytes(4)));
$order = [
    'id' => $orderId,
    'created_at' => date(DATE_ATOM),
    'status' => 'new',
    'customer_name' => $name,
    'customer_phone' => $phone,
    'pickup_time' => $pickupTime,
    'note' => $note,
    'items' => array_values($items),
    'total_cents' => $totalCents,
];

try {
    append_order($order);
} catch (Throwable $exception) {
    error_log('Koriko order save failed: ' . $exception->getMessage());
    order_error('The cafe could not save your order. Please try again in a moment.', 500);
}

$_SESSION['order_confirmation'] = ['id' => $orderId, 'total_cents' => $totalCents];
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
header('Location: thanks.php', true, 303);
exit;
