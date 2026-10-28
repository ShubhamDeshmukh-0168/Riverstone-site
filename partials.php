<?php
function render_header($active = '') {
    $items = [
        'home'    => ['/index.php', 'Home'],
        'about'   => ['/about.php', 'About'],
        'team'    => ['/team.php', 'Team'],
        'contact' => ['/contact.php', 'Contact'],
    ];
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Riverstone Consulting</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <header class="site">
        <div class="wrap">
            <a class="brand" href="/index.php">Riverstone Consulting</a>
            <nav class="site">
                <?php foreach ($items as $key => $item): ?>
                    <a href="<?= $item[0] ?>" <?= $key === $active ? 'style="color:#fff;font-weight:600;"' : '' ?>><?= $item[1] ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>
    <?php
}

function render_footer() {
    ?>
    <footer class="site">
        <div class="wrap">
            &copy; <?= date('Y') ?> Riverstone Consulting &middot; Demo site for lab/training use
        </div>
    </footer>
    </body>
    </html>
    <?php
}
