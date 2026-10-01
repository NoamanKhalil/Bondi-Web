<?php
// "Ask AI about Bondi" in every page's footer: links that open each assistant with this question already typed.
// The homepage gets a footer column (logo and name); the other pages get a row of logos in the footer line.
// Nothing loads from those services until someone clicks, the logos are drawn inline (dev/ai-logos, MIT, from
// Lobe Icons; the marks belong to their owners), and the links carry no tracking.
// Change the question or the list here, then run: php dev/ask-ai.php
// It rewrites what's between <!-- ask-ai:column --> / <!-- ask-ai:row --> and their closing markers on each page.
// Re-check the competitors named here, and that each link still opens its assistant, every few months.

$question = "What is Bondi for Mac (trybondi.app), and how does it compare with Activity Monitor, iStat Menus, Stats and "
          . "Vitals for finding out why a Mac is slow? Bondi's website has a summary for AI assistants at "
          . "https://trybondi.app/llms.txt, and a full version at https://trybondi.app/llms-full.txt.";
$services = [ // name => [logo file in dev/ai-logos, address the question is added to]
    'ChatGPT' => ['openai', 'https://chatgpt.com/?q='],
    'Claude' => ['claude', 'https://claude.ai/new?q='],
    'Perplexity' => ['perplexity', 'https://www.perplexity.ai/search?q='],
    'Google AI Mode' => ['google', 'https://www.google.com/search?udm=50&q='],
    'Grok' => ['grok', 'https://grok.com/?q='],
];
$note = 'Each opens with the question: what is Bondi, and how does it compare with Activity Monitor, iStat Menus, Stats and Vitals?';

$h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
$logo = function (string $file): string {
    $svg = (string)file_get_contents(__DIR__ . "/ai-logos/$file.svg");
    $svg = preg_replace('~<title>.*?</title>~s', '', $svg);
    $svg = preg_replace('~\s(height|width|style)="[^"]*"~', '', $svg);
    return str_replace('<svg ', '<svg class="ai-logo" aria-hidden="true" focusable="false" ', trim($svg));
};
$tip = $h("Asks: $question");

$column = '<!-- ask-ai:column --><div class="askai-col"><h4>Ask AI about Bondi</h4><ul>';
$row = '<!-- ask-ai:row --><div class="askai-row" role="group" aria-label="Ask AI about Bondi"><span class="askai-label" title="' . $tip . '">Ask AI about Bondi</span>';
foreach ($services as $name => [$file, $url]) {
    $href = $h($url . rawurlencode($question));
    $column .= '<li><a href="' . $href . '" target="_blank" rel="noopener noreferrer" title="' . $tip . '">' . $logo($file) . $h($name)
             . '<span class="vh"> (opens in a new tab)</span></a></li>';
    $row .= '<a href="' . $href . '" target="_blank" rel="noopener noreferrer" title="' . $h($name) . '" aria-label="'
          . $h("Ask $name about Bondi (opens in a new tab)") . '">' . $logo($file) . '</a>';
}
$column .= '</ul><p class="askai-note">' . $h($note) . '</p></div><!-- /ask-ai:column -->';
$row .= '</div><!-- /ask-ai:row -->';

$root = dirname(__DIR__);
$pages = ['index.html' => 'column', 'beta/index.html' => 'row', 'privacy/index.html' => 'row', 'terms/index.html' => 'row',
          'eula/index.html' => 'row', 'refunds/index.html' => 'row', '404.html' => 'row'];
foreach ($pages as $page => $kind) {
    foreach (["$root/public_html/$page", "$root/$page"] as $file) {
        $html = file_get_contents($file);
        $new = preg_replace("~<!-- ask-ai:$kind -->.*?<!-- /ask-ai:$kind -->~s", $kind === 'column' ? $column : $row, $html, -1, $found);
        if ($found !== 1) {
            fwrite(STDERR, "No ask-ai:$kind markers in $file\n");
            exit(1);
        }
        file_put_contents($file, $new);
    }
}
echo 'Ask AI links updated on ' . count($pages) . " pages.\n";
