<?php
require __DIR__ . '/bootstrap.php';

$error = '';
$flash = $_SESSION['admin_flash'] ?? '';
unset($_SESSION['admin_flash']);
$adminHash = read_admin_hash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        $error = 'This form expired. Refresh the page and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'setup' && $adminHash === null) {
            $password = is_string($_POST['admin_password'] ?? null) ? $_POST['admin_password'] : '';
            $confirmation = is_string($_POST['admin_password_confirm'] ?? null) ? $_POST['admin_password_confirm'] : '';
            if (strlen($password) < 12 || strlen($password) > 200) {
                $error = 'Choose an admin password with at least 12 characters.';
            } elseif (!hash_equals($password, $confirmation)) {
                $error = 'The passwords do not match.';
            } else {
                save_admin_hash(password_hash($password, PASSWORD_DEFAULT));
                session_regenerate_id(true);
                $_SESSION['koriko_admin'] = true;
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header('Location: order.php', true, 303);
                exit;
            }
        } elseif ($action === 'login' && $adminHash !== null) {
            $password = is_string($_POST['admin_password'] ?? null) ? $_POST['admin_password'] : '';
            if (password_verify($password, $adminHash)) {
                session_regenerate_id(true);
                $_SESSION['koriko_admin'] = true;
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header('Location: order.php', true, 303);
                exit;
            }
            $error = 'That admin password did not match.';
        } elseif ($action === 'logout' && !empty($_SESSION['koriko_admin'])) {
            unset($_SESSION['koriko_admin']);
            session_regenerate_id(true);
            $_SESSION['admin_flash'] = 'You are signed out.';
            header('Location: order.php', true, 303);
            exit;
        } elseif ($action === 'status' && !empty($_SESSION['koriko_admin'])) {
            $orderId = is_string($_POST['order_id'] ?? null) ? $_POST['order_id'] : '';
            $status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';
            $validStatuses = ['new', 'preparing', 'ready', 'completed', 'cancelled'];
            if ($orderId !== '' && in_array($status, $validStatuses, true) && update_order_status($orderId, $status)) {
                $_SESSION['admin_flash'] = 'Order status updated.';
                header('Location: order.php', true, 303);
                exit;
            }
            $error = 'That order update could not be saved.';
        } else {
            $error = 'That action is not available.';
        }
    }
}

$adminHash = read_admin_hash();
$isAdmin = !empty($_SESSION['koriko_admin']) && $adminHash !== null;
$orders = $isAdmin ? read_orders() : [];
$totalOrders = count($orders);
$openOrders = count(array_filter($orders, static fn(array $order): bool => !in_array($order['status'] ?? '', ['completed', 'cancelled'], true)));
$todayOrders = count(array_filter($orders, static fn(array $order): bool => substr((string) ($order['created_at'] ?? ''), 0, 10) === date('Y-m-d')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4e9df">
    <title><?= $isAdmin ? 'Order dashboard' : ($adminHash === null ? 'Admin setup' : 'Admin sign in') ?> | Koriko Cafe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Gowun+Dodum&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
    <style>
        body.admin-page { min-height: 100vh; background: #f5eee6; }
        .admin-header { display: flex; min-height: 76px; align-items: center; justify-content: space-between; gap: 20px; padding: 12px clamp(20px, 5vw, 72px); border-bottom: 1px solid var(--line); background: var(--paper-light); }
        .admin-header .button { min-height: 39px; }
        .admin-main { width: min(100% - 40px, 1180px); margin: 46px auto 80px; }
        .admin-heading { display: flex; align-items: end; justify-content: space-between; gap: 20px; margin-bottom: 28px; }
        .admin-heading h1 { margin: 0; font-family: var(--serif); font-size: 40px; font-weight: 500; }
        .admin-heading p { margin: 8px 0 0; color: var(--muted); font-size: 12px; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 28px; }
        .stat { padding: 20px; border: 1px solid var(--line); background: var(--paper-light); }
        .stat span { display: block; margin-bottom: 9px; color: var(--muted); font-size: 10px; }
        .stat strong { font-family: var(--serif); font-size: 29px; font-weight: 500; }
        .orders-head { display: flex; align-items: center; justify-content: space-between; margin: 0 0 12px; }
        .orders-head h2 { margin: 0; font-family: var(--serif); font-size: 23px; font-weight: 500; }
        .orders-head span { color: var(--muted); font-size: 10px; }
        .order-list { display: grid; gap: 12px; }
        .order-card { padding: 20px 22px; border: 1px solid var(--line); background: var(--paper-light); animation: float-in 350ms ease-out both; }
        .order-top { display: flex; flex-wrap: wrap; align-items: start; justify-content: space-between; gap: 10px; padding-bottom: 15px; border-bottom: 1px solid var(--line); }
        .order-id { margin: 0 0 5px; font-family: var(--serif); font-size: 18px; }
        .order-date { margin: 0; color: var(--muted); font-size: 10px; }
        .status-badge { padding: 6px 9px; background: #eee6d9; color: var(--matcha-dark); font-size: 9px; text-transform: uppercase; }
        .status-new { background: #f4ded5; color: #834a3f; }
        .status-preparing { background: #e8e2c9; color: #665a2f; }
        .status-ready { background: #dfe9d6; color: #465c3b; }
        .status-completed { background: #e7e2da; color: #62584f; }
        .status-cancelled { background: #f2dedb; color: #873e37; }
        .order-body { display: grid; grid-template-columns: 1.1fr 1.4fr .7fr; gap: 22px; padding: 16px 0; }
        .order-body h3 { margin: 0 0 7px; color: var(--muted); font-size: 9px; font-weight: 600; text-transform: uppercase; }
        .order-body p, .order-items-list { margin: 0; font-size: 11px; line-height: 1.7; }
        .order-items-list { padding-left: 16px; }
        .order-total { font-family: var(--serif); font-size: 19px !important; }
        .status-form { display: flex; align-items: center; justify-content: end; gap: 8px; padding-top: 13px; border-top: 1px solid var(--line); }
        .status-form label { color: var(--muted); font-size: 10px; }
        .status-form select { min-height: 35px; padding: 0 9px; border: 1px solid var(--line); background: white; color: var(--ink); font-size: 10px; }
        .status-form button { min-height: 35px; padding: 0 12px; border: 0; background: var(--matcha); color: white; cursor: pointer; font-size: 10px; }
        .admin-empty { padding: 52px 20px; border: 1px dashed #cdbdae; color: var(--muted); font-family: var(--serif); font-size: 19px; text-align: center; }
        .admin-auth-wrap { min-height: calc(100vh - 76px); display: grid; place-items: center; padding: 30px 20px; }
        .admin-auth { width: min(100%, 430px); padding: 36px; border: 1px solid var(--line); background: var(--paper-light); }
        .admin-auth h1 { margin: 0 0 8px; font-family: var(--serif); font-size: 31px; font-weight: 500; }
        .admin-auth > p { margin: 0 0 23px; color: var(--muted); font-size: 12px; line-height: 1.6; }
        .admin-auth label { display: block; margin: 14px 0 6px; font-size: 10px; font-weight: 700; }
        .admin-auth input { width: 100%; min-height: 43px; padding: 8px 10px; border: 1px solid var(--line); background: white; color: var(--ink); font-size: 12px; }
        .admin-auth .button { width: 100%; margin-top: 20px; }
        .admin-error, .admin-flash { margin: 0 0 17px; padding: 11px 13px; font-size: 11px; line-height: 1.5; }
        .admin-error { background: #f4dedb; color: #873e37; }
        .admin-flash { background: #e5eadc; color: #465c3b; }
        .admin-back { display: inline-block; margin-top: 20px; color: var(--matcha-dark); font-size: 10px; text-underline-offset: 3px; }
        @media(max-width: 700px) { .admin-main { width: calc(100% - 28px); margin-top: 30px; } .admin-heading { align-items: start; flex-direction: column; } .admin-heading h1 { font-size: 34px; } .order-body { grid-template-columns: 1fr 1fr; gap: 15px; } .order-body > :nth-child(2) { grid-column: 1 / -1; grid-row: 2; } .stats { gap: 7px; } .stat { padding: 14px 11px; } .stat strong { font-size: 24px; } .status-form { justify-content: start; flex-wrap: wrap; } }
        @media(max-width: 430px) { .stats { grid-template-columns: 1fr; } .stat { display: flex; align-items: center; justify-content: space-between; } .stat span { margin: 0; } .admin-auth { padding: 26px 21px; } .admin-header { padding-inline: 15px; } .admin-header .wordmark-copy small { display: none; } }
    </style>
</head>
<body class="admin-page">
    <header class="admin-header">
        <a class="wordmark" href="index.php"><span class="wordmark-seal" aria-hidden="true">고</span><span class="wordmark-copy"><strong>koriko</strong><small>CAFE TEAM</small></span></a>
        <?php if ($isAdmin): ?>
            <form method="post" action="order.php"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="logout"><button class="button button-dark" type="submit">Sign out</button></form>
        <?php else: ?>
            <a class="admin-link" href="index.php">Back to cafe</a>
        <?php endif; ?>
    </header>

    <?php if (!$isAdmin): ?>
        <main class="admin-auth-wrap">
            <section class="admin-auth" aria-labelledby="admin-auth-title">
                <p class="section-eyebrow">관리자 전용 · cafe team only</p>
                <?php if ($adminHash === null): ?>
                    <h1 id="admin-auth-title">Set up your cafe access.</h1>
                    <p>Create the private admin password. This setup page disappears after your password is saved.</p>
                <?php else: ?>
                    <h1 id="admin-auth-title">Good to see you.</h1>
                    <p>Sign in to view and manage customer orders.</p>
                <?php endif; ?>
                <?php if ($flash !== ''): ?><p class="admin-flash" role="status"><?= h($flash) ?></p><?php endif; ?>
                <?php if ($error !== ''): ?><p class="admin-error" role="alert"><?= h($error) ?></p><?php endif; ?>
                <form method="post" action="order.php">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="<?= $adminHash === null ? 'setup' : 'login' ?>">
                    <label for="admin-password">Admin password</label>
                    <input id="admin-password" name="admin_password" type="password" autocomplete="<?= $adminHash === null ? 'new-password' : 'current-password' ?>" minlength="12" required>
                    <?php if ($adminHash === null): ?>
                        <label for="admin-password-confirm">Confirm admin password</label>
                        <input id="admin-password-confirm" name="admin_password_confirm" type="password" autocomplete="new-password" minlength="12" required>
                    <?php endif; ?>
                    <button class="button button-dark" type="submit"><?= $adminHash === null ? 'Create private access' : 'Sign in to orders' ?><span aria-hidden="true">↗</span></button>
                </form>
                <a class="admin-back" href="index.php">← Back to Koriko Cafe</a>
            </section>
        </main>
    <?php else: ?>
        <main class="admin-main">
            <div class="admin-heading"><div><p class="section-eyebrow">주문 현황 · today at koriko</p><h1>Order dashboard</h1><p>Customer orders are private to your cafe team.</p></div><a class="button button-dark" href="index.php">Open cafe site <span aria-hidden="true">↗</span></a></div>
            <?php if ($flash !== ''): ?><p class="admin-flash" role="status"><?= h($flash) ?></p><?php endif; ?>
            <?php if ($error !== ''): ?><p class="admin-error" role="alert"><?= h($error) ?></p><?php endif; ?>
            <section class="stats" aria-label="Order summary">
                <div class="stat"><span>All orders</span><strong><?= $totalOrders ?></strong></div>
                <div class="stat"><span>Open orders</span><strong><?= $openOrders ?></strong></div>
                <div class="stat"><span>Received today</span><strong><?= $todayOrders ?></strong></div>
            </section>
            <section aria-labelledby="orders-title">
                <div class="orders-head"><h2 id="orders-title">All customer orders</h2><span>Newest first</span></div>
                <?php if ($orders === []): ?>
                    <p class="admin-empty">No orders yet. Your first lovely order will show up here.</p>
                <?php else: ?>
                    <div class="order-list">
                        <?php foreach ($orders as $order): ?>
                            <?php
                            $status = is_string($order['status'] ?? null) ? $order['status'] : 'new';
                            $created = strtotime((string) ($order['created_at'] ?? ''));
                            ?>
                            <article class="order-card">
                                <div class="order-top"><div><h3 class="order-id">Order #<?= h($order['id'] ?? '') ?></h3><p class="order-date"><?= $created ? h(date('M j, Y · g:i a', $created)) : 'Date unavailable' ?> · Pickup <?= h($order['pickup_time'] ?? '') ?></p></div><span class="status-badge status-<?= h($status) ?>"><?= h($status) ?></span></div>
                                <div class="order-body">
                                    <div><h3>Customer</h3><p><?= h($order['customer_name'] ?? '') ?><br><?= h($order['customer_phone'] ?? '') ?></p></div>
                                    <div><h3>Items<?= !empty($order['note']) ? ' · note: ' . h($order['note']) : '' ?></h3><ul class="order-items-list">
                                        <?php foreach (($order['items'] ?? []) as $item): ?><li><?= h($item['quantity'] ?? 0) ?> × <?= h($item['name'] ?? '') ?> · $<?= number_format(((int) ($item['line_total'] ?? 0)) / 100, 2) ?></li><?php endforeach; ?>
                                    </ul></div>
                                    <div><h3>Order total</h3><p class="order-total">$<?= number_format(((int) ($order['total_cents'] ?? 0)) / 100, 2) ?></p></div>
                                </div>
                                <form class="status-form" method="post" action="order.php">
                                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="status"><input type="hidden" name="order_id" value="<?= h($order['id'] ?? '') ?>">
                                    <label for="status-<?= h($order['id'] ?? '') ?>">Update status</label>
                                    <select id="status-<?= h($order['id'] ?? '') ?>" name="status">
                                        <?php foreach (['new' => 'New', 'preparing' => 'Preparing', 'ready' => 'Ready', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $statusValue => $statusLabel): ?><option value="<?= h($statusValue) ?>"<?= $status === $statusValue ? ' selected' : '' ?>><?= h($statusLabel) ?></option><?php endforeach; ?>
                                    </select>
                                    <button type="submit">Save status</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    <?php endif; ?>
</body>
</html>
