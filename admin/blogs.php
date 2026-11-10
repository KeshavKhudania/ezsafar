<?php
require_once __DIR__ . '/auth-check.php';
$db = get_db();

// Handle Actions (POST before header output)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'toggle_status' && $id > 0) {
        $current = (int)($_POST['current_status'] ?? 0);
        $new = $current ? 0 : 1;
        $stmt = $db->prepare("UPDATE blog_posts SET is_published = ? WHERE id = ?");
        $stmt->execute([$new, $id]);
        set_flash('info', "Article publish status updated.");
    } elseif ($action === 'delete' && $id > 0) {
        $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('warning', "Article deleted successfully.");
    }

    header('Location: ' . BASE_URL . '/admin/blogs.php');
    exit;
}

$adminTitle = "Blog & Articles Management";
require_once __DIR__ . '/includes/admin-header.php';

// Stats
$totalCount = (int)$db->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
$publishedCount = (int)$db->query("SELECT COUNT(*) FROM blog_posts WHERE is_published = 1")->fetchColumn();
$draftCount = (int)$db->query("SELECT COUNT(*) FROM blog_posts WHERE is_published = 0")->fetchColumn();
$totalViews = (int)$db->query("SELECT COALESCE(SUM(views_count), 0) FROM blog_posts")->fetchColumn();

// Filters
$catFilter = sanitize($_GET['cat'] ?? '');
$search = sanitize($_GET['q'] ?? '');

$where = ["1=1"];
$params = [];
if (!empty($catFilter)) {
    $where[] = "c.slug = ?";
    $params[] = $catFilter;
}
if (!empty($search)) {
    $where[] = "(p.title LIKE ? OR p.author_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSql = implode(" AND ", $where);
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM blog_posts p 
    LEFT JOIN blog_categories c ON p.category_id = c.id 
    WHERE $whereSql 
    ORDER BY p.id DESC
");
$stmt->execute($params);
$posts = $stmt->fetchAll();

$categories = $db->query("SELECT * FROM blog_categories ORDER BY name ASC")->fetchAll();
?>

<!-- Header & Add Button -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-xl font-bold text-slate-800">Blog &amp; Knowledge Hub (<?= $totalCount ?> Articles)</h3>
        <p class="text-xs text-slate-500">Publish and manage SEO-optimized guides, road trips, and automotive knowledge articles.</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/blog-edit.php" class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold shadow flex items-center gap-1.5 self-start">
        <span class="material-symbols-outlined text-base">post_add</span>
        <span>Write New Article</span>
    </a>
</div>

<!-- Stats Metric Row -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Articles</div>
        <div class="text-2xl font-black text-slate-800 mt-1"><?= $totalCount ?></div>
    </div>
    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
        <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Published</div>
        <div class="text-2xl font-black text-emerald-700 mt-1"><?= $publishedCount ?></div>
    </div>
    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
        <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Drafts</div>
        <div class="text-2xl font-black text-amber-700 mt-1"><?= $draftCount ?></div>
    </div>
    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
        <div class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Total Views</div>
        <div class="text-2xl font-black text-blue-700 mt-1"><?= number_format($totalViews) ?></div>
    </div>
</div>

<!-- Filters Bar -->
<div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
    <form action="" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
        <select name="cat" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-300 font-semibold text-slate-700 focus:outline-none">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= ($catFilter === $cat['slug']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="relative flex-1 sm:w-64">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search article title or author..." class="w-full px-3 py-2 rounded-xl border border-slate-300 font-medium text-slate-700 focus:outline-none"/>
        </div>

        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900">
            Filter
        </button>

        <?php if (!empty($catFilter) || !empty($search)): ?>
            <a href="<?= BASE_URL ?>/admin/blogs.php" class="px-3 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold hover:bg-slate-200">
                Clear
            </a>
        <?php endif; ?>
    </form>

    <div class="text-slate-400 font-semibold self-end md:self-center">
        Showing <?= count($posts) ?> of <?= $totalCount ?> posts
    </div>
</div>

<!-- Articles Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                <tr>
                    <th class="p-4">Article</th>
                    <th class="p-4">Category</th>
                    <th class="p-4">Author &amp; Read Time</th>
                    <th class="p-4">Views</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Published Date</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400 font-medium">
                            No articles found. <a href="<?= BASE_URL ?>/admin/blog-edit.php" class="text-primary font-bold hover:underline">Write the first article</a>.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($posts as $p): ?>
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="p-4">
                            <div class="flex items-center gap-3 max-w-md">
                                <img src="<?= htmlspecialchars($p['featured_image']) ?>" alt="" class="w-16 h-12 object-cover rounded-lg bg-slate-100 shrink-0"/>
                                <div class="overflow-hidden">
                                    <div class="font-bold text-slate-800 text-sm line-clamp-1"><?= htmlspecialchars($p['title']) ?></div>
                                    <div class="text-[11px] text-slate-400 truncate">ID: #<?= $p['id'] ?> • <?= htmlspecialchars($p['slug']) ?></div>
                                    <a href="../blog-post.php?slug=<?= urlencode($p['slug']) ?>" target="_blank" class="text-primary hover:underline text-[10px] font-semibold inline-flex items-center gap-0.5 mt-0.5">
                                        <span>View on site</span>
                                        <span class="material-symbols-outlined text-[11px]">open_in_new</span>
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px]">
                                <?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-700"><?= htmlspecialchars($p['author_name']) ?></div>
                            <div class="text-[11px] text-slate-400"><?= $p['reading_time'] ?> min read</div>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 font-bold text-[11px]">
                                <?= number_format($p['views_count']) ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <form action="" method="POST" class="inline">
                                <input type="hidden" name="action" value="toggle_status"/>
                                <input type="hidden" name="id" value="<?= $p['id'] ?>"/>
                                <input type="hidden" name="current_status" value="<?= $p['is_published'] ?>"/>
                                <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold cursor-pointer transition-all <?= $p['is_published'] ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-amber-100 text-amber-800 hover:bg-amber-200' ?>" title="Click to toggle publish status">
                                    <?= $p['is_published'] ? '● Published' : '○ Draft' ?>
                                </button>
                            </form>
                        </td>
                        <td class="p-4 text-slate-500 font-medium">
                            <?= date('M d, Y', strtotime($p['published_at'])) ?>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="<?= BASE_URL ?>/admin/blog-edit.php?id=<?= $p['id'] ?>" class="p-1.5 rounded-lg text-slate-500 hover:text-primary hover:bg-slate-100" title="Edit Article">
                                    <span class="material-symbols-outlined text-base">edit</span>
                                </a>
                                <form action="" method="POST" onsubmit="return confirm('Permanently delete this article?')" class="inline">
                                    <input type="hidden" name="action" value="delete"/>
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>"/>
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Delete Article">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>

