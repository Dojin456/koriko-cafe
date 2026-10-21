<?php
require __DIR__ . '/bootstrap.php';
$confirmation = $_SESSION['order_confirmation'] ?? null;
unset($_SESSION['order_confirmation']);
if (!is_array($confirmation) || !is_string($confirmation['id'] ?? null) || !is_int($confirmation['total_cents'] ?? null)) {
    header('Location: index.php', true, 303);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4e9df">
    <title>Order received | Koriko Cafe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Gowun+Dodum&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
    <style>
        .thanks-page { min-height: 100vh; display: grid; place-items: center; padding: 24px; background: radial-gradient(ellipse at 15% 10%, #fff8f0 0, transparent 42%), var(--paper); }
        .thanks-panel { width: min(100%, 480px); padding: 44px 36px; border: 1px solid var(--line); background: var(--paper-light); text-align: center; animation: float-in 450ms ease-out both; }
        .thanks-seal { display: grid; width: 68px; aspect-ratio: 1; place-items: center; margin: 0 auto 22px; border: 1px solid var(--rose); border-radius: 50%; color: var(--rose); font-family: "Gowun Dodum", serif; font-size: 28px; }
        .thanks-panel h1 { margin: 0 0 12px; font-family: var(--serif); font-size: 38px; font-weight: 500; }
        .thanks-panel p { color: var(--muted); font-size: 13px; line-height: 1.7; }
        .thanks-number { margin: 24px 0; padding: 16px; background: #eee6d9; color: var(--ink); font-size: 11px; }
        .thanks-number strong { display: block; margin-top: 6px; font-family: var(--serif); font-size: 23px; font-weight: 500; }
        @media(max-width: 480px) { .thanks-panel { padding: 34px 22px; } .thanks-panel h1 { font-size: 31px; } }
    </style>
</head>
<body>
    <main class="thanks-page">
        <section class="thanks-panel" aria-labelledby="thanks-title">
            <div class="thanks-seal" aria-hidden="true">고</div>
            <p class="section-eyebrow">주문 완료 · order received</p>
            <h1 id="thanks-title">Something lovely is on its way.</h1>
            <p>Thanks for choosing Koriko. The cafe team has your order and will have it ready for your pickup window.</p>
            <p class="thanks-number">Your order number<strong>#<?= h($confirmation['id']) ?></strong></p>
            <p>Order total · <strong><?= '$' . number_format($confirmation['total_cents'] / 100, 2) ?></strong></p>
            <a class="button button-dark" href="index.php#menu">Back to the menu <span aria-hidden="true">↗</span></a>
        </section>
    </main>
</body>
</html>
