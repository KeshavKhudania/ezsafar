<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$db = get_db();
$slug = sanitize($_GET['slug'] ?? '');
$id = (int)($_GET['id'] ?? 0);

$post = null;
if (!empty($slug)) {
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM blog_posts p 
        LEFT JOIN blog_categories c ON p.category_id = c.id 
        WHERE p.slug = ? AND p.is_published = 1 
        LIMIT 1
    ");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
} elseif ($id > 0) {
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM blog_posts p 
        LEFT JOIN blog_categories c ON p.category_id = c.id 
        WHERE p.id = ? AND p.is_published = 1 
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
}

if (!$post) {
    header("HTTP/1.0 404 Not Found");
    $pageTitle = "Article Not Found | EzSafar";
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="max-w-4xl mx-auto px-4 py-24 text-center">
        <span class="material-symbols-outlined text-6xl text-outline">article</span>
        <h1 class="text-3xl font-black text-on-surface mt-4">Article Not Found</h1>
        <p class="text-sm text-on-surface-variant mt-2">The article you are looking for may have been moved or unpublished.</p>
        <div class="mt-6 flex justify-center gap-3">
            <a href="' . BASE_URL . '/blog.php" class="px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow">Browse All Articles</a>
            <a href="' . BASE_URL . '/index.php" class="px-5 py-2.5 rounded-xl bg-surface-container text-on-surface text-xs font-bold shadow">Return to Home</a>
        </div>
    </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Increment view count
$db->prepare("UPDATE blog_posts SET views_count = views_count + 1 WHERE id = ?")->execute([$post['id']]);

// SEO Meta Variables
$pageTitle = !empty($post['meta_title']) ? $post['meta_title'] : $post['title'] . " | EzSafar Blog";
$pageDesc = !empty($post['meta_description']) ? $post['meta_description'] : $post['excerpt'];
$pageKeywords = !empty($post['meta_keywords']) ? $post['meta_keywords'] : $post['title'] . ", ezsafar blog, jind haryana";
$ogImage = $post['featured_image'];
$canonicalUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/blog-post.php?slug=' . urlencode($post['slug']);

// Fetch related articles
$relStmt = $db->prepare("
    SELECT id, title, slug, featured_image, reading_time, published_at 
    FROM blog_posts 
    WHERE category_id = ? AND id != ? AND is_published = 1 
    ORDER BY published_at DESC 
    LIMIT 3
");
$relStmt->execute([$post['category_id'] ?? 0, $post['id']]);
$relatedPosts = $relStmt->fetchAll();
if (empty($relatedPosts)) {
    // Fallback to recent posts if no same-category articles
    $relStmt = $db->prepare("
        SELECT id, title, slug, featured_image, reading_time, published_at 
        FROM blog_posts 
        WHERE id != ? AND is_published = 1 
        ORDER BY published_at DESC 
        LIMIT 3
    ");
    $relStmt->execute([$post['id']]);
    $relatedPosts = $relStmt->fetchAll();
}

// Fetch prev / next articles for reading flow
$prevStmt = $db->prepare("SELECT title, slug FROM blog_posts WHERE id < ? AND is_published = 1 ORDER BY id DESC LIMIT 1");
$prevStmt->execute([$post['id']]);
$prevPost = $prevStmt->fetch();

$nextStmt = $db->prepare("SELECT title, slug FROM blog_posts WHERE id > ? AND is_published = 1 ORDER BY id ASC LIMIT 1");
$nextStmt->execute([$post['id']]);
$nextPost = $nextStmt->fetch();

// Fetch 2 spotlight vehicles for the sidebar CTA
$sidebarCars = $db->query("SELECT name, slug, price_per_day, category, image_url FROM rental_cars WHERE is_available = 1 ORDER BY id DESC LIMIT 2")->fetchAll();

// Schema.org BlogPosting JSON-LD
$schemaData = [
    "@context" => "https://schema.org",
    "@type" => "BlogPosting",
    "mainEntityOfPage" => [
        "@type" => "WebPage",
        "@id" => $canonicalUrl
    ],
    "headline" => $post['title'],
    "description" => $post['excerpt'],
    "image" => [
        $post['featured_image']
    ],
    "datePublished" => date('c', strtotime($post['published_at'])),
    "dateModified" => date('c', strtotime($post['updated_at'] ?? $post['published_at'])),
    "author" => [
        "@type" => "Person",
        "name" => $post['author_name']
    ],
    "publisher" => [
        "@type" => "Organization",
        "name" => get_setting('site_name', 'EzSafar'),
        "logo" => [
            "@type" => "ImageObject",
            "url" => 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/images/logo.png'
        ]
    ]
];
$schemaJson = json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Schema.org Article Structured Data -->
<script type="application/ld+json">
<?= $schemaJson ?>
</script>

<div class="max-w-7xl mx-auto px-4 md:px-8 py-8 w-full">
    <!-- Breadcrumb SEO Trail -->
    <nav aria-label="Breadcrumb" class="mb-6 flex items-center gap-2 text-xs text-on-surface-variant flex-wrap">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-primary flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">home</span>
            <span>Home</span>
        </a>
        <span>/</span>
        <a href="<?= BASE_URL ?>/blog.php" class="hover:text-primary">Blog &amp; Knowledge Hub</a>
        <span>/</span>
        <?php if (!empty($post['category_name'])): ?>
            <a href="<?= BASE_URL ?>/blog.php?cat=<?= urlencode($post['category_slug']) ?>" class="hover:text-primary">
                <?= htmlspecialchars($post['category_name']) ?>
            </a>
            <span>/</span>
        <?php endif; ?>
        <span class="text-on-surface font-bold truncate max-w-xs sm:max-w-md"><?= htmlspecialchars($post['title']) ?></span>
    </nav>

    <!-- Main Article Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <!-- Main Article Content Column -->
        <article class="lg:col-span-8 space-y-8">
            <!-- Article Header -->
            <header class="space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <?php if (!empty($post['category_name'])): ?>
                        <a href="<?= BASE_URL ?>/blog.php?cat=<?= urlencode($post['category_slug']) ?>" class="px-3 py-1 rounded-full bg-primary-fixed text-on-primary-fixed text-xs font-bold hover:bg-primary hover:text-white transition-colors">
                            <?= htmlspecialchars($post['category_name']) ?>
                        </a>
                    <?php endif; ?>
                    <span class="px-2.5 py-0.5 rounded-full bg-surface-container text-on-surface-variant text-xs font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">schedule</span> <?= $post['reading_time'] ?> min read
                    </span>
                    <span class="text-xs text-on-surface-variant">
                        • <?= date('F j, Y', strtotime($post['published_at'])) ?>
                    </span>
                    <span class="text-xs text-on-surface-variant">
                        • <span class="font-semibold text-on-surface"><?= number_format($post['views_count']) ?></span> views
                    </span>
                </div>

                <h1 class="text-2xl sm:text-4xl md:text-5xl font-black text-on-surface tracking-tight leading-tight">
                    <?= htmlspecialchars($post['title']) ?>
                </h1>

                <?php if (!empty($post['excerpt'])): ?>
                    <p class="text-sm sm:text-base text-on-surface-variant leading-relaxed font-medium">
                        <?= htmlspecialchars($post['excerpt']) ?>
                    </p>
                <?php endif; ?>

                <!-- Author Info & Share Bar -->
                <div class="pt-4 border-t border-outline-variant/15 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-primary to-primary-container text-white flex items-center justify-center font-bold text-sm shadow">
                            <?= strtoupper(substr($post['author_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-on-surface"><?= htmlspecialchars($post['author_name']) ?></div>
                            <div class="text-[11px] text-on-surface-variant">Verified Contributor • EzSafar Jind</div>
                        </div>
                    </div>

                    <!-- Social Share Buttons -->
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-semibold text-on-surface-variant mr-1">Share:</span>
                        <!-- WhatsApp -->
                        <a href="https://api.whatsapp.com/send?text=<?= urlencode($post['title'] . ' ' . $canonicalUrl) ?>" target="_blank" rel="noopener" class="p-2 rounded-xl bg-emerald-100 text-emerald-800 hover:bg-emerald-200 transition-colors" title="Share on WhatsApp">
                            <span class="material-symbols-outlined text-sm">chat</span>
                        </a>
                        <!-- Twitter/X -->
                        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($post['title']) ?>&url=<?= urlencode($canonicalUrl) ?>" target="_blank" rel="noopener" class="p-2 rounded-xl bg-slate-100 text-slate-800 hover:bg-slate-200 transition-colors" title="Share on X">
                            <span class="material-symbols-outlined text-sm">share</span>
                        </a>
                        <!-- Copy Link -->
                        <button onclick="copyArticleLink()" id="copy-btn" class="p-2 rounded-xl bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors" title="Copy Article URL">
                            <span class="material-symbols-outlined text-sm" id="copy-icon">link</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Featured Image -->
            <div class="rounded-3xl overflow-hidden shadow-lg border border-outline-variant/15 relative bg-surface-container-low max-h-[460px]">
                <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="w-full h-full object-cover"/>
            </div>

            <!-- Article Body Typography -->
            <div class="prose max-w-none text-on-surface text-sm sm:text-base leading-relaxed space-y-5">
                <style>
                    .article-content h2 { font-size: 1.5rem; font-weight: 800; color: #131b2e; margin-top: 2rem; margin-bottom: 0.75rem; letter-spacing: -0.02em; }
                    .article-content h3 { font-size: 1.25rem; font-weight: 700; color: #131b2e; margin-top: 1.5rem; margin-bottom: 0.5rem; }
                    .article-content h4 { font-size: 1.1rem; font-weight: 700; color: #131b2e; margin-top: 1rem; margin-bottom: 0.5rem; }
                    .article-content p { margin-bottom: 1rem; line-height: 1.75; color: #334155; }
                    .article-content ul, .article-content ol { margin-left: 1.5rem; margin-bottom: 1.25rem; space-y: 0.5rem; }
                    .article-content ul { list-style-type: disc; }
                    .article-content ol { list-style-type: decimal; }
                    .article-content li { margin-bottom: 0.5rem; line-height: 1.6; }
                    .article-content strong { color: #0f172a; font-weight: 700; }
                    .article-content blockquote { border-left: 4px solid #004cb6; padding-left: 1rem; font-style: italic; color: #475569; margin: 1.5rem 0; }
                </style>
                <div class="article-content">
                    <?= $post['content'] ?>
                </div>
            </div>

            <!-- Article Tags & Keywords -->
            <?php if (!empty($post['meta_keywords'])): ?>
                <div class="pt-6 border-t border-outline-variant/15">
                    <div class="text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Topics &amp; Keywords</div>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach (explode(',', $post['meta_keywords']) as $tag): ?>
                            <span class="px-3 py-1 rounded-lg bg-surface-container text-xs font-medium text-on-surface-variant">
                                #<?= htmlspecialchars(trim($tag)) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Prev / Next Navigation -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-6 border-t border-outline-variant/15">
                <?php if ($prevPost): ?>
                    <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($prevPost['slug']) ?>" class="p-4 rounded-2xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/15 transition-all flex flex-col justify-between group">
                        <span class="text-[11px] font-bold text-on-surface-variant flex items-center gap-1 group-hover:text-primary">
                            <span class="material-symbols-outlined text-sm">arrow_back</span> Previous Article
                        </span>
                        <div class="text-xs font-bold text-on-surface mt-1.5 line-clamp-2">
                            <?= htmlspecialchars($prevPost['title']) ?>
                        </div>
                    </a>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>

                <?php if ($nextPost): ?>
                    <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($nextPost['slug']) ?>" class="p-4 rounded-2xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/15 transition-all flex flex-col justify-between text-right group">
                        <span class="text-[11px] font-bold text-on-surface-variant flex items-center justify-end gap-1 group-hover:text-primary">
                            Next Article <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </span>
                        <div class="text-xs font-bold text-on-surface mt-1.5 line-clamp-2">
                            <?= htmlspecialchars($nextPost['title']) ?>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
        </article>

        <!-- Right: Sticky Sidebar with Fleet & Inquiries -->
        <aside class="lg:col-span-4 space-y-6">
            <!-- Author Desk Box -->
            <div class="p-6 rounded-3xl bg-surface-container-lowest border border-outline-variant/15 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-primary to-primary-container text-white flex items-center justify-center font-bold text-base shadow">
                        <?= strtoupper(substr($post['author_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-on-surface"><?= htmlspecialchars($post['author_name']) ?></h4>
                        <p class="text-xs text-on-surface-variant">Automotive Mobility Desk, Jind</p>
                    </div>
                </div>
                <p class="text-xs text-on-surface-variant leading-relaxed">
                    Published by EzSafar's editorial team based at our Jind Mobility Hub. We write practical, grounded guides for car rentals, certified pre-owned purchasing, and travel across Haryana.
                </p>
                <div class="mt-4 pt-3 border-t border-outline-variant/10 flex items-center justify-between text-xs">
                    <span class="text-on-surface-variant font-medium">Have a question?</span>
                    <a href="https://wa.me/<?= PHONE_WHATSAPP ?>?text=Hi%20EzSafar,%20I%20read%20your%20article%20'<?= urlencode($post['title']) ?>'%20and%20wanted%20to%20ask..." target="_blank" rel="noopener" class="text-emerald-700 font-bold hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">chat</span> Ask on WhatsApp
                    </a>
                </div>
            </div>

            <!-- Spotlight Rental Cars Box -->
            <?php if (!empty($sidebarCars)): ?>
                <div class="p-6 rounded-3xl bg-surface-container-lowest border border-outline-variant/15 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-sm text-on-surface">Rent a Car in Jind</h4>
                        <a href="<?= BASE_URL ?>/car-rentals.php" class="text-xs text-primary font-bold hover:underline">View Fleet</a>
                    </div>

                    <div class="space-y-3">
                        <?php foreach ($sidebarCars as $sc): ?>
                            <a href="<?= BASE_URL ?>/vehicle.php?slug=<?= urlencode($sc['slug']) ?>" class="p-3 rounded-2xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/15 flex items-center gap-3 transition-all group block">
                                <img src="<?= htmlspecialchars($sc['image_url']) ?>" alt="" class="w-16 h-12 object-contain rounded-lg bg-surface-container-lowest p-1 shrink-0"/>
                                <div class="overflow-hidden flex-1">
                                    <div class="font-bold text-xs text-on-surface group-hover:text-primary truncate"><?= htmlspecialchars($sc['name']) ?></div>
                                    <div class="text-[11px] text-on-surface-variant"><?= $sc['category'] ?> • Zero Deposit</div>
                                    <div class="text-xs font-black text-primary mt-0.5"><?= format_rupee($sc['price_per_day']) ?>/day</div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Related Articles -->
            <?php if (!empty($relatedPosts)): ?>
                <div class="p-6 rounded-3xl bg-surface-container-lowest border border-outline-variant/15 shadow-sm space-y-4">
                    <h4 class="font-bold text-sm text-on-surface">Related Articles</h4>
                    <div class="space-y-3">
                        <?php foreach ($relatedPosts as $rp): ?>
                            <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($rp['slug']) ?>" class="flex items-center gap-3 group">
                                <img src="<?= htmlspecialchars($rp['featured_image']) ?>" alt="" class="w-16 h-14 object-cover rounded-xl shrink-0 group-hover:opacity-90"/>
                                <div>
                                    <h5 class="text-xs font-bold text-on-surface group-hover:text-primary transition-colors line-clamp-2 leading-snug">
                                        <?= htmlspecialchars($rp['title']) ?>
                                    </h5>
                                    <span class="text-[10px] text-on-surface-variant block mt-1">
                                        <?= $rp['reading_time'] ?> min read • <?= date('M d, Y', strtotime($rp['published_at'])) ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quick Valuation Banner -->
            <div class="p-6 rounded-3xl bg-secondary-container text-white shadow-lg space-y-3">
                <span class="px-2.5 py-0.5 rounded-full bg-black/20 text-white text-[10px] font-bold uppercase tracking-wider">
                    Instant Cash
                </span>
                <h4 class="text-lg font-black leading-snug">Selling your car in Jind or Haryana?</h4>
                <p class="text-xs text-white/90 leading-relaxed">
                    Get best market valuation, instant doorstep inspection, and immediate bank transfer in 30 minutes.
                </p>
                <a href="<?= BASE_URL ?>/sell-your-car.php" class="inline-block w-full text-center py-2.5 rounded-xl bg-white text-secondary font-bold text-xs shadow hover:bg-slate-50 transition-colors">
                    Check Valuation
                </a>
            </div>
        </aside>
    </div>
</div>

<script>
function copyArticleLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        const btn = document.getElementById('copy-btn');
        const icon = document.getElementById('copy-icon');
        icon.innerText = 'check';
        btn.classList.add('bg-emerald-100', 'text-emerald-800');
        setTimeout(() => {
            icon.innerText = 'link';
            btn.classList.remove('bg-emerald-100', 'text-emerald-800');
        }, 2000);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

