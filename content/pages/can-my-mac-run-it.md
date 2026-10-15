---
title: Can my Mac run it? LLM RAM calculator for Mac
crumb: Can my Mac run it?
description: Pick your Mac's memory and chip to see which local AI models it can run, how much RAM each LLM needs, and how fast: Llama, Qwen, Gemma, DeepSeek and gpt-oss.
answer: A local AI model needs about its download size in memory, plus up to 20% for the conversation. An 8 GB Mac runs models up to about 7B parameters, 16 GB up to 14B, 32 GB up to about 30B, and 64 GB runs 70B models, tightly. How fast it writes depends on the chip's memory bandwidth.
checked: 2026-10-15
checkednote: Model sizes checked against Ollama's library
updated: 2026-10-15
---
<!-- web -->
<style>
.calc { --good: #248a3d; --warn: #c76e00; --bad: #d70015; margin: 30px 0 34px; padding: 26px 24px 22px; border-radius: 22px; background: var(--tile); box-shadow: var(--shadow); display: grid; gap: 16px; }
@media (prefers-color-scheme: dark) { :root:not([data-theme="light"]) .calc { --good: #30d158; --warn: #ff9f0a; --bad: #ff453a; } }
:root[data-theme="dark"] .calc { --good: #30d158; --warn: #ff9f0a; --bad: #ff453a; }
.calc-pick { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(220px, 100%), 1fr)); gap: 12px; }
.calc label { display: grid; gap: 6px; font-size: .85rem; font-weight: 600; color: var(--soft); }
.calc select, .calc input { font: 500 1rem/1.3 var(--sans); color: var(--ink); background: var(--tile-2); border: 1px solid var(--line); border-radius: 12px; padding: 11px 12px; width: 100%; min-width: 0; }
.calc select:focus-visible, .calc input:focus-visible { outline: 2px solid var(--bondi); outline-offset: 1px; }
.calc-how { margin: -6px 0 0; font-size: .82rem; color: var(--faint); }
.calc-sum { margin: 0; font: 600 1.15rem/1.4 var(--display); letter-spacing: -0.015em; }
.calc-group h3 { display: flex; align-items: center; gap: 8px; margin: 0 0 8px; font-size: .9rem; color: var(--soft); }
.calc-group h3::before { content: ""; width: 9px; height: 9px; border-radius: 50%; background: var(--dot); }
.calc-group[data-v="well"] { --dot: var(--good); } .calc-group[data-v="tight"] { --dot: var(--warn); } .calc-group[data-v="no"] { --dot: var(--bad); }
#calc-groups { display: grid; gap: 18px; }
.calc-group ul { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 6px; }
.calc-group li { margin: 0; display: inline-flex; gap: 6px; align-items: baseline; padding: 5px 11px; border-radius: 980px; background: var(--tile-2); border: 1px solid var(--line); font-size: .82rem; line-height: 1.3; }
.calc-group li b { font-weight: 600; color: var(--ink); } .calc-group li span { color: var(--faint); font-variant-numeric: tabular-nums; }
.calc-group[data-v="no"] li { opacity: .75; }
.calc-group summary { cursor: pointer; list-style: none; } .calc-group summary::-webkit-details-marker { display: none; }
.calc-group summary h3 { display: inline-flex; margin: 0; } .calc-group summary .more { margin-left: 8px; font-size: .85rem; color: var(--bondi-ink); font-weight: 600; }
.calc-group details[open] .more { display: none; } .calc-group details ul { margin-top: 8px; }
.calc-custom { border-top: 1px solid var(--line); padding-top: 16px; display: grid; gap: 10px; }
.calc-custom .row { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(200px, 100%), 1fr)); gap: 12px; }
.calc-custom p { margin: 0; font-size: .95rem; color: var(--soft); }
.calc-custom p b { color: var(--ink); }
.calc-note { margin: 0; font-size: .82rem; color: var(--faint); }
.calc-bondi { margin: 0; padding: 12px 16px; border-radius: 14px; background: var(--bondi-soft); font-size: .92rem; }
.calc-bondi a { color: var(--bondi-ink); font-weight: 600; }
@media (max-width: 640px) { .calc { padding: 20px 16px 18px; } }
</style>
<section class="calc" aria-label="LLM RAM calculator">
<div class="calc-pick">
<label for="calc-ram">Your Mac's memory
<select id="calc-ram"><option>8</option><option selected>16</option><option>18</option><option>24</option><option>32</option><option>36</option><option>48</option><option>64</option><option>96</option><option>128</option><option>192</option><option>256</option><option>512</option></select></label>
<label for="calc-chip">Its chip (for speed)
<select id="calc-chip"><option value="">Not sure</option><option value="68">M1</option><option value="200">M1 Pro</option><option value="400">M1 Max</option><option value="800">M1 Ultra</option><option value="100">M2</option><option value="200">M2 Pro</option><option value="400">M2 Max</option><option value="800">M2 Ultra</option><option value="100">M3</option><option value="150">M3 Pro</option><option value="300">M3 Max (30-core GPU)</option><option value="400">M3 Max (40-core GPU)</option><option value="819">M3 Ultra</option><option value="120">M4</option><option value="273">M4 Pro</option><option value="410">M4 Max (32-core GPU)</option><option value="546">M4 Max (40-core GPU)</option><option value="153">M5</option><option value="307">M5 Pro</option><option value="460">M5 Max (32-core GPU)</option><option value="614">M5 Max (40-core GPU)</option><option value="1200">M5 Ultra</option></select></label>
</div>
<p class="calc-how">Not sure? Open the Apple menu and choose About This Mac: it shows the chip and the memory.</p>
<p class="calc-sum" id="calc-sum" aria-live="polite">Pick your Mac's memory to see which models it can run.</p>
<div id="calc-groups"></div>
<div class="calc-custom">
<div class="row">
<label for="calc-params">Another model? Its size, in billions of parameters
<input id="calc-params" type="number" inputmode="decimal" min="0.1" max="2000" step="any" placeholder="For example 70"></label>
<label for="calc-quant">Quality
<select id="calc-quant"><option value="q4">4-bit (Q4, the usual download)</option><option value="q8">8-bit (Q8)</option><option value="f16">16-bit (full size)</option></select></label>
</div>
<p id="calc-custom-out" aria-live="polite">The size is in the model's name: the 70B in "Llama 3.3 70B" means 70 billion.</p>
</div>
<p class="calc-note" id="calc-note"></p>
<p class="calc-bondi">Running one? <a href="/beta/">Bondi</a> shows each loaded model in Ollama or LM Studio with the memory it really holds, and unloads it when you're done.</p>
</section>
<!-- models:data --><script type="application/json" id="llm-data">{"checked":"2026-10-15","rules":{"reading":"a 14-inch MacBook Pro (2021) with M1 Max and 64 GB, on macOS 27.0.1 and Ollama 0.35.1, on October 15, 2026","gpuShare":0.81,"well":0.65,"contextShare":0.2,"contextMax":3000000000,"working":250000000,"efficiency":0.46,"bytesPerParam":{"q4":0.61,"q8":1.1,"f16":2},"sizes":[8,16,18,24,32,36,48,64,96,128,192,256,512]},"models":[["Gemma 3 1B",806058400,1,"gemma3:1b"],["DeepSeek-R1 1.5B",1117320512,1,"deepseek-r1:1.5b"],["Llama 3.2 1B",1321082688,1,"llama3.2:1b"],["Qwen 3.5 0.8B",1322057600,1,"qwen3.5:0.8b"],["Llama 3.2 3B",2019377376,1,"llama3.2:3b"],["Phi-4 mini 3.8B",2491874624,1,"phi4-mini:3.8b"],["Qwen3 4B",2497280480,1,"qwen3:4b"],["Qwen 3.5 2B",2683385216,1,"qwen3.5:2b"],["Qwen 3.5 4B",3324162336,1,"qwen3.5:4b"],["Gemma 3 4B",3350263392,1,"gemma3:4b"],["Mistral 7B",4372811712,1,"mistral:7b"],["Gemma 4 E2B",4587144832,1,"gemma4:e2b"],["DeepSeek-R1 7B",4683073184,1,"deepseek-r1:7b"],["Llama 3.1 8B",4920738944,1,"llama3.1:8b"],["DeepSeek-R1 8B",5225373760,1,"deepseek-r1:8b"],["Qwen3 8B",5225374496,1,"qwen3:8b"],["Qwen 3.5 9B",6550813952,1,"qwen3.5:9b"],["Gemma 4 E4B",6583644832,1,"gemma4:e4b"],["Gemma 4 12B",8021608448,1,"gemma4:12b"],["Gemma 3 12B",8164030720,1,"gemma3:12b"],["DeepSeek-R1 14B",8988109952,1,"deepseek-r1:14b"],["Phi-4 14B",9053114464,1,"phi4:14b"],["Qwen3 14B",9276184896,1,"qwen3:14b"],["gpt-oss 20B",13793422816,0.1722488038277512,"gpt-oss:20b"],["Devstral 24B",14333909728,1,"devstral:24b"],["Mistral Small 3.2 24B",15177369888,1,"mistral-small3.2:24b"],["Gemma 3 27B",17413400640,1,"gemma3:27b"],["Qwen 3.5 27B",17478544992,1,"qwen3.5:27b"],["Qwen 3.8 27B",17741860480,1,"qwen3.8:27b"],["Qwen 3.6 27B",17769065248,1,"qwen3.6:27b"],["Qwen3 30B A3B",18556685856,0.09836065573770492,"qwen3:30b-a3b"],["Qwen3-Coder 30B A3B",18556688736,0.10819672131147541,"qwen3-coder:30b"],["Gemma 4 26B A4B",18731013952,0.15873015873015872,"gemma4:26b"],["DeepSeek-R1 32B",19851335552,1,"deepseek-r1:32b"],["Qwen3 32B",20201240160,1,"qwen3:32b"],["Gemma 4 31B",20385446560,1,"gemma4:31b"],["Qwen 3.5 35B A3B",22069580576,0.08645533141210374,"qwen3.5:35b-a3b"],["Qwen 3.6 35B A3B",22621302688,0.08450704225352113,"qwen3.6:35b-a3b"],["DeepSeek-R1 70B",42520395616,1,"deepseek-r1:70b"],["Llama 3.3 70B",42520398528,1,"llama3.3:70b"],["gpt-oss 120B",65369800832,0.04366438356164384,"gpt-oss:120b"],["Llama 4 Scout",67436800960,0.1565377532228361,"llama4:latest"],["Qwen 3.5 122B A10B",81370024480,0.07993605115907275,"qwen3.5:122b-a10b"],["Qwen3 235B A22B",142154073664,0.09357720119098256,"qwen3:235b-a22b"],["Llama 3.1 405B",243069610720,1,"llama3.1:405b"],["Qwen3-Coder 480B A35B",290058824416,0.0728862973760933,"qwen3-coder:480b"],["DeepSeek-V3 671B",404430186016,0.05514157973174367,"deepseek-v3:671b"],["DeepSeek-R1 671B",404430186848,0.05514157973174367,"deepseek-r1:671b"]]}</script><!-- /models:data -->
<script>
(() => {
  const data = JSON.parse(document.getElementById('llm-data').textContent);
  const R = data.rules, GiB = 1073741824;
  const $ = id => document.getElementById(id);
  const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);
  const need = b => b + Math.min(R.contextShare * b, R.contextMax) + R.working;
  const gb = b => (b / 1e9).toFixed(1) + ' GB';
  const verdict = (n, ram) => n <= R.well * ram ? 'well' : n <= R.gpuShare * ram ? 'tight' : 'no';
  const smallest = (n, share) => { const s = R.sizes.find(g => n <= share * g * GiB); return s ? s + ' GB' : 'more than 512 GB'; };
  // Writing speed: each new word reads the working part of the model from memory once
  const speed = (b, active, bw) => { const s = R.efficiency * bw * 1e9 / (b * active); return s >= 100 ? '100+ tokens/s' : '~' + (s >= 20 ? Math.round(s / 5) * 5 : Math.max(1, Math.round(s))) + ' tokens/s'; };
  const titles = { well: 'Runs well', tight: 'Fits, tightly: close other apps first', no: "Won't fit in memory" };
  const render = () => {
    const ramGb = +$('calc-ram').value, ram = ramGb * GiB, bw = +$('calc-chip').value;
    const groups = { well: [], tight: [], no: [] };
    for (const [name, bytes, active] of data.models) groups[verdict(need(bytes), ram)].push([name, bytes, active]);
    groups.well.reverse(); // biggest first: the best a Mac can do is the interesting part
    $('calc-sum').textContent = `With ${ramGb} GB, ${groups.well.length} of these ${data.models.length} popular models run well`
      + (groups.tight.length ? ` and ${groups.tight.length} more fit, tightly.` : '.');
    // The models that won't fit stay folded away, so the ones that do stay in view
    const open = !!document.querySelector('.calc-group details[open]');
    $('calc-groups').innerHTML = Object.entries(groups).filter(([, list]) => list.length).map(([v, list]) => {
      const chips = '<ul>' + list.map(([name, bytes, active]) =>
        `<li><b>${esc(name)}</b><span>${gb(bytes)}${bw && v !== 'no' ? ' · ' + speed(bytes, active, bw) : ''}</span></li>`).join('') + '</ul>';
      const head = `<h3>${titles[v]} (${list.length})</h3>`;
      return `<div class="calc-group" data-v="${v}">` + (v === 'no'
        ? `<details${open ? ' open' : ''}><summary>${head}<span class="more">Show</span></summary>${chips}</details>` : head + chips) + '</div>';
    }).join('');
    $('calc-note').textContent = (bw ? 'Speeds are estimates from the chip\'s memory bandwidth (Apple\'s figure), checked against a real reading. ' : 'Pick the chip to see how fast each model writes. ')
      + 'Sizes are Ollama\'s default downloads, checked ' + new Date(data.checked + 'T12:00:00Z').toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) + '.';
    const p = parseFloat($('calc-params').value);
    if (p > 0) {
      const bytes = p * 1e9 * R.bytesPerParam[$('calc-quant').value], n = need(bytes), v = verdict(n, ram);
      const said = { well: `runs well on your ${ramGb} GB Mac`, tight: `fits on your ${ramGb} GB Mac, tightly`, no: `won't fit in your ${ramGb} GB Mac` }[v];
      $('calc-custom-out').innerHTML = `About <b>${gb(bytes)}</b> to download and <b>${gb(n)}</b> of memory: it <b>${said}</b>`
        + (bw && v !== 'no' ? `, at ${speed(bytes, 1, bw)} if all of it works on each word` : '')
        + `. It runs well on ${smallest(n, R.well)} and fits, tightly, on ${smallest(n, R.gpuShare)}.`;
    }
  };
  ['calc-ram', 'calc-chip', 'calc-quant'].forEach(id => $(id).addEventListener('change', render));
  $('calc-params').addEventListener('input', render);
  render();
})();
</script>
<!-- /web -->
Pick your Mac's memory, and its chip if you know it. The calculator sorts 48 popular local AI models into three groups: the ones that run well, the ones that fit if you close other apps, and the ones that won't fit. Every size is the real download from Ollama's library, and the rules come from real readings on a Mac, described below.

## How much RAM do you need to run a local LLM?

A local AI model (an LLM, or large language model) has to sit in your Mac's memory while it works. So the first number to know is its download size: Llama 3.1 8B is 4.9 GB in Ollama, Llama 3.3 70B is 42.5 GB.

On top of that, it needs room for the conversation (the context) and a little working space. In our reading, Dolphin Llama 3 8B (a 4.66 GB download) took **5.66 GB** of memory with an 8,192-token conversation. The calculator allows 20% of the model, up to 3 GB, plus 0.25 GB, which comes to 5.84 GB for that model. Longer conversations need more.

Then there's how much of the memory the model can use. Macs share one pool of memory between the processor and the graphics chip, which is why they're good at this. But macOS keeps part of it back: on our 64 GB M1 Max, it let the graphics chip use **51.8 GB (81%)** by default. Macs with less memory may get a smaller share.

So the calculator says:

- **Runs well:** the model needs no more than 65% of your memory, leaving the rest for macOS and your apps.
- **Fits, tightly:** it needs up to 81%. It loads, but close other apps first, or your Mac will start to swap and everything slows down.
- **Won't fit:** more than that. Ollama can still run part of it on the processor instead, but it's many times slower.

## How fast will it run?

Every new word (a token, about three-quarters of a word) means reading the working part of the model from memory once. So speed depends on the chip's **memory bandwidth**, which Apple publishes for each chip: 120 GB/s for M4, 273 GB/s for M4 Pro, up to 546 GB/s for M4 Max, and 1.2 TB/s for M5 Ultra.

In our readings, on an M1 Max (400 GB/s):

| Model | Download | In memory | Writing speed |
| --- | --- | --- | --- |
| Dolphin Llama 3 8B (Q4_0) | 4.66 GB | 5.66 GB, with an 8,192-token conversation | 39.5 tokens/s |
| Qwen2.5 0.5B (Q8_0) | 0.40 GB | 0.93 GB, with a 32,768-token conversation | 218 tokens/s |

The 8B model reached 46% of the theoretical limit (400 GB/s ÷ 4.66 GB), so the calculator's speeds are 46% of each chip's limit. Treat them as estimates: very small models are held back by other work, and long conversations slow every model down. Reading speed is about 5 tokens a second, so anything above 20 feels quick.

Mixture-of-experts models, such as gpt-oss and Qwen3 30B A3B, are a special case. All of the model has to fit in memory, but only a small part of it works on each word, so they write much faster than their size suggests.

## Every model, and the Mac it needs

Sizes are Ollama's default download of each model (usually 4-bit, Q4_K_M), checked on October 15, 2026. LM Studio's versions of the same models can be a little bigger or smaller.

<!-- models:table -->
| Model | Download | Memory it needs | Runs well on | Fits, tightly, on |
| --- | --- | --- | --- | --- |
| Gemma 3 1B | 0.8 GB | 1.2 GB | 8 GB | 8 GB |
| DeepSeek-R1 1.5B | 1.1 GB | 1.6 GB | 8 GB | 8 GB |
| Llama 3.2 1B | 1.3 GB | 1.8 GB | 8 GB | 8 GB |
| Qwen 3.5 0.8B | 1.3 GB | 1.8 GB | 8 GB | 8 GB |
| Llama 3.2 3B | 2.0 GB | 2.7 GB | 8 GB | 8 GB |
| Phi-4 mini 3.8B | 2.5 GB | 3.2 GB | 8 GB | 8 GB |
| Qwen3 4B | 2.5 GB | 3.2 GB | 8 GB | 8 GB |
| Qwen 3.5 2B | 2.7 GB | 3.5 GB | 8 GB | 8 GB |
| Qwen 3.5 4B | 3.3 GB | 4.2 GB | 8 GB | 8 GB |
| Gemma 3 4B | 3.4 GB | 4.3 GB | 8 GB | 8 GB |
| Mistral 7B | 4.4 GB | 5.5 GB | 8 GB | 8 GB |
| Gemma 4 E2B | 4.6 GB | 5.8 GB | 16 GB | 8 GB |
| DeepSeek-R1 7B | 4.7 GB | 5.9 GB | 16 GB | 8 GB |
| Llama 3.1 8B | 4.9 GB | 6.2 GB | 16 GB | 8 GB |
| DeepSeek-R1 8B | 5.2 GB | 6.5 GB | 16 GB | 8 GB |
| Qwen3 8B | 5.2 GB | 6.5 GB | 16 GB | 8 GB |
| Qwen 3.5 9B | 6.6 GB | 8.1 GB | 16 GB | 16 GB |
| Gemma 4 E4B | 6.6 GB | 8.2 GB | 16 GB | 16 GB |
| Gemma 4 12B | 8.0 GB | 9.9 GB | 16 GB | 16 GB |
| Gemma 3 12B | 8.2 GB | 10.0 GB | 16 GB | 16 GB |
| DeepSeek-R1 14B | 9.0 GB | 11.0 GB | 16 GB | 16 GB |
| Phi-4 14B | 9.1 GB | 11.1 GB | 16 GB | 16 GB |
| Qwen3 14B | 9.3 GB | 11.4 GB | 18 GB | 16 GB |
| gpt-oss 20B (mixture of experts, 3.6B active) | 13.8 GB | 16.8 GB | 32 GB | 24 GB |
| Devstral 24B | 14.3 GB | 17.5 GB | 32 GB | 24 GB |
| Mistral Small 3.2 24B | 15.2 GB | 18.4 GB | 32 GB | 24 GB |
| Gemma 3 27B | 17.4 GB | 20.7 GB | 32 GB | 24 GB |
| Qwen 3.5 27B | 17.5 GB | 20.7 GB | 32 GB | 24 GB |
| Qwen 3.8 27B | 17.7 GB | 21.0 GB | 32 GB | 32 GB |
| Qwen 3.6 27B | 17.8 GB | 21.0 GB | 32 GB | 32 GB |
| Qwen3 30B A3B (mixture of experts, 3B active) | 18.6 GB | 21.8 GB | 32 GB | 32 GB |
| Qwen3-Coder 30B A3B (mixture of experts, 3.3B active) | 18.6 GB | 21.8 GB | 32 GB | 32 GB |
| Gemma 4 26B A4B (mixture of experts, 4B active) | 18.7 GB | 22.0 GB | 32 GB | 32 GB |
| DeepSeek-R1 32B | 19.9 GB | 23.1 GB | 36 GB | 32 GB |
| Qwen3 32B | 20.2 GB | 23.5 GB | 36 GB | 32 GB |
| Gemma 4 31B | 20.4 GB | 23.6 GB | 36 GB | 32 GB |
| Qwen 3.5 35B A3B (mixture of experts, 3B active) | 22.1 GB | 25.3 GB | 48 GB | 32 GB |
| Qwen 3.6 35B A3B (mixture of experts, 3B active) | 22.6 GB | 25.9 GB | 48 GB | 32 GB |
| DeepSeek-R1 70B | 42.5 GB | 45.8 GB | 96 GB | 64 GB |
| Llama 3.3 70B | 42.5 GB | 45.8 GB | 96 GB | 64 GB |
| gpt-oss 120B (mixture of experts, 5.1B active) | 65.4 GB | 68.6 GB | 128 GB | 96 GB |
| Llama 4 Scout (mixture of experts, 17B active) | 67.4 GB | 70.7 GB | 128 GB | 96 GB |
| Qwen 3.5 122B A10B (mixture of experts, 10B active) | 81.4 GB | 84.6 GB | 128 GB | 128 GB |
| Qwen3 235B A22B (mixture of experts, 22B active) | 142.2 GB | 145.4 GB | 256 GB | 192 GB |
| Llama 3.1 405B | 243.1 GB | 246.3 GB | 512 GB | 512 GB |
| Qwen3-Coder 480B A35B (mixture of experts, 35B active) | 290.1 GB | 293.3 GB | 512 GB | 512 GB |
| DeepSeek-V3 671B (mixture of experts, 37B active) | 404.4 GB | 407.7 GB | more than 512 GB | 512 GB |
| DeepSeek-R1 671B (mixture of experts, 37B active) | 404.4 GB | 407.7 GB | more than 512 GB | 512 GB |
<!-- /models:table -->

## Questions

### How much RAM do I need to run a local LLM?

About the model's download size, plus up to 20% for the conversation. As a rule of thumb, an 8 GB Mac runs models up to about 7B parameters, 16 GB up to 14B, 32 GB up to about 30B, and 64 GB runs 70B models, tightly. The calculator above checks 48 popular models against your Mac's memory.

### What LLM can I run on my Mac?

It depends on your Mac's memory. With 8 GB: Llama 3.2 3B, Gemma 3 4B, Qwen 3.5 4B or Mistral 7B. With 16 GB: Qwen 3.5 9B, Gemma 4 12B, Phi-4 14B or DeepSeek-R1 14B. With 32 GB: gpt-oss 20B, Gemma 3 27B, Qwen 3.6 27B or Qwen3 30B A3B. With 64 GB: Llama 3.3 70B or DeepSeek-R1 70B, tightly. With 128 GB: gpt-oss 120B or Qwen 3.5 122B A10B.

### Can a Mac mini run LLMs?

Yes. A Mac mini uses the same unified memory as every Apple silicon Mac, so what it can run depends only on its memory and chip: with 16 GB, models up to about 14B parameters; with 64 GB, 70B models fit, tightly. Pick its memory and chip in the calculator above.

### How much RAM do I need for Llama 3.3 70B?

Llama 3.3 70B is a 42.5 GB download in Ollama and needs about 45.8 GB of memory. It fits, tightly, on a 64 GB Mac and runs well on 96 GB or more. On an M1 Max it would write about 4 tokens a second, by our estimate.

### Is 8 GB of RAM enough to run an LLM?

For small models, yes. An 8 GB Mac runs models such as Llama 3.2 3B, Gemma 3 4B and Mistral 7B well, and fits most other 7B and 8B models, such as Llama 3.1 8B, tightly: close other apps first.

### Why does my Mac get slow when a model is loaded?

A loaded model holds its memory even when you're not using it. If it takes most of your Mac's memory, macOS starts compressing memory and swapping to disk, and every app slows down. Quit or unload the model when you're done. [Bondi](https://trybondi.app/) shows each loaded model in Ollama or LM Studio with the memory it holds, and can unload it.

### Does this page check my Mac?

No. Websites can't see how much memory a Mac has, so you choose it. Everything is worked out in your browser, and nothing is sent anywhere.
