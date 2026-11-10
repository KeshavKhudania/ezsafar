<?php
header("Content-Type: application/xml; charset=utf-8");

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$db = get_db();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$siteUrl = $protocol . $host . BASE_URL;

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    
    <!-- Core Hub Pages -->
    <url>
        <loc><?= $siteUrl ?>/index.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= $siteUrl ?>/car-rentals.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= $siteUrl ?>/buy-cars.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= $siteUrl ?>/sell-your-car.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= $siteUrl ?>/blog.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Rental Fleet Product URLs -->
    <?php
    $rentals = $db->query("SELECT * FROM rental_cars WHERE is_available = 1 ORDER BY id DESC")->fetchAll();
    foreach ($rentals as $r):
        $slug = $r['slug'] ?: 'rental-' . $r['id'];
    ?>
    <url>
        <loc><?= $siteUrl ?>/vehicle.php?slug=<?= htmlspecialchars($slug) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($r['created_at'])) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
        <?php if (!empty($r['image_url'])): ?>
            <image:image>
                <image:loc><?= htmlspecialchars($r['image_url']) ?></image:loc>
                <image:title><?= htmlspecialchars($r['name']) ?></image:title>
                <image:caption>Rent <?= htmlspecialchars($r['name']) ?> in Jind, Haryana</image:caption>
            </image:image>
        <?php endif; ?>
    </url>
    <?php endforeach; ?>

    <!-- Certified Used Cars Product URLs -->
    <?php
    $used = $db->query("SELECT * FROM used_cars WHERE status = 'Available' ORDER BY id DESC")->fetchAll();
    foreach ($used as $u):
        $slug = $u['slug'] ?: 'used-' . $u['id'];
    ?>
    <url>
        <loc><?= $siteUrl ?>/vehicle.php?slug=<?= htmlspecialchars($slug) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($u['created_at'])) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
        <?php if (!empty($u['image_url'])): ?>
            <image:image>
                <image:loc><?= htmlspecialchars($u['image_url']) ?></image:loc>
                <image:title><?= htmlspecialchars($u['title']) ?></image:title>
                <image:caption>Buy <?= htmlspecialchars($u['title']) ?> in Jind Haryana 140-point certified</image:caption>
            </image:image>
        <?php endif; ?>
    </url>
    <?php endforeach; ?>

    <!-- Blog & Knowledge Hub Articles -->
    <?php
    $blogPosts = $db->query("SELECT * FROM blog_posts WHERE is_published = 1 ORDER BY id DESC")->fetchAll();
    foreach ($blogPosts as $bp):
    ?>
    <url>
        <loc><?= $siteUrl ?>/blog-post.php?slug=<?= htmlspecialchars($bp['slug']) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($bp['updated_at'] ?? $bp['published_at'])) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
        <?php if (!empty($bp['featured_image'])): ?>
            <image:image>
                <image:loc><?= htmlspecialchars($bp['featured_image']) ?></image:loc>
                <image:title><?= htmlspecialchars($bp['title']) ?></image:title>
                <image:caption><?= htmlspecialchars(substr($bp['excerpt'] ?? $bp['title'], 0, 150)) ?></image:caption>
            </image:image>
        <?php endif; ?>
    </url>
    <?php endforeach; ?>
</urlset>

