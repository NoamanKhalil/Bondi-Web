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
// How Bondi is described everywhere, word for word (CLAUDE.md: keep every page, listing and file in step)
const PITCH = "Bondi is a native macOS system monitor with on-device AI. It tells you why your Mac is slow, in one plain sentence.";
$h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

// Topics: the blog index and llms.txt group articles under these, in this order. An article's "topic:" header names
// one; an article without a known topic goes under "More".
const TOPICS = [
    'slow' => ['Why is my Mac slow?', 'Start here: the usual causes, and how to tell which one it is.'],
    'memory' => ['Memory', "What's using your Mac's memory, and when it matters."],
    'heat' => ['Heat, fans and busy processes', 'Why the fans spin up, and what macOS is doing when your Mac gets hot.'],
    'processes' => ['What is this process?', 'The macOS processes people ask about most, in plain words.'],
    'tools' => ['Mac tools and shortcuts', 'The tools already on your Mac: the task manager, Force Quit, uptime and more.'],
    'bondi' => ['Bondi and other tools', 'How Bondi compares with Activity Monitor, and how its on-device AI works.'],
    'more' => ['More', ''],
];
/** A visible breadcrumb trail: [[name, href], …]; the last one is the current section. */
function crumbs(array $trail): string
{
    $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    return '<nav class="crumbs" aria-label="Breadcrumb"><ol>' . implode('', array_map(fn($c) => '<li><a href="' . $h($c[1]) . '">' . $h($c[0]) . '</a></li>', $trail)) . '</ol></nav>';
}

/** The same trail for search engines (schema.org BreadcrumbList), ending with the page itself. */
function breadcrumb_ld(array $trail): array
{
    return ['@type' => 'BreadcrumbList', 'itemListElement' => array_map(fn($i, $c) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $c[0],
        'item' => str_starts_with($c[1], 'http') ? $c[1] : SITE . $c[1]], array_keys($trail), $trail)];
}

/** Articles grouped by topic, in TOPICS order, each group in its "order:" order. */
function by_topic(array $posts): array
{
    $groups = [];
    foreach ($posts as $slug => $post) {
        $groups[isset(TOPICS[$post['topic'] ?? '']) ? $post['topic'] : 'more'][$slug] = $post;
    }
    $sorted = [];
    foreach (array_keys(TOPICS) as $key) {
        if (!empty($groups[$key])) {
            uasort($groups[$key], fn($a, $b) => ((int)($a['order'] ?? 99) <=> (int)($b['order'] ?? 99)) ?: strcmp($a['title'], $b['title']));
            $sorted[$key] = $groups[$key];
        }
    }
    return $sorted;
}

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
define('HAS_GUIDE', $preview !== null || (bool)array_filter(glob("$root/content/guide/*.md") ?: [],
    fn($f) => (bool)preg_match('/^status:\s*published\s*$/m', (string)file_get_contents($f))));
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
    $answer = ($post['answer'] ?? '') !== '' ? "**Quick answer:** {$post['answer']}\n\n" : '';
    return "# {$post['title']}\n\n> {$post['description']}\n\n{$answer}By " . AUTHOR . ", maker of Bondi. $dates.\nWeb page: " . SITE . "/blog/{$post['slug']}/\n\n"
         . trim($md) . "\n\n---\n" . PITCH . ' ' . SITE . "/\n";
}

function nice_date(string $ymd): string
{
    return date('F j, Y', strtotime($ymd . ' 12:00 UTC'));
}

function page(string $title, string $description, string $canonical, string $body, string $extraHead, string $askRow): string
{
    $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $toggle = '<button class="theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark mode"><svg class="moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5a8.5 8.5 0 1 0 10.7 10.7z"/></svg><svg class="sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4.2"/><path d="M12 2.5v2.2M12 19.3v2.2M2.5 12h2.2M19.3 12h2.2M5.3 5.3l1.6 1.6M17.1 17.1l1.6 1.6M5.3 18.7l1.6-1.6M17.1 6.9l1.6-1.6"/></svg></button>';
    $guideLink = HAS_GUIDE ? '<a href="/guide/">Guide</a>' : ''; // only once a guide page is live (or in a preview)
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
  <nav class="links" aria-label="Site"><a href="/blog/">Blog</a>{$guideLink}<a href="/">What's Bondi?</a></nav>
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
             'abstract' => ($post['answer'] ?? '') !== '' ? plain($post['answer']) : null,
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
    $ld['@graph'][0] = array_filter($ld['@graph'][0], fn($value) => $value !== null); // no "abstract" without an answer
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
    // The quick answer: the article's point in a sentence or two, set apart at the top (header "answer:")
    $answerBox = ($post['answer'] ?? '') !== ''
        ? '<aside class="quick-answer" aria-label="Quick answer"><p class="qa-label">Quick answer</p><p>' . $parsedown->line($post['answer']) . '</p></aside>' : '';
    $topic = isset(TOPICS[$post['topic'] ?? '']) && ($post['topic'] ?? '') !== 'more' ? $post['topic'] : null;
    $trail = crumbs(array_merge([['Bondi', '/'], ['Blog', '/blog/']], $topic ? [[TOPICS[$topic][0], '/blog/#' . $topic]] : []));
    $draft = $post['status'] !== 'published' ? '<p class="draft-flag">Draft, for review: not on trybondi.app yet.</p>' : '';
    $body = <<<HTML
<article class="post">
  $trail
  $draft
  <h1>{$h($post['title'])}</h1>
  <p class="byline">By <a href="{$h(AUTHOR_URL)}" target="_blank" rel="me noopener">Noaman Khalil</a>, maker of Bondi · <time datetime="{$h($post['date'])}">{$h(nice_date($post['date']))}</time>$updated · {$post['minutes']} min read</p>
  $answerBox
  <div class="prose">
$html
  </div>
  <p class="plain-copy">Also as <a href="/blog/{$h($slug)}.md">plain text</a>, for AI assistants and readers.</p>
  <aside class="cta">
    <img src="/assets/icon-blue.png" alt="" width="56" height="56">
    <div><p class="cta-title">{$h(PITCH)}</p>
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

// The index of articles, grouped by topic
if ($shown) {
    $groups = '';
    foreach (by_topic($shown) as $key => $group) {
        $cards = '';
        foreach ($group as $slug => $post) {
            $cards .= '<li><a class="card" href="/blog/' . $h($slug) . '/"><span class="card-date">' . $h(nice_date($post['date'])) . ' · ' . $post['minutes'] . ' min read</span>'
                    . '<span class="card-title">' . $h($post['title']) . '</span><span class="card-desc">' . $h($post['description']) . '</span></a></li>';
        }
        [$name, $intro] = TOPICS[$key];
        $groups .= '<section class="topic" id="' . $h($key) . '" aria-labelledby="topic-' . $h($key) . '"><h2 id="topic-' . $h($key) . '">' . $h($name) . '</h2>'
                 . ($intro !== '' ? '<p class="topic-intro">' . $h($intro) . '</p>' : '') . '<ul class="cards">' . $cards . '</ul></section>';
    }
    $ld = ['@context' => 'https://schema.org', '@graph' => [
        ['@type' => 'Blog', 'name' => 'Bondi blog', 'url' => SITE . '/blog/', 'publisher' => ['@type' => 'Organization', 'name' => 'Jabble Super Intelligence Inc.']],
        breadcrumb_ld([['Bondi', '/'], ['Blog', '/blog/']])]];
    $head = '<meta property="og:type" content="website"><meta property="og:title" content="Bondi blog"><meta property="og:image" content="' . SITE . '/assets/og-image.jpg">'
          . "\n<script type=\"application/ld+json\">" . json_encode($ld, JSON_UNESCAPED_SLASHES) . '</script>';
    $body = '<section class="blog-head">' . crumbs([['Bondi', '/'], ['Blog', '/blog/']]) . '<p class="eyebrow">Bondi blog</p><h1>Why your Mac does what it does.</h1>'
          . '<p class="lede">Plain answers about Mac performance, from real readings of a real Mac, by the maker of Bondi.</p></section>'
          . $groups;
    file_put_contents("$out/blog/index.html", page('Bondi blog: why your Mac is slow, and what to do about it',
        'Plain answers about Mac performance: slowdowns, memory pressure, loud fans, kernel_task, WindowServer and Spotlight, from real readings of a real Mac.',
        SITE . '/blog/', $body, $head, $askRow));
    $written[] = "$out/blog/index.html";
}

// MARK: Pages: content/pages/<slug>.md (the comparison page) at /<slug>/, with the blog's look but no byline

foreach (glob("$root/content/pages/*.md") ?: [] as $file) {
    if (!preg_match('/\A---\n(.*?)\n---\n(.*)\z/s', (string)file_get_contents($file), $m)) {
        fwrite(STDERR, "No header block in $file\n");
        exit(1);
    }
    $meta = [];
    foreach (explode("\n", $m[1]) as $line) {
        if (preg_match('/^([a-z]+):\s*(.*)$/', $line, $kv)) {
            $meta[$kv[1]] = preg_replace('/^"(.*)"$/', '$1', trim($kv[2]));
        }
    }
    $slug = basename($file, '.md');
    $url = SITE . "/$slug/";
    $html = preg_replace('~<a href="(https?://(?!trybondi\.app)[^"]+)"~', '<a href="$1" target="_blank" rel="noopener"', $parsedown->text($m[2]));
    $answer = $meta['answer'] ?? '';
    $ld = ['@context' => 'https://schema.org', '@graph' => [
        array_filter(['@type' => 'WebPage', 'name' => $meta['title'], 'description' => $meta['description'], 'url' => $url,
            'abstract' => $answer !== '' ? plain($answer) : null, 'dateModified' => $meta['updated'] ?? null, 'about' => ['@id' => SITE . '/#app']]),
        ['@type' => 'SoftwareApplication', '@id' => SITE . '/#app', 'name' => 'Bondi', 'url' => SITE . '/', 'description' => PITCH,
            'applicationCategory' => 'UtilitiesApplication', 'applicationSubCategory' => 'System monitor', 'operatingSystem' => 'macOS 15 or later'],
        breadcrumb_ld([['Bondi', '/'], [$meta['title'], "/$slug/"]]),
    ]];
    if ($qa = questions($m[2])) {
        $ld['@graph'][] = ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($q) => ['@type' => 'Question', 'name' => $q[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]], $qa)];
    }
    $head = '<meta property="og:type" content="website"><meta property="og:title" content="' . $h($meta['title']) . '">'
          . '<meta property="og:description" content="' . $h($meta['description']) . '"><meta property="og:image" content="' . SITE . '/assets/og-image.jpg">'
          . '<link rel="alternate" type="text/markdown" title="Plain text, for AI assistants" href="/' . $h($slug) . '/' . $h($slug) . '.md">'
          . "\n<script type=\"application/ld+json\">\n" . json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n</script>";
    $checked = isset($meta['checked']) ? 'Checked against each app\'s own website on ' . $h(nice_date($meta['checked'])) : '';
    $updated = isset($meta['updated']) ? ($checked !== '' ? ' · ' : '') . 'Updated ' . $h(nice_date($meta['updated'])) : '';
    $answerBox = $answer !== '' ? '<aside class="quick-answer" aria-label="Quick answer"><p class="qa-label">Quick answer</p><p>' . $parsedown->line($answer) . '</p></aside>' : '';
    $body = '<article class="post">' . crumbs([['Bondi', '/'], [$meta['crumb'] ?? $meta['title'], "/$slug/"]]) . '<h1>' . $h($meta['title']) . '</h1>'
          . '<p class="byline">' . $checked . $updated . '</p>' . $answerBox . '<div class="prose">' . $html . '</div>'
          . '<p class="plain-copy">Also as <a href="/' . $h($slug) . '/' . $h($slug) . '.md">plain text</a>, for AI assistants and readers.</p>'
          . '<aside class="cta"><img src="/assets/icon-blue.png" alt="" width="56" height="56"><div><p class="cta-title">' . $h(PITCH) . '</p>'
          . '<p>It runs on your Mac, groups every process into the apps you know, and keeps 30 days of history. Nothing leaves your Mac.</p>'
          . '<p><a class="pillbtn primary" href="/beta/">Join the beta</a> <a class="pillbtn secondary" href="/">See how it works</a></p></div></aside></article>';
    @mkdir("$out/$slug", 0755, true);
    file_put_contents("$out/$slug/index.html", page($meta['title'] . ' · Bondi', $meta['description'], $url, $body, $head, $askRow));
    $md = preg_replace('~\]\(/~', '](' . SITE . '/', $m[2]);
    file_put_contents("$out/$slug/$slug.md", "# {$meta['title']}\n\n> {$meta['description']}\n\n" . ($answer !== '' ? "**Quick answer:** $answer\n\n" : '')
        . "Web page: $url\n\n" . trim($md) . "\n\n---\n" . PITCH . ' ' . SITE . "/\n");
    file_put_contents("$out/$slug/.htaccess", "# This page's plain-text copy (/$slug/$slug.md) is public, unlike .md files elsewhere on the site.\n"
        . "<FilesMatch \"\\.md$\">\n  Require all granted\n</FilesMatch>\nAddType \"text/markdown; charset=utf-8\" .md\n");
    $written[] = "$out/$slug/index.html";
}

// MARK: The guide: content/guide/<slug>.md at /guide/<slug>/ (reference pages, e.g. "What is launchd on Mac?")
// Built from the app's own process notes (TryBondi's ProcessGuide.json) and real readings; reviewed by the owner.
// Header: title, name, group (processes|terms), description, answer, status draft|published, reviewed, related (guide slugs),
// article (a blog slug). dev/guide-new.php scaffolds new pages.

const GUIDE_GROUPS = ['processes' => ['Processes', 'What the processes in Activity Monitor are, why they get busy, and whether you can quit them.'],
                      'terms' => ['Mac terms', 'The words Activity Monitor and macOS use, in plain English.']];
$guide = [];
foreach (glob("$root/content/guide/*.md") ?: [] as $file) {
    if (!preg_match('/\A---\n(.*?)\n---\n(.*)\z/s', (string)file_get_contents($file), $m)) {
        fwrite(STDERR, "No header block in $file\n");
        exit(1);
    }
    $meta = [];
    foreach (explode("\n", $m[1]) as $line) {
        if (preg_match('/^([a-z]+):\s*(.*)$/', $line, $kv)) {
            $meta[$kv[1]] = preg_replace('/^"(.*)"$/', '$1', trim($kv[2]));
        }
    }
    foreach (['title', 'name', 'description', 'status'] as $need) {
        if (($meta[$need] ?? '') === '') {
            fwrite(STDERR, "$file needs \"$need:\"\n");
            exit(1);
        }
    }
    $slug = basename($file, '.md');
    $guide[$slug] = array_merge($meta, ['slug' => $slug, 'markdown' => $m[2], 'group' => isset(GUIDE_GROUPS[$meta['group'] ?? '']) ? $meta['group'] : 'processes',
        'related' => array_values(array_filter(array_map('trim', explode(',', $meta['related'] ?? ''))))]);
}
ksort($guide, SORT_NATURAL | SORT_FLAG_CASE);
$guideLive = array_filter($guide, fn($g) => $g['status'] === 'published');
$guideShown = $preview !== null ? $guide : $guideLive;

foreach ($guideShown as $slug => $g) {
    $url = SITE . "/guide/$slug/";
    $groupName = GUIDE_GROUPS[$g['group']][0];
    $html = preg_replace('~<a href="(https?://(?!trybondi\.app)[^"]+)"~', '<a href="$1" target="_blank" rel="noopener"', $parsedown->text($g['markdown']));
    $answer = $g['answer'] ?? '';
    $reviewed = $g['reviewed'] ?? null;
    $related = '';
    foreach ($g['related'] as $other) {
        if (isset($guideShown[$other])) {
            $related .= '<li><a href="/guide/' . $h($other) . '/">' . $h($guideShown[$other]['name']) . '</a><span>' . $h($guideShown[$other]['description']) . '</span></li>';
        }
    }
    if (($g['article'] ?? '') !== '' && isset($shown[$g['article']])) {
        $related .= '<li><a href="/blog/' . $h($g['article']) . '/">' . $h($shown[$g['article']]['title']) . '</a><span>' . $h($shown[$g['article']]['description']) . '</span></li>';
    }
    $ld = ['@context' => 'https://schema.org', '@graph' => [
        array_filter(['@type' => 'WebPage', 'name' => $g['title'], 'description' => $g['description'], 'url' => $url,
            'abstract' => $answer !== '' ? plain($answer) : null, 'lastReviewed' => $reviewed,
            'reviewedBy' => ['@type' => 'Person', 'name' => AUTHOR, 'url' => AUTHOR_URL],
            'publisher' => ['@type' => 'Organization', 'name' => 'Jabble Super Intelligence Inc.'],
            'mainEntity' => ['@type' => 'DefinedTerm', 'name' => $g['name'], 'description' => plain($g['description']),
                'inDefinedTermSet' => ['@type' => 'DefinedTermSet', 'name' => "Bondi's Mac guide", 'url' => SITE . '/guide/']]], fn($v) => $v !== null),
        breadcrumb_ld([['Bondi', '/'], ['Guide', '/guide/'], [$g['title'], "/guide/$slug/"]]),
    ]];
    if ($qa = questions($g['markdown'])) {
        $ld['@graph'][] = ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($q) => ['@type' => 'Question', 'name' => $q[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]], $qa)];
    }
    $head = '<meta property="og:type" content="article"><meta property="og:title" content="' . $h($g['title']) . '">'
          . '<meta property="og:description" content="' . $h($g['description']) . '"><meta property="og:image" content="' . SITE . '/assets/og-image.jpg">'
          . '<link rel="alternate" type="text/markdown" title="Plain text, for AI assistants" href="/guide/' . $h($slug) . '.md">'
          . ($g['status'] !== 'published' ? '<meta name="robots" content="noindex">' : '')
          . "\n<script type=\"application/ld+json\">\n" . json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n</script>";
    $answerBox = $answer !== '' ? '<aside class="quick-answer" aria-label="Quick answer"><p class="qa-label">Quick answer</p><p>' . $parsedown->line($answer) . '</p></aside>' : '';
    $draft = $g['status'] !== 'published' ? '<p class="draft-flag">Draft, for review: not on trybondi.app yet.</p>' : '';
    $body = '<article class="post">' . crumbs([['Bondi', '/'], ['Guide', '/guide/'], [$groupName, '/guide/#' . $g['group']]]) . $draft
          . '<h1>' . $h($g['title']) . '</h1>'
          . '<p class="byline">Bondi\'s Mac guide · Reviewed by <a href="' . $h(AUTHOR_URL) . '" target="_blank" rel="me noopener">' . AUTHOR . '</a>'
          . ($reviewed ? ' · <time datetime="' . $h($reviewed) . '">' . $h(nice_date($reviewed)) . '</time>' : '') . '</p>'
          . $answerBox . '<div class="prose">' . $html . '</div>'
          . '<p class="plain-copy">Also as <a href="/guide/' . $h($slug) . '.md">plain text</a>, for AI assistants and readers.</p>'
          . '<aside class="cta"><img src="/assets/icon-blue.png" alt="" width="56" height="56"><div><p class="cta-title">' . $h(PITCH) . '</p>'
          . '<p>Its process guide explains 125 macOS processes like this one, with what each is using on your Mac right now.</p>'
          . '<p><a class="pillbtn primary" href="/beta/">Join the beta</a> <a class="pillbtn secondary" href="/">See how it works</a></p></div></aside>'
          . ($related !== '' ? '<section class="related" aria-labelledby="related-title"><h2 id="related-title">Related</h2><ul>' . $related . '</ul></section>' : '')
          . '</article>';
    @mkdir("$out/guide/$slug", 0755, true);
    file_put_contents("$out/guide/$slug/index.html", page($g['title'] . ' · Bondi guide', $g['description'], $url, $body, $head, $askRow));
    $md = preg_replace('~\]\(/~', '](' . SITE . '/', $g['markdown']);
    file_put_contents("$out/guide/$slug.md", "# {$g['title']}\n\n> {$g['description']}\n\n" . ($answer !== '' ? "**Quick answer:** $answer\n\n" : '')
        . "From Bondi's Mac guide, reviewed by " . AUTHOR . ($reviewed ? ' on ' . nice_date($reviewed) : '') . ". Web page: $url\n\n" . trim($md) . "\n\n---\n" . PITCH . ' ' . SITE . "/\n");
}
if ($guideShown) {
    $sections = '';
    foreach (GUIDE_GROUPS as $key => [$name, $intro]) {
        $items = array_filter($guideShown, fn($g) => $g['group'] === $key);
        if (!$items) { continue; }
        $cards = '';
        foreach ($items as $slug => $g) {
            $cards .= '<li><a class="card" href="/guide/' . $h($slug) . '/"><span class="card-title">' . $h($g['name']) . '</span><span class="card-desc">' . $h($g['description']) . '</span></a></li>';
        }
        $sections .= '<section class="topic" id="' . $h($key) . '" aria-labelledby="topic-' . $h($key) . '"><h2 id="topic-' . $h($key) . '">' . $h($name) . '</h2><p class="topic-intro">' . $h($intro) . '</p><ul class="cards">' . $cards . '</ul></section>';
    }
    $ld = ['@context' => 'https://schema.org', '@graph' => [
        ['@type' => 'DefinedTermSet', 'name' => "Bondi's Mac guide", 'url' => SITE . '/guide/', 'hasDefinedTerm' => array_values(array_map(fn($g) => ['@type' => 'DefinedTerm', 'name' => $g['name'], 'url' => SITE . "/guide/{$g['slug']}/"], $guideShown))],
        breadcrumb_ld([['Bondi', '/'], ['Guide', '/guide/']])]];
    $head = '<meta property="og:type" content="website"><meta property="og:title" content="Bondi\'s Mac guide"><meta property="og:image" content="' . SITE . '/assets/og-image.jpg">'
          . "\n<script type=\"application/ld+json\">" . json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    $body = '<section class="blog-head">' . crumbs([['Bondi', '/'], ['Guide', '/guide/']]) . '<p class="eyebrow">Bondi\'s Mac guide</p><h1>What is this process?</h1>'
          . '<p class="lede">The processes you see in Activity Monitor, in plain words: what each one does, why it gets busy, and whether you can quit it. From the notes in Bondi\'s process guide, with real readings of a real Mac.</p></section>' . $sections;
    @mkdir("$out/guide", 0755, true);
    file_put_contents("$out/guide/index.html", page("Bondi's Mac guide: what is this process on my Mac?",
        'What the processes in Activity Monitor are, in plain words: what each does, why it gets busy, and whether you can quit it. With real readings of a real Mac.',
        SITE . '/guide/', $body, $head, $askRow));
    file_put_contents("$out/guide/.htaccess", "# The guide's plain-text copies (/guide/<slug>.md) are public, unlike .md files elsewhere on the site.\n"
        . "<FilesMatch \"\\.md$\">\n  Require all granted\n</FilesMatch>\nAddType \"text/markdown; charset=utf-8\" .md\n");
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
$guideEntries = $guideLive ? '  <url><loc>' . SITE . '/guide/</loc><lastmod>' . max(array_map(fn($g) => $g['reviewed'] ?? '2026-10-10', $guideLive)) . "</lastmod></url>\n" : '';
foreach ($guideLive as $slug => $g) {
    $guideEntries .= '  <url><loc>' . SITE . "/guide/$slug/</loc><lastmod>" . ($g['reviewed'] ?? '2026-10-10') . "</lastmod></url>\n";
}
$sitemap = (string)file_get_contents("$root/public_html/sitemap.xml");
$sitemap = preg_replace('~\s*<!-- guide -->.*?<!-- /guide -->~s', '', $sitemap);
$sitemap = str_replace('</urlset>', "  <!-- guide -->\n$guideEntries  <!-- /guide -->\n</urlset>", $sitemap);
$sitemap = preg_replace('~\s*<!-- blog -->.*?<!-- /blog -->~s', '', $sitemap);
$sitemap = str_replace('</urlset>', "  <!-- blog -->\n$sitemapEntries  <!-- /blog -->\n</urlset>", $sitemap);
file_put_contents("$root/public_html/sitemap.xml", $sitemap);
$written[] = "$root/public_html/sitemap.xml";

$llms = (string)file_get_contents("$root/public_html/llms.txt");
$llms = preg_replace('~\n?<!-- blog -->.*?<!-- /blog -->\n?~s', "\n", $llms);
if ($live) {
    $section = "<!-- blog -->\n";
    $ordered = [];
    foreach (by_topic($live) as $key => $group) {
        $section .= '## Guides: ' . TOPICS[$key][0] . "\n";
        foreach ($group as $slug => $post) {
            $section .= '- [' . $post['title'] . '](' . SITE . "/blog/$slug.md): " . $post['description'] . "\n";
            $ordered[$slug] = $post;
        }
        $section .= "\n";
    }
    $section = rtrim($section) . "\n";
    $llms = rtrim($llms) . "\n\n$section<!-- /blog -->\n";
}
$llms = preg_replace('~\n?<!-- guide -->.*?<!-- /guide -->\n?~s', "\n", $llms);
if ($guideLive) {
    $section = "<!-- guide -->\n## Bondi's Mac guide: what is this process?\n";
    foreach ($guideLive as $slug => $g) {
        $section .= '- [' . $g['title'] . '](' . SITE . "/guide/$slug.md): " . $g['description'] . "\n";
    }
    $llms = rtrim($llms) . "\n\n$section<!-- /guide -->\n";
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
if (!$guideLive) {
    shell_exec('rm -rf ' . escapeshellarg("$root/public_html/guide")); // nothing published yet: no guide pages on the site
}
foreach (['blog', 'guide', 'sitemap.xml', 'llms.txt', 'llms-full.txt'] as $item) {
    shell_exec('rm -rf ' . escapeshellarg("$root/$item") . ' && cp -R ' . escapeshellarg("$root/public_html/$item") . ' ' . escapeshellarg("$root/$item") . ' 2>/dev/null');
}
echo 'Published ' . count($live) . " articles.\n";

// The rest of the served copy (minified CSS, stamped films) comes from dev/build-site.php
passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/build-site.php'));
