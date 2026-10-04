<?php
// Copies public_html/ to the top level of this repository, which is what trybondi.app serves (Hostinger's Git deploy
// puts the repository's top level in the site's public_html). On the way:
// - CSS is minified, in .css files and in the <style> blocks of .html pages; public_html keeps the readable source.
// - Pages ask for the films and their posters (assets/bondi-*.mp4, -poster.jpg/png) with ?v=<content hash>, so
//   browsers can keep them for a year (.htaccess) and a new render is still seen at once.
// Run it after changing anything in public_html, before committing: php dev/build-site.php
// (dev/build-blog.php runs it too.)

if (!in_array(PHP_SAPI, ['cli', 'cli-server'], true)) {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$src = "$root/public_html";

/** CSS without comments and spare whitespace. Quoted strings and url(...) are kept exactly as written. */
function minify_css(string $css): string
{
    $squeeze = function (string $s): string {
        $s = preg_replace('/\s+/', ' ', $s);
        $s = preg_replace('/\s*([{};,>])\s*/', '$1', $s);
        $s = preg_replace('/:\s+/', ':', $s);
        return str_replace(';}', '}', $s);
    };
    $out = '';
    $plain = '';
    $n = strlen($css);
    for ($i = 0; $i < $n; $i++) {
        $c = $css[$i];
        if ($c === '/' && ($css[$i + 1] ?? '') === '*') { // a comment: dropped
            $end = strpos($css, '*/', $i + 2);
            $i = $end === false ? $n : $end + 1;
            $plain .= ' ';
            continue;
        }
        $verbatim = null;
        if ($c === '"' || $c === "'") { // a quoted string
            $j = $i + 1;
            while ($j < $n && $css[$j] !== $c) {
                $j += $css[$j] === '\\' ? 2 : 1;
            }
            $verbatim = substr($css, $i, $j - $i + 1);
            $i = $j;
        } elseif (strncasecmp(substr($css, $i, 4), 'url(', 4) === 0) { // url(...)
            $j = strpos($css, ')', $i);
            $j = $j === false ? $n - 1 : $j;
            $verbatim = substr($css, $i, $j - $i + 1);
            $i = $j;
        }
        if ($verbatim === null) {
            $plain .= $c;
            continue;
        }
        $out .= $squeeze($plain) . $verbatim;
        $plain = '';
    }
    return trim($out . $squeeze($plain));
}

/** The films' and posters' addresses with ?v=<the first 10 characters of the file's SHA-1>. */
function stamp_films(string $html, string $src): string
{
    static $hashes = [];
    return preg_replace_callback('~((?:src|poster|href)=")(/?assets/(bondi-[a-z]+(?:-poster)?\.(?:mp4|jpg|png)))"~',
        function (array $m) use ($src, &$hashes): string {
            $file = "$src/assets/{$m[3]}";
            if (!is_file($file)) {
                return $m[0];
            }
            $hashes[$file] ??= substr(sha1_file($file), 0, 10);
            return $m[1] . $m[2] . '?v=' . $hashes[$file] . '"';
        }, $html);
}

$changed = [];
$cssBefore = $cssAfter = 0;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS));
foreach ($files as $file) {
    if ($file->getFilename() === '.DS_Store') {
        continue;
    }
    $rel = substr($file->getPathname(), strlen($src) + 1);
    $data = (string)file_get_contents($file->getPathname());
    $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
    if ($ext === 'css') {
        $cssBefore += strlen($data);
        $data = minify_css($data);
        $cssAfter += strlen($data);
    } elseif ($ext === 'html') {
        $data = preg_replace_callback('~(<style\b[^>]*>)(.*?)(</style>)~is', function (array $m) use (&$cssBefore, &$cssAfter): string {
            $min = minify_css($m[2]);
            $cssBefore += strlen($m[2]);
            $cssAfter += strlen($min);
            return $m[1] . $min . $m[3];
        }, $data);
        $data = stamp_films($data, $src);
    }
    $target = "$root/$rel";
    if (is_file($target) && file_get_contents($target) === $data) {
        continue;
    }
    if (!is_dir(dirname($target))) {
        mkdir(dirname($target), 0755, true);
    }
    file_put_contents($target, $data);
    $changed[] = $rel;
}

printf("Served copy: %d file%s updated%s. CSS %.1f KB, minified to %.1f KB.\n", count($changed), count($changed) === 1 ? '' : 's',
    $changed ? ' (' . implode(', ', array_slice($changed, 0, 8)) . (count($changed) > 8 ? ', …' : '') . ')' : '',
    $cssBefore / 1024, $cssAfter / 1024);
