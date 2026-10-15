<?php
// The models on /can-my-mac-run-it/ and their real sizes:
//   php dev/llm-models.php             reads Ollama's registry again, then rewrites the page's table and data
//   php dev/llm-models.php --offline   only rewrites the table and data (after changing RULES), from content/llm-models.json
// Reads each model's manifest from Ollama's registry (exact bytes of the files Ollama loads, plus the parameter
// count and quality level it states) and writes content/llm-models.json, then fills the <!-- models --> parts of
// content/pages/can-my-mac-run-it.md. Then run php dev/build-blog.php to publish it.
// The list is Ollama's most popular families (ollama.com/library?sort=popular), at the sizes people use.
// "active" is for mixture-of-experts models, where only part of the model works on each word: from Ollama's
// own tag name (…-a3b) or the maker's model card. It sets the speed estimate, not the memory needed.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

// [Ollama tag, name shown, active billions (mixture of experts) or null]
const MODELS = [
    ['gemma4:e2b', 'Gemma 4 E2B', null], ['gemma4:e4b', 'Gemma 4 E4B', null], ['gemma4:12b', 'Gemma 4 12B', null],
    ['gemma4:26b', 'Gemma 4 26B A4B', 4], ['gemma4:31b', 'Gemma 4 31B', null],
    ['qwen3.8:27b', 'Qwen 3.8 27B', null],
    ['qwen3.6:27b', 'Qwen 3.6 27B', null], ['qwen3.6:35b-a3b', 'Qwen 3.6 35B A3B', 3],
    ['qwen3.5:0.8b', 'Qwen 3.5 0.8B', null], ['qwen3.5:2b', 'Qwen 3.5 2B', null], ['qwen3.5:4b', 'Qwen 3.5 4B', null],
    ['qwen3.5:9b', 'Qwen 3.5 9B', null], ['qwen3.5:27b', 'Qwen 3.5 27B', null], ['qwen3.5:35b-a3b', 'Qwen 3.5 35B A3B', 3],
    ['qwen3.5:122b-a10b', 'Qwen 3.5 122B A10B', 10],
    ['gpt-oss:20b', 'gpt-oss 20B', 3.6], ['gpt-oss:120b', 'gpt-oss 120B', 5.1],   // OpenAI's model card
    ['deepseek-r1:1.5b', 'DeepSeek-R1 1.5B', null], ['deepseek-r1:7b', 'DeepSeek-R1 7B', null], ['deepseek-r1:8b', 'DeepSeek-R1 8B', null],
    ['deepseek-r1:14b', 'DeepSeek-R1 14B', null], ['deepseek-r1:32b', 'DeepSeek-R1 32B', null], ['deepseek-r1:70b', 'DeepSeek-R1 70B', null],
    ['deepseek-r1:671b', 'DeepSeek-R1 671B', 37],                                   // DeepSeek's model card
    ['deepseek-v3:671b', 'DeepSeek-V3 671B', 37],
    ['llama3.2:1b', 'Llama 3.2 1B', null], ['llama3.2:3b', 'Llama 3.2 3B', null], ['llama3.1:8b', 'Llama 3.1 8B', null],
    ['llama3.3:70b', 'Llama 3.3 70B', null], ['llama3.1:405b', 'Llama 3.1 405B', null],
    ['llama4:latest', 'Llama 4 Scout', 17],                                          // Meta's model card
    ['mistral:7b', 'Mistral 7B', null], ['mistral-small3.2:24b', 'Mistral Small 3.2 24B', null], ['devstral:24b', 'Devstral 24B', null],
    ['phi4-mini:3.8b', 'Phi-4 mini 3.8B', null], ['phi4:14b', 'Phi-4 14B', null],
    ['qwen3-coder:30b', 'Qwen3-Coder 30B A3B', 3.3], ['qwen3-coder:480b', 'Qwen3-Coder 480B A35B', 35],   // Qwen's model cards
    ['gemma3:1b', 'Gemma 3 1B', null], ['gemma3:4b', 'Gemma 3 4B', null], ['gemma3:12b', 'Gemma 3 12B', null], ['gemma3:27b', 'Gemma 3 27B', null],
    ['qwen3:4b', 'Qwen3 4B', null], ['qwen3:8b', 'Qwen3 8B', null], ['qwen3:14b', 'Qwen3 14B', null], ['qwen3:30b-a3b', 'Qwen3 30B A3B', 3],
    ['qwen3:32b', 'Qwen3 32B', null], ['qwen3:235b-a22b', 'Qwen3 235B A22B', 22],
];

// How the calculator decides, in one place (the page's script reads these too). Every number is a real reading or
// Apple's own figure, except "well", which is our rule of thumb for leaving macOS and your apps enough room.
const RULES = [
    'reading' => 'a 14-inch MacBook Pro (2021) with M1 Max and 64 GB, on macOS 27.0.1 and Ollama 0.35.1, on October 15, 2026',
    'gpuShare' => 0.81,       // macOS let the GPU use 51.8 of 64 GB by default (Metal's recommendedMaxWorkingSetSize: 55,662,788,608 bytes)
    'well' => 0.65,           // "Runs well": the model needs at most 65% of memory, leaving the rest for macOS and apps
    'contextShare' => 0.20,   // room for the conversation: 20% of the model, at most 3 GB, plus 0.25 GB of working space.
    'contextMax' => 3e9,      //   Llama 3 8B (4.66 GB file) took 5.66 GB in memory with an 8,192-token conversation;
    'working' => 0.25e9,      //   this rule says 5.84 GB.
    'efficiency' => 0.46,     // Llama 3 8B wrote 39.5 tokens/s on M1 Max (400 GB/s): 46% of 400 GB/s ÷ 4.66 GB
    'bytesPerParam' => ['q4' => 0.61, 'q8' => 1.1, 'f16' => 2.0],   // Ollama's Q4_K_M Llama 3.1 8B and Q8_0 Llama 3.2 1B; 16-bit is 2 bytes
    'sizes' => [8, 16, 18, 24, 32, 36, 48, 64, 96, 128, 192, 256, 512],   // memory a Mac can have, in GB
];

$root = dirname(__DIR__);
$offline = in_array('--offline', $argv, true);
$get = function (string $url, bool $manifest = false): ?array {
    $accept = $manifest ? " -H 'Accept: application/vnd.docker.distribution.manifest.v2+json'" : '';
    $out = (string)shell_exec("curl -sL --max-time 20$accept " . escapeshellarg($url));
    return json_decode($out, true) ?: null;
};
$rows = [];
foreach ($offline ? [] : MODELS as [$tag, $name, $active]) {
    [$model, $size] = explode(':', $tag);
    $m = $get("https://registry.ollama.ai/v2/library/$model/manifests/$size", true);
    if (!$m || empty($m['layers'])) {
        fwrite(STDERR, "$tag: not found in Ollama's registry; skipped\n");
        continue;
    }
    // What Ollama loads into memory: the model, plus its image reader (projector) and draft model if it has them
    $bytes = 0;
    foreach ($m['layers'] as $layer) {
        if (in_array($layer['mediaType'], ['application/vnd.ollama.image.model', 'application/vnd.ollama.image.projector', 'application/vnd.ollama.image.draft'], true)) {
            $bytes += $layer['size'];
        }
    }
    $config = $get("https://registry.ollama.ai/v2/library/$model/blobs/{$m['config']['digest']}") ?? [];
    $rows[] = ['tag' => $tag, 'name' => $name, 'bytes' => $bytes, 'params' => $config['model_type'] ?? null,
               'quant' => $config['file_type'] ?? null, 'active' => $active];
    printf("%-22s %7.1f GB  %-8s %s\n", $tag, $bytes / 1e9, $config['model_type'] ?? '?', $config['file_type'] ?? '?');
    usleep(150000);
}
if (!$offline) {
    $data = ['checked' => date('Y-m-d'), 'source' => 'Ollama library (registry.ollama.ai), default download of each tag', 'models' => $rows];
    file_put_contents("$root/content/llm-models.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    echo count($rows) . " models written to content/llm-models.json\n";
}
$data = json_decode((string)file_get_contents("$root/content/llm-models.json"), true);

// The page: a table every reader and search engine sees, and the same data for the calculator's script
$need = fn(float $b) => $b + min(RULES['contextShare'] * $b, RULES['contextMax']) + RULES['working'];
$smallest = function (float $need, float $share): string {
    foreach (RULES['sizes'] as $gb) {
        if ($need <= $share * $gb * 1073741824) { return "$gb GB"; }
    }
    return 'more than 512 GB';
};
$gb = fn(float $b) => number_format($b / 1e9, 1) . ' GB';
$models = $data['models'];
usort($models, fn($a, $b) => $a['bytes'] <=> $b['bytes']);
$table = "| Model | Download | Memory it needs | Runs well on | Fits, tightly, on |\n| --- | --- | --- | --- | --- |\n";
foreach ($models as $m) {
    $n = $need($m['bytes']);
    $kind = $m['active'] ? " (mixture of experts, {$m['active']}B active)" : '';
    $table .= "| {$m['name']}$kind | {$gb($m['bytes'])} | {$gb($n)} | {$smallest($n, RULES['well'])} | {$smallest($n, RULES['gpuShare'])} |\n";
}
$json = json_encode(['checked' => $data['checked'], 'rules' => RULES,
    'models' => array_map(fn($m) => [$m['name'], $m['bytes'], $m['active'] ? $m['active'] / (float)$m['params'] : 1, $m['tag']], $models)],
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$page = "$root/content/pages/can-my-mac-run-it.md";
$md = (string)file_get_contents($page);
$md = preg_replace('~<!-- models:table -->.*?<!-- /models:table -->~s', "<!-- models:table -->\n" . str_replace('$', '\\$', $table) . "<!-- /models:table -->", $md);
$md = preg_replace('~<!-- models:data -->.*?<!-- /models:data -->~s', '<!-- models:data --><script type="application/json" id="llm-data">' . str_replace('$', '\\$', $json) . '</script><!-- /models:data -->', $md);
file_put_contents($page, $md);
echo "Wrote the table and data into content/pages/can-my-mac-run-it.md\n";
