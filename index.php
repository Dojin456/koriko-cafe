<?php
require __DIR__ . '/bootstrap.php';
$csrf = csrf_token();
$menuJson = json_encode($menuItems, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4e9df">
    <title>Koriko Cafe | A little Seoul, close to home</title>
    <meta name="description" content="Matcha, milk bread, bingsu, and cozy Korean cafe days. Order your favorites from Koriko Cafe.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Gowun+Dodum&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
    <script>window.KORIKO_MENU = <?= $menuJson ?>;</script>
    <script src="assets/app.js" defer></script>
</head>
<body>
    <header class="site-header">
        <a class="wordmark" href="#top" aria-label="Koriko Cafe home">
            <span class="wordmark-seal" aria-hidden="true">고</span>
            <span class="wordmark-copy"><strong>koriko</strong><small>SEOUL CAFE · SINCE 2024</small></span>
        </a>
        <nav class="main-nav" aria-label="Main navigation">
            <a href="#menu">Menu</a>
            <a href="#story">Our story</a>
            <a href="#visit">Visit</a>
        </nav>
        <button class="cart-trigger" id="cart-open" type="button" aria-controls="cart-panel" aria-expanded="false">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.1 10.1a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 1.9-1.4L22 8H6M10 20h.01M18 20h.01"/></svg>
            <span class="cart-trigger-label">Your bag</span><span class="cart-count" id="cart-count">0</span>
        </button>
    </header>

    <main id="top">
        <section class="hero" aria-labelledby="hero-title">
            <div class="hero-shade"></div>
            <div class="hero-content">
                <p class="kicker"><span class="kicker-dot"></span> A little Seoul, close to home</p>
                <h1 id="hero-title">Soft days.<br><em>Something sweet.</em></h1>
                <p class="hero-description">Come in for the matcha. Stay for the milk bread, the slow mornings, and one more little treat.</p>
                <a class="button button-light" href="#menu">Find your favorite <span aria-hidden="true">↘</span></a>
            </div>
            <div class="hero-sticker" aria-label="Made fresh every day"><span>매일</span><small>made fresh<br>every day</small></div>
            <div class="hero-caption"><span>01 / 03</span><span>YOUR NEIGHBORHOOD SLOW-DOWN</span></div>
        </section>

        <section class="menu-section section-wrap" id="menu" aria-labelledby="menu-title">
            <div class="section-heading">
                <div><p class="section-eyebrow">작은 행복 · little joys</p><h2 id="menu-title">The cafe menu</h2></div>
                <p class="section-note">A few lovely things, made fresh<br>and meant to be shared.</p>
            </div>
            <div class="menu-toolbar">
                <div class="filter-list" role="group" aria-label="Filter menu">
                    <button class="filter-button is-active" type="button" data-filter="all" aria-pressed="true">Everything <span>09</span></button>
                    <button class="filter-button" type="button" data-filter="drink" aria-pressed="false">Sips</button>
                    <button class="filter-button" type="button" data-filter="dessert" aria-pressed="false">Sweet things</button>
                    <button class="filter-button" type="button" data-filter="savory" aria-pressed="false">From the oven</button>
                </div>
                <p class="made-note"><span aria-hidden="true">✳</span> Always made with care</p>
            </div>
            <div class="menu-grid" id="menu-grid">
                <?php foreach ($menuItems as $index => $item): ?>
                    <article class="menu-card" data-category="<?= h($item['category']) ?>" style="--card-order: <?= $index ?>">
                        <div class="menu-image-wrap">
                            <img class="menu-image" src="<?= h($item['image']) ?>" alt="<?= h($item['alt']) ?>" loading="lazy" width="900" height="700">
                            <span class="menu-badge"><?= h($item['badge']) ?></span>
                            <button class="quick-add" type="button" data-add="<?= h($item['id']) ?>" aria-label="Add <?= h($item['name']) ?> to your bag">+</button>
                        </div>
                        <div class="menu-card-copy">
                            <div class="menu-title-line"><div><span class="korean-name"><?= h($item['korean']) ?></span><h3><?= h($item['name']) ?></h3></div><span class="price">$<?= number_format($item['price'] / 100, 2) ?></span></div>
                            <p><?= h($item['description']) ?></p>
                            <button class="add-link" type="button" data-add="<?= h($item['id']) ?>">Add to bag <span aria-hidden="true">+</span></button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="empty-filter" id="empty-filter" hidden>No treats in this little corner yet.</p>
        </section>

        <section class="story-band" id="story">
            <div class="story-image" role="img" aria-label="A cozy cafe table set for tea"></div>
            <div class="story-copy">
                <p class="section-eyebrow">천천히 · take it slow</p>
                <h2>A small pause<br>in a busy day.</h2>
                <p>Koriko is our love letter to the Seoul cafe afternoons we never wanted to end: warm bread, good tea, and a seat saved for a friend.</p>
                <a class="text-link" href="#visit">A little more about us <span aria-hidden="true">↗</span></a>
                <span class="story-stamp" aria-hidden="true">코리코<br><small>come as you are</small></span>
            </div>
        </section>

        <section class="visit-section section-wrap" id="visit">
            <div><p class="section-eyebrow">찾아오세요 · come say hi</p><h2>Your cozy corner<br>is right here.</h2></div>
            <div class="visit-details"><p>Pickup orders are made fresh<br>at the Koriko counter.</p><p>Open daily · 8am–6pm<br><a href="#menu">Browse today's menu</a></p></div>
            <a class="visit-arrow" href="#menu" aria-label="Browse cafe menu">↗</a>
        </section>
    </main>

    <footer class="site-footer"><a class="footer-wordmark" href="#top">koriko<span>고요한 오후, 작은 행복</span></a><p>Made slowly, shared gladly. © <?= date('Y') ?> Koriko Cafe.</p><a href="order.php" class="admin-link">Cafe team</a></footer>

    <div class="cart-backdrop" id="cart-backdrop" hidden></div>
    <aside class="cart-panel" id="cart-panel" aria-labelledby="cart-title" aria-hidden="true" inert>
        <div class="cart-head"><div><p class="section-eyebrow">오늘의 주문 · today's order</p><h2 id="cart-title">Your little bag</h2></div><button class="icon-button" id="cart-close" type="button" aria-label="Close bag">×</button></div>
        <div class="cart-items" id="cart-items" aria-live="polite"></div>
        <p class="cart-empty" id="cart-empty">Your bag is taking a little nap.<br>Add something lovely from the menu.</p>
        <form class="checkout-form" id="checkout-form" action="place-order.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
            <input type="hidden" name="items" id="order-items">
            <div class="cart-total"><span>Subtotal</span><strong id="cart-total">$0.00</strong></div>
            <label class="checkout-label" for="customer-name">Your name</label><input class="checkout-input" id="customer-name" name="customer_name" maxlength="80" autocomplete="name" required>
            <label class="checkout-label" for="customer-phone">Phone number</label><input class="checkout-input" id="customer-phone" name="customer_phone" type="tel" maxlength="24" autocomplete="tel" required>
            <label class="checkout-label" for="pickup-time">Pickup time</label>
            <select class="checkout-input" id="pickup-time" name="pickup_time" required><option value="">Choose a pickup window</option><option value="As soon as possible">As soon as possible</option><option value="In 20 minutes">In 20 minutes</option><option value="In 40 minutes">In 40 minutes</option></select>
            <label class="checkout-label" for="order-note">A note for the cafe <span>(optional)</span></label><textarea class="checkout-input checkout-note" id="order-note" name="order_note" maxlength="240" rows="2" placeholder="Milk preference, allergy note..."></textarea>
            <button class="button button-dark checkout-submit" id="checkout-submit" type="submit" disabled>Place my order <span aria-hidden="true">↗</span></button>
            <p class="checkout-fineprint">Your order details are only visible to the cafe team.</p>
        </form>
    </aside>
    <p class="cart-announcement sr-only" id="cart-announcement" aria-live="polite"></p>
</body>
</html>
