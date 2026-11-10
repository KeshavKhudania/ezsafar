<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$db = get_db();

// Filter parameters
$search = sanitize($_GET['q'] ?? '');
$categorySlug = sanitize($_GET['cat'] ?? '');

$pageTitle = "EzSafar Blog | Car Rental Guides & Used Car Tips in Jind, Haryana";
$pageDesc = "Expert automotive articles, self-drive car rental tips, 140-point inspection checklists, Haryana RTO guides, and scenic road trips from Jind.";
$pageKeywords = "ezsafar blog, car rental guide jind, used car buying tips haryana, rc transfer jind, self drive road trips";

// Fetch categories with active post count
$categories = $db->query("
    SELECT c.*, COUNT(p.id) as posts_count 
    FROM blog_categories c 
    LEFT JOIN blog_posts p ON c.id = p.category_id AND p.is_published = 1
    GROUP BY c.id 
    ORDER BY c.name ASC
")->fetchAll();

// Build query for posts
$where = ["p.is_published = 1"];
$params = [];

if (!empty($categorySlug)) {
    $where[] = "c.slug = ?";
    $params[] = $categorySlug;
}

if (!empty($search)) {
    $where[] = "(p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSql = implode(" AND ", $where);
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM blog_posts p
    LEFT JOIN blog_categories c ON p.category_id = c.id
    WHERE $whereSql
    ORDER BY p.published_at DESC
");
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Featured post (first post if on main page without search)
$featuredPost = null;
if (empty($search) && empty($categorySlug) && !empty($posts)) {
    $featuredPost = $posts[0];
    $gridPosts = array_slice($posts, 1);
} else {
    $gridPosts = $posts;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 md:px-8 py-10 w-full">
    <!-- Breadcrumb Trail -->
    <nav aria-label="Breadcrumb" class="mb-6 flex items-center gap-2 text-xs text-on-surface-variant">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-primary flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">home</span>
            <span>Home</span>
        </a>
        <span>/</span>
        <span class="text-on-surface font-bold">Blog &amp; Knowledge Hub</span>
    </nav>

    <!-- Page Header & Overview -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-8 border-b border-outline-variant/15">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-fixed text-on-primary-fixed text-xs font-bold mb-3">
                <span class="material-symbols-outlined text-sm">menu_book</span> AUTOMOTIVE INSIGHTS &amp; TRAVEL GUIDES
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-on-surface tracking-tight">
                EzSafar Knowledge Hub
            </h1>
            <p class="text-sm text-on-surface-variant mt-2 max-w-2xl">
                Actionable advice on self-drive car rentals, 140-point pre-owned inspection tips, Haryana RTO procedures, and curated road trip routes from Jind.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-on-surface-variant">Articles Published:</span>
            <span class="px-3.5 py-1 rounded-full bg-surface-container font-bold text-sm text-primary"><?= count($posts) ?> Articles</span>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="my-8 p-4 sm:p-5 rounded-3xl bg-surface-container-low border border-outline-variant/15 flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Category Pills -->
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <a href="<?= BASE_URL ?>/blog.php" class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= empty($categorySlug) ? 'bg-primary text-white shadow' : 'bg-surface-container-lowest text-on-surface-variant hover:text-on-surface' ?>">
                All Articles
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= BASE_URL ?>/blog.php?cat=<?= urlencode($cat['slug']) ?>" class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= ($categorySlug === $cat['slug']) ? 'bg-primary text-white shadow' : 'bg-surface-container-lowest text-on-surface-variant hover:text-on-surface' ?>">
                    <?= htmlspecialchars($cat['name']) ?> (<?= $cat['posts_count'] ?>)
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Search Form -->
        <form action="<?= BASE_URL ?>/blog.php" method="GET" class="flex items-center gap-2 w-full md:w-auto">
            <?php if (!empty($categorySlug)): ?>
                <input type="hidden" name="cat" value="<?= htmlspecialchars($categorySlug) ?>"/>
            <?php endif; ?>
            <div class="relative w-full md:w-64">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-outline text-base">search</span>
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search guides, RTO, routes..." class="w-full pl-9 pr-4 py-2 rounded-xl bg-surface-container-lowest border border-outline-variant/20 text-xs text-on-surface focus:outline-none focus:border-primary"/>
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold shadow hover:bg-primary-container">
                Search
            </button>
            <?php if (!empty($search) || !empty($categorySlug)): ?>
                <a href="<?= BASE_URL ?>/blog.php" class="p-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface-variant text-xs font-bold" title="Reset Search">
                    <span class="material-symbols-outlined text-sm">restart_alt</span>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- FEATURED POST BANNER (Only on unfiltered main view) -->
    <?php if ($featuredPost): ?>
        <div class="mb-12 rounded-3xl bg-surface-container-lowest border border-outline-variant/15 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 grid grid-cols-1 lg:grid-cols-12 group">
            <div class="lg:col-span-7 relative h-72 sm:h-96 bg-surface-container-low overflow-hidden">
                <img src="<?= htmlspecialchars($featuredPost['featured_image']) ?>" alt="<?= htmlspecialchars($featuredPost['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"/>
                <div class="absolute top-4 left-4 flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full bg-secondary-container text-white text-xs font-bold shadow">
                        ★ FEATURED GUIDE
                    </span>
                    <span class="px-3 py-1 rounded-full bg-surface-container-lowest/90 backdrop-blur-md text-xs font-bold text-on-surface shadow">
                        <?= htmlspecialchars($featuredPost['category_name'] ?? 'General') ?>
                    </span>
                </div>
            </div>
            <div class="lg:col-span-5 p-6 sm:p-10 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 text-xs text-on-surface-variant mb-3">
                        <span class="flex items-center gap-1 font-semibold text-primary">
                            <span class="material-symbols-outlined text-[15px]">schedule</span> <?= $featuredPost['reading_time'] ?> min read
                        </span>
                        <span>•</span>
                        <span><?= date('M d, Y', strtotime($featuredPost['published_at'])) ?></span>
                        <span>•</span>
                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">visibility</span> <?= number_format($featuredPost['views_count']) ?> views</span>
                    </div>

                    <h2 class="text-xl sm:text-3xl font-black text-on-surface leading-snug group-hover:text-primary transition-colors">
                        <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($featuredPost['slug']) ?>">
                            <?= htmlspecialchars($featuredPost['title']) ?>
                        </a>
                    </h2>

                    <p class="text-xs sm:text-sm text-on-surface-variant mt-3 line-clamp-3 leading-relaxed">
                        <?= htmlspecialchars($featuredPost['excerpt']) ?>
                    </p>
                </div>

                <div class="mt-6 pt-5 border-t border-outline-variant/15 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
                            <?= strtoupper(substr($featuredPost['author_name'] ?? 'E', 0, 1)) ?>
                        </div>
                        <span class="text-xs font-bold text-on-surface"><?= htmlspecialchars($featuredPost['author_name']) ?></span>
                    </div>

                    <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($featuredPost['slug']) ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow hover:bg-primary-container transition-all">
                        <span>Read Full Guide</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ARTICLES GRID -->
    <?php if (empty($gridPosts) && !$featuredPost): ?>
        <div class="py-20 text-center rounded-3xl bg-surface-container-lowest border border-outline-variant/15 p-8">
            <span class="material-symbols-outlined text-6xl text-outline">search_off</span>
            <h3 class="text-xl font-bold mt-3 text-on-surface">No articles found matching your criteria</h3>
            <p class="text-xs sm:text-sm text-on-surface-variant mt-1">Try adjusting your keywords or clearing the category filter.</p>
            <a href="<?= BASE_URL ?>/blog.php" class="inline-block mt-5 px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow">
                Clear Filters &amp; View All
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($gridPosts as $post): ?>
                <article class="rounded-3xl bg-surface-container-lowest border border-outline-variant/15 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                    <div>
                        <!-- Thumbnail -->
                        <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($post['slug']) ?>" class="relative block w-full h-52 bg-surface-container-low overflow-hidden">
                            <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                            <span class="absolute top-3 left-3 bg-surface-container-lowest/90 backdrop-blur-md px-2.5 py-1 rounded-full text-[10px] font-bold text-primary shadow">
                                <?= htmlspecialchars($post['category_name'] ?? 'Automotive') ?>
                            </span>
                            <span class="absolute bottom-3 right-3 bg-black/60 backdrop-blur-md text-white px-2 py-0.5 rounded-lg text-[10px] font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">schedule</span> <?= $post['reading_time'] ?> min read
                            </span>
                        </a>

                        <!-- Body -->
                        <div class="p-6">
                            <div class="flex items-center gap-2 text-[11px] text-on-surface-variant mb-2">
                                <span><?= date('M d, Y', strtotime($post['published_at'])) ?></span>
                                <span>•</span>
                                <span><?= number_format($post['views_count']) ?> views</span>
                            </div>

                            <h3 class="text-base sm:text-lg font-bold text-on-surface leading-snug group-hover:text-primary transition-colors">
                                <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($post['slug']) ?>">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </h3>

                            <p class="text-xs text-on-surface-variant mt-2.5 line-clamp-3 leading-relaxed">
                                <?= htmlspecialchars($post['excerpt']) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-6 pt-0">
                        <div class="pt-4 border-t border-outline-variant/15 flex items-center justify-between">
                            <span class="text-xs font-semibold text-on-surface-variant">
                                By <?= htmlspecialchars($post['author_name']) ?>
                            </span>
                            <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($post['slug']) ?>" class="inline-flex items-center gap-1 text-primary text-xs font-bold hover:underline">
                                <span>Read</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Bottom Newsletter / Mobility CTA -->
    <div class="mt-16 p-8 sm:p-12 rounded-3xl bg-gradient-to-r from-primary to-primary-container text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="px-3 py-1 rounded-full bg-white/20 text-white text-[11px] font-bold uppercase tracking-wider backdrop-blur-md">
                Need Wheels in Jind?
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold mt-3 tracking-tight">
                Ready to Experience Friction-Free Mobility?
            </h2>
            <p class="text-xs sm:text-sm text-white/90 mt-2 leading-relaxed">
                Rent a commercial sanitized self-drive car with zero deposit, or buy a 140-point certified pre-owned car with a 1-year warranty right here in Jind, Haryana.
            </p>
            <div class="mt-6 flex flex-wrap items-center gap-3">
                <a href="<?= BASE_URL ?>/car-rentals.php" class="px-5 py-3 rounded-xl bg-white text-primary text-xs font-bold shadow hover:bg-slate-50 transition-all">
                    Explore Rental Fleet
                </a>
                <a href="<?= BASE_URL ?>/buy-cars.php" class="px-5 py-3 rounded-xl bg-secondary-container hover:bg-secondary text-white text-xs font-bold shadow transition-all">
                    Browse Certified Used Cars
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

