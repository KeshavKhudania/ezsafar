<?php
require_once __DIR__ . '/auth-check.php';
$db = get_db();

$id = (int)($_GET['id'] ?? 0);
$isEditing = ($id > 0);

// Handle POST Save Action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int)($_POST['id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $catId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    // Allow standard HTML formatting in blog content
    $content = trim($_POST['content'] ?? '');
    $featuredImg = sanitize($_POST['featured_image'] ?? '');
    $author = sanitize($_POST['author_name'] ?? 'EzSafar Editorial Team');
    $isPub = isset($_POST['is_published']) ? 1 : 0;

    // Calculate reading time (~200 words per minute)
    $words = str_word_count(strip_tags($content));
    $readingTime = max(1, (int)ceil($words / 200));

    // SEO URL Slug
    $slug = slugify(sanitize($_POST['slug'] ?? ''));
    if (empty($slug)) {
        $slug = slugify($title);
    }
    // Check uniqueness
    $checkSql = "SELECT COUNT(*) FROM blog_posts WHERE slug = ?" . ($postId > 0 ? " AND id != ?" : "");
    $checkParams = ($postId > 0) ? [$slug, $postId] : [$slug];
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute($checkParams);
    if ($checkStmt->fetchColumn() > 0) {
        $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
    }

    // SEO Meta Tags
    $metaTitle = sanitize($_POST['meta_title'] ?? '');
    if (empty($metaTitle)) {
        $metaTitle = $title . " | EzSafar Blog";
    }
    $metaDesc = sanitize($_POST['meta_description'] ?? '');
    if (empty($metaDesc)) {
        $metaDesc = !empty($excerpt) ? $excerpt : substr(strip_tags($content), 0, 155);
    }
    $metaKeywords = sanitize($_POST['meta_keywords'] ?? '');

    if (!empty($title) && !empty($content) && !empty($featuredImg)) {
        if ($postId > 0) {
            $stmt = $db->prepare("UPDATE blog_posts SET 
                category_id=?, title=?, slug=?, excerpt=?, content=?, featured_image=?, 
                author_name=?, reading_time=?, is_published=?, 
                meta_title=?, meta_description=?, meta_keywords=? 
                WHERE id=?");
            $stmt->execute([
                $catId, $title, $slug, $excerpt, $content, $featuredImg,
                $author, $readingTime, $isPub,
                $metaTitle, $metaDesc, $metaKeywords, $postId
            ]);
            set_flash('success', "Article '$title' updated successfully.");
        } else {
            $stmt = $db->prepare("INSERT INTO blog_posts 
                (category_id, title, slug, excerpt, content, featured_image, author_name, reading_time, is_published, meta_title, meta_description, meta_keywords, published_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $catId, $title, $slug, $excerpt, $content, $featuredImg,
                $author, $readingTime, $isPub,
                $metaTitle, $metaDesc, $metaKeywords
            ]);
            set_flash('success', "New article '$title' published successfully.");
        }

        header('Location: ' . BASE_URL . '/admin/blogs.php');
        exit;
    } else {
        set_flash('error', "Title, Featured Image, and Article Content are required.");
    }
}

// Fetch existing article if editing
$post = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'category_id' => '',
    'excerpt' => '',
    'content' => '',
    'featured_image' => '',
    'author_name' => $_SESSION['admin_full_name'] ?? 'EzSafar Editorial Team',
    'reading_time' => 5,
    'is_published' => 1,
    'meta_title' => '',
    'meta_description' => '',
    'meta_keywords' => ''
];

if ($isEditing) {
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if ($existing) {
        $post = $existing;
    } else {
        set_flash('error', "Article not found.");
        header('Location: ' . BASE_URL . '/admin/blogs.php');
        exit;
    }
}

$categories = $db->query("SELECT * FROM blog_categories ORDER BY name ASC")->fetchAll();

$adminTitle = $isEditing ? "Edit Article: " . $post['title'] : "Write New Article";
require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Header & Back Button -->
<div class="flex items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= BASE_URL ?>/admin/blogs.php" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 shadow-sm" title="Back to Articles">
            <span class="material-symbols-outlined text-base">arrow_back</span>
        </a>
        <div>
            <h3 class="text-xl font-bold text-slate-800"><?= $isEditing ? "Edit Article #$id" : "Create New Knowledge Article" ?></h3>
            <p class="text-xs text-slate-500">Author comprehensive guides and travel content with live SEO optimization.</p>
        </div>
    </div>
    <?php if ($isEditing && !empty($post['slug'])): ?>
        <a href="<?= BASE_URL ?>/blog-post.php?slug=<?= urlencode($post['slug']) ?>" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1 shadow-sm">
            <span>Preview Article</span>
            <span class="material-symbols-outlined text-sm">open_in_new</span>
        </a>
    <?php endif; ?>
</div>

<form action="" method="POST" class="space-y-6">
    <input type="hidden" name="id" value="<?= $post['id'] ?>"/>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Main Content (Left 8 cols) -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Title & Excerpt Box -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase text-xs mb-1">Article Headline / Title *</label>
                    <input type="text" name="title" id="blog-title" required value="<?= htmlspecialchars($post['title']) ?>" placeholder="e.g. 5 Best Self-Drive Weekend Getaways from Jind" class="w-full px-4 py-3 rounded-xl border border-slate-300 text-base font-bold text-slate-800 focus:outline-none focus:border-primary" oninput="updateSlugAndPreview()"/>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase text-[11px] mb-1">SEO URL Slug</label>
                        <input type="text" name="slug" id="blog-slug" value="<?= htmlspecialchars($post['slug']) ?>" placeholder="e.g. 5-best-self-drive-weekend-getaways-jind" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-slate-700 focus:outline-none focus:border-primary"/>
                        <p class="text-[10px] text-slate-400 mt-1">Leave empty to auto-generate from headline.</p>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 uppercase text-[11px] mb-1">Category</label>
                        <select name="category_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 focus:outline-none">
                            <option value="">Uncategorized</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($post['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase text-[11px] mb-1">Short Summary / Excerpt *</label>
                    <textarea name="excerpt" id="blog-excerpt" rows="2" required placeholder="A brief 1-2 sentence hook for search engines and article cards..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium text-slate-700 focus:outline-none focus:border-primary" oninput="updateSerpPreview()"><?= htmlspecialchars($post['excerpt']) ?></textarea>
                </div>
            </div>

            <!-- Content Editor Box -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <label class="font-bold text-slate-800 text-sm">Full Article Body *</label>
                    <!-- Quick Format Insert Buttons -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-[10px] font-bold text-slate-400 uppercase mr-1">Quick Insert:</span>
                        <button type="button" onclick="insertHtml('<h2>', '</h2>')" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-[11px] font-bold text-slate-700">H2</button>
                        <button type="button" onclick="insertHtml('<h3>', '</h3>')" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-[11px] font-bold text-slate-700">H3</button>
                        <button type="button" onclick="insertHtml('<p>', '</p>')" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-[11px] font-bold text-slate-700">Para</button>
                        <button type="button" onclick="insertHtml('<strong>', '</strong>')" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-[11px] font-bold text-slate-700">Bold</button>
                        <button type="button" onclick="insertHtml('<ul>\n  <li>', '</li>\n</ul>')" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-[11px] font-bold text-slate-700">Bullet List</button>
                        <button type="button" onclick="insertCallout()" class="px-2 py-1 rounded bg-blue-50 hover:bg-blue-100 text-[11px] font-bold text-blue-800">Callout Box</button>
                    </div>
                </div>

                <textarea name="content" id="blog-content" rows="18" required placeholder="Write your article in clean HTML (<p>, <h2>, <ul>, etc.)..." class="w-full px-4 py-3 rounded-2xl border border-slate-300 text-xs font-mono leading-relaxed text-slate-800 focus:outline-none focus:border-primary"><?= htmlspecialchars($post['content']) ?></textarea>
                <p class="text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Supports semantic HTML tags: &lt;h2&gt;, &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;blockquote&gt;</span>
                    <span id="word-count-badge" class="font-bold text-slate-600">0 words</span>
                </p>
            </div>

            <!-- SEO & OpenGraph Configuration -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center gap-2 text-slate-800 font-bold">
                    <span class="material-symbols-outlined text-primary text-lg">travel_explore</span>
                    <span>Search Engine Optimization (SEO) &amp; Social Tags</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase text-[11px] mb-1">Custom Meta Title</label>
                    <input type="text" name="meta_title" id="meta-title" value="<?= htmlspecialchars($post['meta_title']) ?>" placeholder="Leave blank to use Article Headline + Brand" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary" oninput="updateSerpPreview()"/>
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase text-[11px] mb-1">Custom Meta Description</label>
                    <textarea name="meta_description" id="meta-desc" rows="2" placeholder="Leave blank to use Article Excerpt" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary" oninput="updateSerpPreview()"><?= htmlspecialchars($post['meta_description']) ?></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase text-[11px] mb-1">Target Keywords (Comma Separated)</label>
                    <input type="text" name="meta_keywords" value="<?= htmlspecialchars($post['meta_keywords']) ?>" placeholder="e.g. self drive car jind, car rental tips, road trip routes haryana" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary"/>
                </div>

                <!-- Live SERP Preview -->
                <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Google Search Live Preview</div>
                    <div class="font-sans">
                        <div class="text-[11px] text-slate-600 truncate flex items-center gap-1">
                            <span>https://ezsafar.in</span>
                            <span>›</span>
                            <span>blog</span>
                            <span>›</span>
                            <span id="serp-slug" class="text-slate-500 font-mono"><?= $post['slug'] ?: 'article-slug' ?></span>
                        </div>
                        <div id="serp-title" class="text-sm font-bold text-blue-700 hover:underline mt-0.5 truncate">
                            <?= htmlspecialchars($post['meta_title'] ?: ($post['title'] ?: 'Article Title | EzSafar Blog')) ?>
                        </div>
                        <div id="serp-desc" class="text-xs text-slate-600 mt-1 line-clamp-2">
                            <?= htmlspecialchars($post['meta_description'] ?: ($post['excerpt'] ?: 'Article excerpt and description will appear here as search snippets...')) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Publishing Controls Sidebar (Right 4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Publishing Actions Box -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <h4 class="font-bold text-slate-800 text-sm pb-3 border-b border-slate-100 flex items-center justify-between">
                    <span>Publishing Status</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $post['is_published'] ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                        <?= $post['is_published'] ? 'Published' : 'Draft' ?>
                    </span>
                </h4>

                <div class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <input type="checkbox" name="is_published" id="is_published" value="1" <?= $post['is_published'] ? 'checked' : '' ?> class="w-4 h-4 text-primary rounded"/>
                    <label for="is_published" class="font-bold text-slate-700 text-xs cursor-pointer">
                        Live / Published on Website
                    </label>
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase text-[11px] mb-1">Author Name</label>
                    <input type="text" name="author_name" value="<?= htmlspecialchars($post['author_name']) ?>" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none"/>
                </div>

                <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                    <button type="submit" class="w-full py-3 rounded-xl bg-primary hover:bg-primary-container text-white font-bold text-xs shadow-md flex items-center justify-center gap-1.5 transition-all">
                        <span class="material-symbols-outlined text-base">save</span>
                        <span><?= $isEditing ? 'Save Article Changes' : 'Publish Article Now' ?></span>
                    </button>
                    <a href="<?= BASE_URL ?>/admin/blogs.php" class="w-full py-2.5 text-center rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50">
                        Cancel
                    </a>
                </div>
            </div>

            <!-- Featured Image Box -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-3">
                <h4 class="font-bold text-slate-800 text-sm pb-2 border-b border-slate-100">Featured Image *</h4>
                
                <div>
                    <label class="block font-bold text-slate-600 uppercase text-[11px] mb-1">Image URL</label>
                    <input type="url" name="featured_image" id="featured-image-input" required value="<?= htmlspecialchars($post['featured_image']) ?>" placeholder="https://images.unsplash.com/..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-slate-800 focus:outline-none focus:border-primary" oninput="updateImagePreview(this.value)"/>
                </div>

                <div class="w-full h-44 rounded-2xl bg-slate-100 border border-dashed border-slate-300 overflow-hidden flex items-center justify-center relative">
                    <img id="image-preview" src="<?= htmlspecialchars($post['featured_image']) ?>" alt="Preview" class="<?= empty($post['featured_image']) ? 'hidden' : '' ?> w-full h-full object-cover"/>
                    <div id="image-placeholder" class="<?= !empty($post['featured_image']) ? 'hidden' : '' ?> text-center p-4 text-slate-400">
                        <span class="material-symbols-outlined text-3xl">image</span>
                        <div class="text-[11px] mt-1 font-semibold">Image preview appears here</div>
                    </div>
                </div>

                <!-- Quick Presets -->
                <div class="pt-2">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Free Auto Photography Presets:</div>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="setImage('https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=1200&q=80')" class="px-2 py-1 rounded-lg bg-slate-100 text-[10px] font-semibold text-slate-700 hover:bg-slate-200">Swift/Car</button>
                        <button type="button" onclick="setImage('https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=80')" class="px-2 py-1 rounded-lg bg-slate-100 text-[10px] font-semibold text-slate-700 hover:bg-slate-200">Sedan</button>
                        <button type="button" onclick="setImage('https://images.unsplash.com/photo-1506015391300-4802dc74de2e?auto=format&fit=crop&w=1200&q=80')" class="px-2 py-1 rounded-lg bg-slate-100 text-[10px] font-semibold text-slate-700 hover:bg-slate-200">Highway Route</button>
                        <button type="button" onclick="setImage('https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?auto=format&fit=crop&w=1200&q=80')" class="px-2 py-1 rounded-lg bg-slate-100 text-[10px] font-semibold text-slate-700 hover:bg-slate-200">Steering Wheel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function slugifyJs(text) {
    return text.toString().toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
}

function updateSlugAndPreview() {
    const title = document.getElementById('blog-title').value;
    const slugInput = document.getElementById('blog-slug');
    if (!slugInput.dataset.manual) {
        slugInput.value = slugifyJs(title);
    }
    updateSerpPreview();
}

document.getElementById('blog-slug').addEventListener('input', function() {
    this.dataset.manual = "true";
    updateSerpPreview();
});

function updateSerpPreview() {
    const title = document.getElementById('meta-title').value || document.getElementById('blog-title').value || 'Article Title | EzSafar Blog';
    const excerpt = document.getElementById('meta-desc').value || document.getElementById('blog-excerpt').value || 'Article summary preview...';
    const slug = document.getElementById('blog-slug').value || 'article-slug';

    document.getElementById('serp-title').innerText = title;
    document.getElementById('serp-desc').innerText = excerpt;
    document.getElementById('serp-slug').innerText = slug;
}

function updateImagePreview(url) {
    const img = document.getElementById('image-preview');
    const placeholder = document.getElementById('image-placeholder');
    if (url && url.trim().length > 5) {
        img.src = url;
        img.classList.remove('hidden');
        placeholder.classList.add('hidden');
    } else {
        img.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }
}

function setImage(url) {
    document.getElementById('featured-image-input').value = url;
    updateImagePreview(url);
}

function insertHtml(openTag, closeTag) {
    const textarea = document.getElementById('blog-content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selText = textarea.value.substring(start, end) || 'Sample Text';
    const replacement = openTag + selText + closeTag;
    textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + openTag.length, start + openTag.length + selText.length);
    calcWords();
}

function insertCallout() {
    const callout = '<div class="my-6 p-5 rounded-2xl bg-blue-50 border-l-4 border-blue-600 text-blue-950">\n    <h4 class="font-bold text-base mb-1">Key Takeaway</h4>\n    <p class="text-sm">Enter essential advice or tip here...</p>\n</div>\n';
    insertHtml(callout, '');
}

function calcWords() {
    const text = document.getElementById('blog-content').value.replace(/<[^>]*>/g, ' ');
    const count = text.trim() ? text.trim().split(/\s+/).length : 0;
    document.getElementById('word-count-badge').innerText = count + ' words';
}

document.getElementById('blog-content').addEventListener('input', calcWords);
calcWords();
updateSerpPreview();
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>

