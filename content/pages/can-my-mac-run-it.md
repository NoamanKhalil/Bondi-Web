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
/* A pocket calculator: the same graphite body and keys in light and dark, like a real object; the model list prints on a paper tape */
.calc-wrap { margin: 30px 0 34px; display: grid; justify-items: center; }
.calc { --good: #30d158; --warn: #ff9f0a; --bad: #ff453a; --glow: #5fd4ea; width: 100%; max-width: 720px; padding: 22px 22px 24px; border-radius: 34px; color: #f5f5f7;
  background: linear-gradient(160deg, #3a3a3e 0%, #232326 55%, #1b1b1d 100%); box-shadow: inset 0 1px 0 rgba(255,255,255,.16), inset 0 -2px 0 rgba(0,0,0,.4), 0 2px 0 #0e0e0f, 0 30px 60px -24px rgba(0,0,0,.55);
  display: grid; gap: 18px; position: relative; z-index: 1; font-family: var(--sans); }
.calc-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 0 4px; }
.calc .calc-brand { margin: 0; display: flex; align-items: center; gap: 8px; font: 700 .78rem/1 var(--sans); letter-spacing: .16em; text-transform: uppercase; color: #a9a9ae; }
.calc .calc-brand img { display: block; margin: 0; width: 20px; height: 20px; border-radius: 5px; box-shadow: none; }
.calc .calc-brand span { color: #6f6f75; font-weight: 600; }
.calc-solar { display: flex; gap: 2px; padding: 3px; border-radius: 6px; background: #121214; box-shadow: inset 0 1px 2px rgba(0,0,0,.8), 0 1px 0 rgba(255,255,255,.08); }
.calc-solar i { width: 18px; height: 12px; border-radius: 2px; background: linear-gradient(180deg, #3b2f26, #241c16); }
.calc-screen { position: relative; border-radius: 18px; padding: 16px 20px 16px 22px; min-height: 148px; background: radial-gradient(120% 140% at 70% 0%, #0f2a30 0%, #071114 60%); box-shadow: inset 0 2px 10px rgba(0,0,0,.9), 0 1px 0 rgba(255,255,255,.1); display: grid; align-content: space-between; gap: 6px; overflow: hidden; }
.calc-screen .line { display: flex; justify-content: space-between; gap: 10px; font: 500 .82rem/1.2 var(--mono); letter-spacing: .04em; color: #7fb9c4; text-transform: uppercase; }
.calc-screen .big { text-align: right; font: 300 clamp(2.6rem, 9vw, 3.6rem)/1 var(--display); letter-spacing: -0.02em; color: var(--glow); text-shadow: 0 0 18px rgba(95,212,234,.45); font-variant-numeric: tabular-nums; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.calc-screen .big small { font-size: .42em; font-weight: 500; letter-spacing: 0; margin-left: 8px; color: #9fd9e4; text-shadow: none; }
.calc-screen .line.bottom { text-transform: none; letter-spacing: 0; font: 500 .9rem/1.35 var(--sans); color: #b9dfe6; justify-content: flex-end; text-align: right; }
.calc-led { position: absolute; left: 12px; top: 50%; width: 6px; height: 46px; margin-top: -23px; border-radius: 3px; background: var(--led, #2a3a3e); box-shadow: 0 0 12px var(--led, transparent); }
.calc-body { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr); gap: 18px; }
.calc-panel { display: grid; gap: 10px; align-content: start; }
.calc-panel h3 { margin: 4px 2px 0; font: 700 .68rem/1 var(--sans); letter-spacing: .16em; text-transform: uppercase; color: #8b8b91; }
.calc-panel .hint { margin: -2px 2px 0; font-size: .74rem; line-height: 1.35; color: #77777d; }
.keys { display: grid; gap: 8px; }
.keys.mem { grid-template-columns: repeat(5, minmax(0, 1fr)); } .keys.gen { grid-template-columns: repeat(5, minmax(0, 1fr)); }
.keys.tier { grid-template-columns: repeat(4, minmax(0, 1fr)); } .keys.cores { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.keys.pad { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.keys[hidden] { display: none; }
.key { min-width: 0; height: 46px; border: 0; border-radius: 12px; cursor: pointer; font: 600 1rem/1 var(--sans); color: #f5f5f7; letter-spacing: -0.01em;
  background: linear-gradient(180deg, #4a4a4f, #36363a); box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 4px 0 #161618, 0 6px 10px rgba(0,0,0,.45); transition: transform .06s, box-shadow .06s, background .12s; -webkit-tap-highlight-color: transparent; }
.key.num { font-size: 1.25rem; font-weight: 500; height: 54px; background: linear-gradient(180deg, #5a5a60, #444449); }
.key.fn { font-size: .85rem; color: #d7d7dc; }
.key.clear { color: #ff8a80; }
.key.eq { grid-column: 1 / -1; height: 52px; font-size: 1.5rem; color: #031418; background: linear-gradient(180deg, #5fd4ea, #2cc0de); box-shadow: inset 0 1px 0 rgba(255,255,255,.45), 0 4px 0 #157f95, 0 6px 12px rgba(0,0,0,.45); }
.key[aria-pressed="true"] { color: #031418; background: linear-gradient(180deg, #8fe3f2, #5fd4ea); box-shadow: inset 0 1px 0 rgba(255,255,255,.5), 0 2px 0 #157f95, 0 3px 6px rgba(0,0,0,.4); transform: translateY(2px); }
.key:active, .key.is-down { transform: translateY(3px); box-shadow: inset 0 1px 0 rgba(255,255,255,.12), 0 1px 0 #161618, 0 2px 4px rgba(0,0,0,.4); }
.key:disabled { opacity: .35; cursor: default; }
.key:focus-visible { outline: 2px solid #5fd4ea; outline-offset: 2px; }
.calc-vh { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
/* The tape */
.tape { --ink: #2a2a2c; --faint: #8b8a84; --good: #248a3d; --warn: #b06100; --bad: #c4001a; width: calc(100% - 80px); max-width: 600px; margin-top: -14px; padding: 30px 22px 22px; color: var(--ink); background: #fbfaf5;
  font: 500 .82rem/1.55 var(--mono); box-shadow: 0 14px 30px -18px rgba(0,0,0,.45), 0 0 0 1px rgba(0,0,0,.05);
  -webkit-mask: linear-gradient(#000, #000) top / 100% calc(100% - 10px) no-repeat, radial-gradient(circle 6px at 8px 100%, transparent 5.5px, #000 6px) bottom / 16px 10px repeat-x;
  mask: linear-gradient(#000, #000) top / 100% calc(100% - 10px) no-repeat, radial-gradient(circle 6px at 8px 100%, transparent 5.5px, #000 6px) bottom / 16px 10px repeat-x; }
.calc-wrap .tape p { margin: 0; color: var(--ink); }
.calc-wrap .tape .head { display: flex; justify-content: space-between; gap: 10px; text-transform: uppercase; letter-spacing: .06em; font-size: .74rem; color: var(--faint); padding-bottom: 8px; border-bottom: 1px dashed #cfcdc4; margin-bottom: 10px; }
.tape h3 { margin: 14px 0 4px; font: 700 .78rem/1.3 var(--mono); letter-spacing: .08em; text-transform: uppercase; display: flex; align-items: center; gap: 8px; }
.tape h3::before { content: ""; width: 8px; height: 8px; border-radius: 50%; background: var(--dot); }
.tape [data-v="well"] { --dot: var(--good); } .tape [data-v="tight"] { --dot: var(--warn); } .tape [data-v="no"] { --dot: var(--bad); }
.tape ul { list-style: none; margin: 0; padding: 0; }
.calc-wrap .tape li { margin: 0; color: var(--ink); display: flex; align-items: baseline; gap: 6px; font-variant-numeric: tabular-nums; }
.tape li .n { flex: 0 1 auto; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.tape li .dots { flex: 1 1 12px; border-bottom: 1px dotted #b9b7ae; transform: translateY(-4px); min-width: 12px; }
.tape li .v { flex: none; white-space: nowrap; color: #55544f; }
.calc-wrap .tape [data-v="no"] li { color: #77766f; }
.tape summary { cursor: pointer; list-style: none; } .tape summary::-webkit-details-marker { display: none; }
.tape summary h3 { display: inline-flex; } .tape summary .more { margin-left: 8px; font-size: .74rem; color: #00708a; text-decoration: underline; }
.tape details[open] .more { display: none; }
.calc-wrap .tape .foot { margin-top: 14px; padding-top: 8px; border-top: 1px dashed #cfcdc4; font-size: .72rem; line-height: 1.5; color: var(--faint); }
.calc-bondi { margin: 22px 0 0; padding: 12px 16px; border-radius: 14px; background: var(--bondi-soft); font-size: .92rem; }
.calc-bondi a { color: var(--bondi-ink); font-weight: 600; }
@media (max-width: 640px) {
  .calc { padding: 16px 14px 18px; border-radius: 28px; gap: 14px; } .calc-body { grid-template-columns: minmax(0, 1fr); }
  .key { height: 44px; } .key.num { height: 50px; } .tape { width: calc(100% - 24px); padding: 26px 14px 20px; font-size: .76rem; }
}
@media (prefers-reduced-motion: reduce) { .key { transition: none; } }
</style>
<div class="calc-wrap">
<section class="calc" id="calc" aria-label="LLM RAM calculator">
<div class="calc-top"><p class="calc-brand"><img src="/assets/icon-blue.png" alt="" width="20" height="20">Bondi <span>LLM RAM calculator</span></p><div class="calc-solar" aria-hidden="true"><i></i><i></i><i></i><i></i></div></div>
<div class="calc-screen" aria-hidden="true"><i class="calc-led" id="calc-led"></i><div class="line"><span id="calc-l1">16 GB</span><span id="calc-l1r">Q4</span></div><div class="big" id="calc-big">22<small>run well</small></div><div class="line bottom" id="calc-l3"></div></div>
<p class="calc-vh" id="calc-say" aria-live="polite"></p>
<div class="calc-body">
<div class="calc-panel">
<h3 id="calc-mem-h">Your Mac's memory, in GB</h3>
<div class="keys mem" role="group" aria-labelledby="calc-mem-h"><button type="button" class="key" data-k="ram:8" aria-pressed="false">8</button><button type="button" class="key" data-k="ram:16" aria-pressed="true">16</button><button type="button" class="key" data-k="ram:18" aria-pressed="false">18</button><button type="button" class="key" data-k="ram:24" aria-pressed="false">24</button><button type="button" class="key" data-k="ram:32" aria-pressed="false">32</button><button type="button" class="key" data-k="ram:36" aria-pressed="false">36</button><button type="button" class="key" data-k="ram:48" aria-pressed="false">48</button><button type="button" class="key" data-k="ram:64" aria-pressed="false">64</button><button type="button" class="key" data-k="ram:96" aria-pressed="false">96</button><button type="button" class="key" data-k="ram:128" aria-pressed="false">128</button><button type="button" class="key" data-k="ram:192" aria-pressed="false">192</button><button type="button" class="key" data-k="ram:256" aria-pressed="false">256</button><button type="button" class="key" data-k="ram:512" aria-pressed="false">512</button></div>
<h3 id="calc-chip-h">Its chip, for speed</h3>
<div class="keys gen" role="group" aria-labelledby="calc-chip-h"><button type="button" class="key" data-k="gen:M1" aria-pressed="false">M1</button><button type="button" class="key" data-k="gen:M2" aria-pressed="false">M2</button><button type="button" class="key" data-k="gen:M3" aria-pressed="false">M3</button><button type="button" class="key" data-k="gen:M4" aria-pressed="false">M4</button><button type="button" class="key" data-k="gen:M5" aria-pressed="false">M5</button></div>
<div class="keys tier" role="group" aria-label="Chip kind"><button type="button" class="key" data-k="tier:base" aria-pressed="true" aria-label="Base chip">—</button><button type="button" class="key" data-k="tier:pro" aria-pressed="false">Pro</button><button type="button" class="key" data-k="tier:max" aria-pressed="false">Max</button><button type="button" class="key" data-k="tier:ultra" aria-pressed="false">Ultra</button></div>
<div class="keys cores" id="calc-cores" role="group" aria-label="GPU cores" hidden><button type="button" class="key" data-k="cores:0" aria-pressed="true"></button><button type="button" class="key" data-k="cores:1" aria-pressed="false"></button></div>
<p class="hint">Not sure? Open the Apple menu and choose About This Mac: it shows the chip and the memory.</p>
</div>
<div class="calc-panel">
<h3 id="calc-pad-h">Any model: its size, in billions</h3>
<div class="keys pad" role="group" aria-labelledby="calc-pad-h"><button type="button" class="key num" data-k="d:7">7</button><button type="button" class="key num" data-k="d:8">8</button><button type="button" class="key num" data-k="d:9">9</button><button type="button" class="key fn" data-k="quant:q4" aria-pressed="true" aria-label="4-bit quality">Q4</button><button type="button" class="key num" data-k="d:4">4</button><button type="button" class="key num" data-k="d:5">5</button><button type="button" class="key num" data-k="d:6">6</button><button type="button" class="key fn" data-k="quant:q8" aria-pressed="false" aria-label="8-bit quality">Q8</button><button type="button" class="key num" data-k="d:1">1</button><button type="button" class="key num" data-k="d:2">2</button><button type="button" class="key num" data-k="d:3">3</button><button type="button" class="key fn" data-k="quant:f16" aria-pressed="false" aria-label="16-bit, full size quality">16-bit</button><button type="button" class="key num" data-k="d:0">0</button><button type="button" class="key num" data-k="d:." aria-label="Decimal point">.</button><button type="button" class="key fn" data-k="back" aria-label="Delete last digit">⌫</button><button type="button" class="key clear" data-k="clear" aria-label="Clear">C</button><button type="button" class="key eq" data-k="eq" aria-label="Equals: check this model">=</button></div>
<p class="hint">The 70B in "Llama 3.3 70B" means 70 billion. Your keyboard works too.</p>
</div>
</div>
</section>
<div class="tape" id="calc-tape" aria-label="Printout: the models, sorted for your Mac"></div>
</div>
<!-- models:data --><script type="application/json" id="llm-data">{"checked":"2026-10-15","rules":{"reading":"a 14-inch MacBook Pro (2021) with M1 Max and 64 GB, on macOS 27.0.1 and Ollama 0.35.1, on October 15, 2026","gpuShare":0.81,"well":0.65,"contextShare":0.2,"contextMax":3000000000,"working":250000000,"efficiency":0.46,"bytesPerParam":{"q4":0.61,"q8":1.1,"f16":2},"sizes":[8,16,18,24,32,36,48,64,96,128,192,256,512]},"models":[["Gemma 3 1B",806058400,1,"gemma3:1b"],["DeepSeek-R1 1.5B",1117320512,1,"deepseek-r1:1.5b"],["Llama 3.2 1B",1321082688,1,"llama3.2:1b"],["Qwen 3.5 0.8B",1322057600,1,"qwen3.5:0.8b"],["Llama 3.2 3B",2019377376,1,"llama3.2:3b"],["Phi-4 mini 3.8B",2491874624,1,"phi4-mini:3.8b"],["Qwen3 4B",2497280480,1,"qwen3:4b"],["Qwen 3.5 2B",2683385216,1,"qwen3.5:2b"],["Qwen 3.5 4B",3324162336,1,"qwen3.5:4b"],["Gemma 3 4B",3350263392,1,"gemma3:4b"],["Mistral 7B",4372811712,1,"mistral:7b"],["Gemma 4 E2B",4587144832,1,"gemma4:e2b"],["DeepSeek-R1 7B",4683073184,1,"deepseek-r1:7b"],["Llama 3.1 8B",4920738944,1,"llama3.1:8b"],["DeepSeek-R1 8B",5225373760,1,"deepseek-r1:8b"],["Qwen3 8B",5225374496,1,"qwen3:8b"],["Qwen 3.5 9B",6550813952,1,"qwen3.5:9b"],["Gemma 4 E4B",6583644832,1,"gemma4:e4b"],["Gemma 4 12B",8021608448,1,"gemma4:12b"],["Gemma 3 12B",8164030720,1,"gemma3:12b"],["DeepSeek-R1 14B",8988109952,1,"deepseek-r1:14b"],["Phi-4 14B",9053114464,1,"phi4:14b"],["Qwen3 14B",9276184896,1,"qwen3:14b"],["gpt-oss 20B",13793422816,0.1722488038277512,"gpt-oss:20b"],["Devstral 24B",14333909728,1,"devstral:24b"],["Mistral Small 3.2 24B",15177369888,1,"mistral-small3.2:24b"],["Gemma 3 27B",17413400640,1,"gemma3:27b"],["Qwen 3.5 27B",17478544992,1,"qwen3.5:27b"],["Qwen 3.8 27B",17741860480,1,"qwen3.8:27b"],["Qwen 3.6 27B",17769065248,1,"qwen3.6:27b"],["Qwen3 30B A3B",18556685856,0.09836065573770492,"qwen3:30b-a3b"],["Qwen3-Coder 30B A3B",18556688736,0.10819672131147541,"qwen3-coder:30b"],["Gemma 4 26B A4B",18731013952,0.15873015873015872,"gemma4:26b"],["DeepSeek-R1 32B",19851335552,1,"deepseek-r1:32b"],["Qwen3 32B",20201240160,1,"qwen3:32b"],["Gemma 4 31B",20385446560,1,"gemma4:31b"],["Qwen 3.5 35B A3B",22069580576,0.08645533141210374,"qwen3.5:35b-a3b"],["Qwen 3.6 35B A3B",22621302688,0.08450704225352113,"qwen3.6:35b-a3b"],["DeepSeek-R1 70B",42520395616,1,"deepseek-r1:70b"],["Llama 3.3 70B",42520398528,1,"llama3.3:70b"],["gpt-oss 120B",65369800832,0.04366438356164384,"gpt-oss:120b"],["Llama 4 Scout",67436800960,0.1565377532228361,"llama4:latest"],["Qwen 3.5 122B A10B",81370024480,0.07993605115907275,"qwen3.5:122b-a10b"],["Qwen3 235B A22B",142154073664,0.09357720119098256,"qwen3:235b-a22b"],["Llama 3.1 405B",243069610720,1,"llama3.1:405b"],["Qwen3-Coder 480B A35B",290058824416,0.0728862973760933,"qwen3-coder:480b"],["DeepSeek-V3 671B",404430186016,0.05514157973174367,"deepseek-v3:671b"],["DeepSeek-R1 671B",404430186848,0.05514157973174367,"deepseek-r1:671b"]]}</script><!-- /models:data -->
<p class="calc-bondi">Running one? <a href="/beta/">Bondi</a> shows each loaded model in Ollama or LM Studio with the memory it really holds, and unloads it when you're done.</p>
<script>
(() => {
  const data = JSON.parse(document.getElementById('llm-data').textContent);
  const R = data.rules, GiB = 1073741824;
  const $ = id => document.getElementById(id);
  const calc = $('calc');
  const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);
  const need = b => b + Math.min(R.contextShare * b, R.contextMax) + R.working;
  const gb = b => (b / 1e9).toFixed(1) + ' GB';
  const verdict = (n, ram) => n <= R.well * ram ? 'well' : n <= R.gpuShare * ram ? 'tight' : 'no';
  const smallest = (n, share) => { const s = R.sizes.find(g => n <= share * g * GiB); return s ? s + ' GB' : 'more than 512 GB'; };
  // Writing speed: each new word reads the working part of the model from memory once
  const tps = (b, active, bw) => R.efficiency * bw * 1e9 / (b * active);
  const speed = s => s >= 100 ? '100+ tokens/s' : '~' + (s >= 20 ? Math.round(s / 5) * 5 : Math.max(1, Math.round(s))) + ' tokens/s';
  const short = s => s >= 100 ? '100+ t/s' : '~' + (s >= 20 ? Math.round(s / 5) * 5 : Math.max(1, Math.round(s))) + ' t/s';
  // Apple's published memory bandwidth, GB/s; Max comes in two GPU sizes from M3 on
  const CHIPS = { M1: { base: 68, pro: 200, max: [400], ultra: 800 }, M2: { base: 100, pro: 200, max: [400], ultra: 800 },
    M3: { base: 100, pro: 150, max: [300, 400], ultra: 819, cores: [30, 40] }, M4: { base: 120, pro: 273, max: [410, 546], cores: [32, 40] },
    M5: { base: 153, pro: 307, max: [460, 614], ultra: 1200, cores: [32, 40] } };
  const QUANT = { q4: 'Q4', q8: 'Q8', f16: '16-bit' };
  const st = { ram: 16, gen: null, tier: 'base', cores: 0, quant: 'q4', entry: '', shown: null };
  const chip = () => st.gen ? CHIPS[st.gen] : null;
  const bw = () => { const c = chip(); if (!c) return 0; const v = c[st.tier]; return Array.isArray(v) ? v[Math.min(st.cores, v.length - 1)] : v || 0; };
  const chipName = () => { const c = chip(); if (!c) return ''; const t = { base: '', pro: ' Pro', max: ' Max', ultra: ' Ultra' }[st.tier];
    return st.gen + t + (st.tier === 'max' && c.cores ? `, ${c.cores[st.cores]}-core GPU` : ''); };
  const titles = { well: 'Runs well', tight: 'Fits, tightly: close other apps', no: "Won't fit" };
  const checked = new Date(data.checked + 'T12:00:00Z').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

  const render = () => {
    const ram = st.ram * GiB, b = bw(), name = chipName();
    // Keys
    calc.querySelectorAll('[data-k]').forEach(k => {
      const [kind, val] = k.dataset.k.split(':');
      const on = { ram: +val === st.ram, gen: val === st.gen, tier: val === st.tier, cores: +val === st.cores, quant: val === st.quant }[kind];
      if (on !== undefined) k.setAttribute('aria-pressed', String(on));
    });
    const c = chip();
    calc.querySelector('[data-k="tier:ultra"]').disabled = !!c && !c.ultra;
    $('calc-cores').hidden = !(c && c.cores && st.tier === 'max');
    if (c && c.cores) c.cores.forEach((n, i) => { calc.querySelector(`[data-k="cores:${i}"]`).textContent = n + '-core GPU'; });
    // Screen
    const groups = { well: [], tight: [], no: [] };
    for (const [mName, bytes, active] of data.models) groups[verdict(need(bytes), ram)].push([mName, bytes, active]);
    groups.well.reverse(); // biggest first: the best a Mac can do is the interesting part
    $('calc-l1').textContent = `${st.ram} GB` + (name ? ` · ${name}` : ' · chip not set');
    $('calc-l1r').textContent = st.entry || st.shown !== null ? QUANT[st.quant] : '';
    let big, l3, led, say;
    if (st.shown !== null) {
      const bytes = st.shown * 1e9 * R.bytesPerParam[st.quant], n = need(bytes), v = verdict(n, ram);
      const said = { well: `Runs well on ${st.ram} GB`, tight: `Fits on ${st.ram} GB, tightly`, no: `Won't fit in ${st.ram} GB` }[v];
      big = `${(n / 1e9).toFixed(1)}<small>GB needed</small>`;
      l3 = `${esc(said)}` + (b && v !== 'no' ? ` · ${speed(tps(bytes, 1, b))}` : '') + `<br>Runs well on ${smallest(n, R.well)} · fits on ${smallest(n, R.gpuShare)}`;
      led = { well: 'var(--good)', tight: 'var(--warn)', no: 'var(--bad)' }[v];
      say = `A ${st.shown} billion model at ${QUANT[st.quant]} is about ${gb(bytes)} to download and needs ${gb(n)} of memory. ${said}. It runs well on ${smallest(n, R.well)} and fits, tightly, on ${smallest(n, R.gpuShare)}.`;
    } else if (st.entry) {
      big = `${esc(st.entry)}<small>B</small>`; l3 = 'Press = to check this model'; led = '#2a3a3e'; say = '';
    } else {
      big = `${groups.well.length}<small>run well</small>`;
      l3 = `${groups.tight.length} more fit, tightly · ${groups.no.length} won't fit`;
      led = groups.well.length ? 'var(--good)' : 'var(--bad)';
      say = `With ${st.ram} GB, ${groups.well.length} of ${data.models.length} popular models run well, ${groups.tight.length} more fit tightly, and ${groups.no.length} won't fit.`;
    }
    $('calc-big').innerHTML = big; $('calc-l3').innerHTML = l3; $('calc-led').style.setProperty('--led', led);
    if (say !== undefined && say !== $('calc-say').textContent) $('calc-say').textContent = say;
    // Tape
    const open = !!$('calc-tape').querySelector('details[open]');
    $('calc-tape').innerHTML = `<p class="head"><span>${st.ram} GB${name ? ' · ' + esc(name) : ''}</span><span>Sizes ${checked}</span></p>`
      + Object.entries(groups).filter(([, list]) => list.length).map(([v, list]) => {
        const rows = '<ul>' + list.map(([mName, bytes, active]) => `<li><span class="n">${esc(mName)}</span><span class="dots"></span><span class="v">${gb(bytes)}`
          + (b && v !== 'no' ? ' · ' + short(tps(bytes, active, b)) : '') + '</span></li>').join('') + '</ul>';
        const head = `<h3>${titles[v]} (${list.length})</h3>`;
        return `<div data-v="${v}">` + (v === 'no' ? `<details${open ? ' open' : ''}><summary>${head}<span class="more">Show</span></summary>${rows}</details>` : head + rows) + '</div>';
      }).join('')
      + `<p class="foot">${b ? 'Speeds (t/s, tokens a second) are estimates from the chip\'s memory bandwidth, checked against a real reading.' : 'Press a chip key to see how fast each model writes.'} Sizes are Ollama's default downloads.</p>`;
  };

  const press = key => {
    const [kind, val] = key.split(':');
    if (kind === 'ram') st.ram = +val;
    else if (kind === 'gen') { st.gen = st.gen === val ? null : val; if (st.gen && !CHIPS[st.gen][st.tier]) st.tier = 'base'; }
    else if (kind === 'tier') st.tier = val;
    else if (kind === 'cores') st.cores = +val;
    else if (kind === 'quant') st.quant = val;
    else if (kind === 'd') {
      if (st.shown !== null) { st.shown = null; st.entry = ''; }
      if (val === '.' && st.entry.includes('.')) return;
      if (st.entry.replace('.', '').length >= 5) return;
      st.entry = st.entry === '0' && val !== '.' ? val : (st.entry === '' && val === '.' ? '0.' : st.entry + val);
    }
    else if (kind === 'back') { if (st.shown !== null) st.shown = null; else st.entry = st.entry.slice(0, -1); }
    else if (kind === 'clear') { st.entry = ''; st.shown = null; }
    else if (kind === 'eq') { const v = parseFloat(st.entry); if (v > 0) { st.shown = v; st.entry = ''; } }
    render();
  };
  calc.addEventListener('click', e => { const k = e.target.closest('[data-k]'); if (k && !k.disabled) press(k.dataset.k); });
  // Typing works too, while the calculator has focus
  calc.addEventListener('keydown', e => {
    if (e.metaKey || e.ctrlKey || e.altKey) return;
    const map = { Enter: 'eq', '=': 'eq', Backspace: 'back', Escape: 'clear', Delete: 'clear', c: 'clear', C: 'clear', '.': 'd:.', ',': 'd:.' };
    const key = /^[0-9]$/.test(e.key) ? 'd:' + e.key : map[e.key];
    if (!key || (e.key === 'Enter' && e.target.closest('button'))) return; // Enter on a key presses that key, as buttons do
    e.preventDefault();
    const btn = calc.querySelector(`[data-k="${key}"]`);
    if (btn) { btn.classList.add('is-down'); setTimeout(() => btn.classList.remove('is-down'), 120); }
    press(key);
  });
  render();
})();
</script>
<!-- /web -->
Press your Mac's memory, and its chip if you know it. The calculator sorts 48 popular local AI models into three groups: the ones that run well, the ones that fit if you close other apps, and the ones that won't fit. Every size is the real download from Ollama's library, and the rules come from real readings on a Mac, described below.

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
