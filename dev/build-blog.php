<?php
if (!in_array(PHP_SAPI, ['cli', 'cli-server'], true)) { http_response_code(404); exit; } // a tool for Terminal (and the local test server), never a web page on the host
// Builds the blog from content/blog/*.md into public_html/blog/ (and the top-level copy Hostinger serves).
//
//   php dev/build-blog.php                 publish: every article with "status: published"
//   php dev/build-blog.php --preview DIR   every article, drafts too, into DIR (for the owner's review)
//
// Each article is Markdown with a header block:
//   ---
//   title: Why is my Mac slow?            (the page's headline; the search title adds " · Bondi blog")
//   description: …                        (search result description, ~150 characters)
//   date: 2026-10-03                      (first published; updated: for later edits)
//   status: draft | published
//   image: /assets/features/why.png       (optional: shown at the top and in link previews)
//   related: slug-one, slug-two           (optional: "Keep reading" links)
//   order: 1                              (optional: position among articles published the same day)
//   ---
// Numbers in articles come from real readings of the owner's Mac (CLAUDE.md, "Real numbers only").
//
// Publishing also writes /blog/ (the index), /blog/rss.xml, the blog part of sitemap.xml and of llms.txt, and the
// "Ask AI about Bondi" row (dev/ask-ai.php --print-row).

error_reporting(E_ALL & ~E_DEPRECATED);
require __DIR__ . '/vendor/Parsedown.php';

$root = dirname(__DIR__);
$preview = ($argv[1] ?? '') === '--preview' ? rtrim((string)($argv[2] ?? ''), '/') : null;
if (($argv[1] ?? '') === '--preview' && $preview === '') {
    fwrite(STDERR, "Usage: php dev/build-blog.php --preview DIR\n");
    exit(1);
}
const SITE = 'https://trybondi.app';
const AUTHOR = 'Noaman Khalil';
const AUTHOR_URL = 'https://x.com/khalilnoaman';
$h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

// MARK: Read the articles

$posts = [];
foreach (glob("$root/content/blog/*.md") as $file) {
    $raw = (string)file_get_contents($file);
    if (!preg_match('/\A---\n(.*?)\n---\n(.*)\z/s', $raw, $m)) {
        fwrite(STDERR, "No header block in $file\n");
        exit(1);
    }
    $meta = [];
    foreach (explode("\n", $m[1]) as $line) {
        if (preg_match('/^([a-z]+):\s*(.*)$/', $line, $kv)) {
            $meta[$kv[1]] = preg_replace('/^"(.*)"$/', '$1', trim($kv[2])); // a value may be in quotes
        }
    }
    foreach (['title', 'description', 'date', 'status'] as $need) {
        if (($meta[$need] ?? '') === '') {
            fwrite(STDERR, "$file needs \"$need:\"\n");
            exit(1);
        }
    }
    $slug = basename($file, '.md');
    $words = str_word_count(strip_tags($m[2]));
    $posts[$slug] = array_merge($meta, [
        'slug' => $slug,
        'markdown' => $m[2],
        'minutes' => max(1, (int)round($words / 220)),
        'updated' => $meta['updated'] ?? $meta['date'],
        'related' => array_values(array_filter(array_map('trim', explode(',', $meta['related'] ?? '')))),
    ]);
}
$live = array_filter($posts, fn($p) => $p['status'] === 'published');
$shown = $preview !== null ? $posts : $live;
uasort($shown, fn($a, $b) => strcmp($b['date'], $a['date']) ?: ((int)($a['order'] ?? 99) <=> (int)($b['order'] ?? 99)) ?: strcmp($a['title'], $b['title']));

$askRow = trim((string)shell_exec('php ' . escapeshellarg(__DIR__ . '/ask-ai.php') . ' --print-row'));

// MARK: Pages

/** Markdown to plain text, for structured data: links keep their words, emphasis and code marks go. */
function plain(string $md): string
{
    $t = preg_replace('/!\[[^\]]*\]\([^)]*\)/', '', $md);
    $t = preg_replace('/\[([^\]]+)\]\([^)]*\)/', '$1', $t);
    $t = str_replace(['**', '`'], '', $t);
    return trim(preg_replace('/\s+/', ' ', $t));
}

/** The article's "## Questions" section as [question, answer] pairs (each question is a ### heading). */
function questions(string $md): array
{
    if (!preg_match('/^## Questions\s*$(.*?)(?=^## |\z)/ms', $md, $m)) {
        return [];
    }
    preg_match_all('/^### (.+?)\s*$\n(.*?)(?=^### |\z)/ms', $m[1], $qs, PREG_SET_ORDER);
    return array_map(fn($q) => [plain($q[1]), plain($q[2])], $qs);
}

/** The article as clean Markdown with full addresses, for AI assistants and other tools (/blog/<slug>.md). */
function markdown_copy(array $post): string
{
    $md = preg_replace('~\]\(/~', '](' . SITE . '/', $post['markdown']);
    $dates = 'Published ' . nice_date($post['date']) . ($post['updated'] !== $post['date'] ? ', updated ' . nice_date($post['updated']) : '');
    return "# {$post['title']}\n\n> {$post['description']}\n\nBy " . AUTHOR . ", maker of Bondi. $dates.\nWeb page: " . SITE . "/blog/{$post['slug']}/\n\n"
         . trim($md) . "\n\n---\nBondi is a Mac app that tells you why your Mac is slow in one plain sentence, written on the Mac by an on-device AI: " . SITE . "/\n";
}

function nice_date(string $ymd): string
{
    return date('F j, Y', strtotime($ymd . ' 12:00 UTC'));
}

function page(string $title, string $description, string $canonical, string $body, string $extraHead, string $askRow): string
{
    $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $toggle = '<button class="theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark mode"><svg class="moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5a8.5 8.5 0 1 0 10.7 10.7z"/></svg><svg class="sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4.2"/><path d="M12 2.5v2.2M12 19.3v2.2M2.5 12h2.2M19.3 12h2.2M5.3 5.3l1.6 1.6M17.1 17.1l1.6 1.6M5.3 18.7l1.6-1.6M17.1 6.9l1.6-1.6"/></svg></button>';
    $made = '<span class="made"><span>Made with <span class="heart" role="img" aria-label="love">♥</span> by <a href="' . AUTHOR_URL . '" target="_blank" rel="me noopener">' . AUTHOR . '</a></span></span>';
    return <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{$h($title)}</title>
<meta name="description" content="{$h($description)}">
<link rel="canonical" href="{$h($canonical)}">
<link rel="icon" type="image/png" href="/assets/favicon.png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="alternate" type="application/rss+xml" title="Bondi blog" href="/blog/rss.xml">
<meta property="og:site_name" content="Bondi">
<meta property="og:url" content="{$h($canonical)}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:creator" content="@khalilnoaman">
$extraHead
<script src="/assets/theme.js"></script>
<link rel="stylesheet" href="/assets/blog.css">
</head>
<body>
<header class="nav"><div class="nav-inner">
  <a class="brand" href="/"><img src="/assets/icon-blue.png" alt="" width="26" height="26">Bondi</a>
  <nav class="links" aria-label="Site"><a href="/blog/">Blog</a><a href="/">What's Bondi?</a></nav>
  <div class="nav-right">$toggle<a class="pillbtn primary small" href="/beta/">Join the beta</a></div>
</div></header>
<main>
$body
</main>
<footer>
  <div class="foot-line"><span>© 2026 Jabble Super Intelligence Inc.</span>$made<a href="/blog/">Blog</a><a href="/privacy/">Privacy</a><a href="/terms/">Terms</a><a href="/your-data/">Your data</a><a href="mailto:support@trybondi.app">support@trybondi.app</a></div>
  <div class="foot-line">$askRow</div>
</footer>
</body>
</html>

HTML;
}

$parsedown = new Parsedown();
$written = [];
$out = $preview ?? "$root/public_html";

foreach ($shown as $slug => $post) {
    $url = SITE . "/blog/$slug/";
    $html = $parsedown->text($post['markdown']);
    $html = str_replace('<img ', '<img loading="lazy" ', $html);
    $html = preg_replace('~<a href="(https?://(?!trybondi\.app)[^"]+)"~', '<a href="$1" target="_blank" rel="noopener"', $html);
    $image = $post['image'] ?? '';
    $related = '';
    foreach ($post['related'] as $other) {
        if (isset($shown[$other])) {
            $related .= '<li><a href="/blog/' . $h($other) . '/">' . $h($shown[$other]['title']) . '</a><span>' . $h($shown[$other]['description']) . '</span></li>';
        }
    }
    $ld = [
        '@context' => 'https://schema.org',
        '@graph' => [
            ['@type' => 'BlogPosting', 'headline' => $post['title'], 'description' => $post['description'],
             'datePublished' => $post['date'], 'dateModified' => $post['updated'], 'mainEntityOfPage' => $url,
             'image' => SITE . ($image ?: '/assets/og-image.jpg'),
             'author' => ['@type' => 'Person', 'name' => AUTHOR, 'url' => AUTHOR_URL, 'jobTitle' => 'Maker of Bondi'],
             'publisher' => ['@type' => 'Organization', 'name' => 'Jabble Super Intelligence Inc.', 'logo' => SITE . '/assets/icon-blue.png'],
             'about' => ['@id' => SITE . '/#app']],
            ['@type' => 'BreadcrumbList', 'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Bondi', 'item' => SITE . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => SITE . '/blog/'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $post['title'], 'item' => $url]]],
        ],
    ];
    $qa = questions($post['markdown']);
    if ($qa) {
        $ld['@graph'][] = ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($q) => ['@type' => 'Question', 'name' => $q[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]], $qa)];
    }
    $head = '<meta property="og:type" content="article"><meta property="og:title" content="' . $h($post['title']) . '">'
          . '<meta property="og:description" content="' . $h($post['description']) . '">'
          . '<meta property="og:image" content="' . $h(SITE . ($image ?: '/assets/og-image.jpg')) . '">'
          . '<meta property="article:published_time" content="' . $h($post['date']) . '"><meta property="article:author" content="' . AUTHOR_URL . '">'
          . '<link rel="alternate" type="text/markdown" title="Plain text, for AI assistants" href="/blog/' . $h($slug) . '.md">'
          . ($post['status'] !== 'published' ? '<meta name="robots" content="noindex">' : '')
          . "\n<script type=\"application/ld+json\">\n" . json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n</script>";
    $updated = $post['updated'] !== $post['date'] ? ' · Updated ' . $h(nice_date($post['updated'])) : '';
    $draft = $post['status'] !== 'published' ? '<p class="draft-flag">Draft, for review: not on trybondi.app yet.</p>' : '';
    $body = <<<HTML
<article class="post">
  <nav class="crumbs" aria-label="Breadcrumb"><a href="/blog/">Blog</a></nav>
  $draft
  <h1>{$h($post['title'])}</h1>
  <p class="byline">By <a href="{$h(AUTHOR_URL)}" target="_blank" rel="me noopener">Noaman Khalil</a>, maker of Bondi · <time datetime="{$h($post['date'])}">{$h(nice_date($post['date']))}</time>$updated · {$post['minutes']} min read</p>
  <div class="prose">
$html
  </div>
  <p class="plain-copy">Also as <a href="/blog/{$h($slug)}.md">plain text</a>, for AI assistants and readers.</p>
  <aside class="cta">
    <img src="/assets/icon-blue.png" alt="" width="56" height="56">
    <div><p class="cta-title">Bondi tells you why your Mac is slow, in one plain sentence.</p>
    <p>It runs on your Mac, groups every process into the apps you know, and keeps 30 days of history. Nothing leaves your Mac.</p>
    <p><a class="pillbtn primary" href="/beta/">Join the beta</a> <a class="pillbtn secondary" href="/">See how it works</a></p></div>
  </aside>
HTML;
    if ($related !== '') {
        $body .= "\n  <section class=\"related\" aria-labelledby=\"related-title\"><h2 id=\"related-title\">Keep reading</h2><ul>$related</ul></section>";
    }
    $body .= "\n</article>";
    $file = "$out/blog/$slug/index.html";
    @mkdir(dirname($file), 0755, true);
    file_put_contents($file, page($post['title'] . ' · Bondi blog', $post['description'], $url, $body, $head, $askRow));
    file_put_contents("$out/blog/$slug.md", markdown_copy($post));
    $written[] = $file;
}

// The index of articles
if ($shown) {
    $cards = '';
    foreach ($shown as $slug => $post) {
        $cards .= '<li><a class="card" href="/blog/' . $h($slug) . '/"><span class="card-date">' . $h(nice_date($post['date'])) . ' · ' . $post['minutes'] . ' min read</span>'
                . '<span class="card-title">' . $h($post['title']) . '</span><span class="card-desc">' . $h($post['description']) . '</span></a></li>';
    }
    $ld = ['@context' => 'https://schema.org', '@type' => 'Blog', 'name' => 'Bondi blog', 'url' => SITE . '/blog/',
           'publisher' => ['@type' => 'Organization', 'name' => 'Jabble Super Intelligence Inc.']];
    $head = '<meta property="og:type" content="website"><meta property="og:title" content="Bondi blog"><meta property="og:image" content="' . SITE . '/assets/og-image.jpg">'
          . "\n<script type=\"application/ld+json\">" . json_encode($ld, JSON_UNESCAPED_SLASHES) . '</script>';
    $body = '<section class="blog-head"><p class="eyebrow">Bondi blog</p><h1>Why your Mac does what it does.</h1>'
          . '<p class="lede">Plain answers about Mac performance, from real readings of a real Mac, by the maker of Bondi.</p></section>'
          . '<ul class="cards">' . $cards . '</ul>';
    file_put_contents("$out/blog/index.html", page('Bondi blog: why your Mac is slow, and what to do about it',
        'Plain answers about Mac performance: slowdowns, memory pressure, loud fans, kernel_task, WindowServer and Spotlight, from real readings of a real Mac.',
        SITE . '/blog/', $body, $head, $askRow));
    $written[] = "$out/blog/index.html";
}

if ($preview !== null) {
    @mkdir("$preview/assets", 0755, true);
    echo 'Preview: ' . count($shown) . " articles in $preview/blog/\n";
    exit;
}

// MARK: Publishing: RSS, sitemap, llms.txt, and the top-level copy

$items = '';
foreach ($live as $slug => $post) {
    $items .= '<item><title>' . $h($post['title']) . '</title><link>' . SITE . "/blog/$slug/</link><guid>" . SITE . "/blog/$slug/</guid>"
            . '<pubDate>' . date(DATE_RSS, strtotime($post['date'] . ' 12:00 UTC')) . '</pubDate><description>' . $h($post['description']) . '</description></item>';
}
if ($live) {
    file_put_contents("$root/public_html/blog/rss.xml", '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Bondi blog</title><link>' . SITE . '/blog/</link>'
        . '<description>Why your Mac does what it does, by the maker of Bondi.</description><language>en</language>' . $items . "</channel></rss>\n");
    $written[] = "$root/public_html/blog/rss.xml";
}

$sitemapEntries = $live ? '  <url><loc>' . SITE . '/blog/</loc><lastmod>' . max(array_column($live, 'updated')) . "</lastmod></url>\n" : '';
foreach ($live as $slug => $post) {
    $sitemapEntries .= '  <url><loc>' . SITE . "/blog/$slug/</loc><lastmod>{$post['updated']}</lastmod></url>\n";
}
$sitemap = (string)file_get_contents("$root/public_html/sitemap.xml");
$sitemap = preg_replace('~\s*<!-- blog -->.*?<!-- /blog -->~s', '', $sitemap);
$sitemap = str_replace('</urlset>', "  <!-- blog -->\n$sitemapEntries  <!-- /blog -->\n</urlset>", $sitemap);
file_put_contents("$root/public_html/sitemap.xml", $sitemap);
$written[] = "$root/public_html/sitemap.xml";

$llms = (string)file_get_contents("$root/public_html/llms.txt");
$llms = preg_replace('~\n?<!-- blog -->.*?<!-- /blog -->\n?~s', "\n", $llms);
if ($live) {
    $section = "<!-- blog -->\n## Blog\n";
    $ordered = $live;
    uasort($ordered, fn($a, $b) => ((int)($a['order'] ?? 99) <=> (int)($b['order'] ?? 99)) ?: strcmp($a['title'], $b['title']));
    foreach ($ordered as $slug => $post) {
        $section .= '- [' . $post['title'] . '](' . SITE . "/blog/$slug.md): " . $post['description'] . "\n";
    }
    $llms = rtrim($llms) . "\n\n$section<!-- /blog -->\n";
}
file_put_contents("$root/public_html/llms.txt", $llms);
$written[] = "$root/public_html/llms.txt";

$full = (string)file_get_contents("$root/public_html/llms-full.txt");
$full = rtrim(preg_replace('~\n*<!-- blog -->.*?<!-- /blog -->\n?~s', '', $full)) . "\n";
if ($live) {
    $full .= "\n<!-- blog -->\n## Articles from the Bondi blog\nThe full text of each article, by " . AUTHOR . ", maker of Bondi.\n";
    foreach ($ordered as $slug => $post) {
        $full .= "\n" . preg_replace('/^#/m', '###', markdown_copy($post)); // the article's headings sit under this section
    }
    $full .= "<!-- /blog -->\n";
}
file_put_contents("$root/public_html/llms-full.txt", $full);

// Plain-text copies may be read even though the site blocks .md files elsewhere (notes and drafts)
file_put_contents("$root/public_html/blog/.htaccess", "# The articles' plain-text copies (/blog/<slug>.md) are public, unlike .md files elsewhere on the site.\n<FilesMatch \"\\.md$\">\n  Require all granted\n</FilesMatch>\nAddType \"text/markdown; charset=utf-8\" .md\n");

// Remove pages of articles that are no longer published
foreach (glob("$root/public_html/blog/*/index.html") ?: [] as $page) {
    if (!isset($live[basename(dirname($page))])) {
        unlink($page);
        @rmdir(dirname($page));
        @unlink(dirname($page) . '.md');
    }
}
if (!$live) {
    @unlink("$root/public_html/blog/index.html");
    @unlink("$root/public_html/blog/rss.xml");
}

// The top-level copy that Hostinger serves mirrors public_html for these files
foreach (['blog', 'sitemap.xml', 'llms.txt', 'llms-full.txt'] as $item) {
    shell_exec('rm -rf ' . escapeshellarg("$root/$item") . ' && cp -R ' . escapeshellarg("$root/public_html/$item") . ' ' . escapeshellarg("$root/$item") . ' 2>/dev/null');
}
echo 'Published ' . count($live) . " articles.\n";
