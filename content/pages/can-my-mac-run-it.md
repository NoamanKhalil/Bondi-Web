---
title: Can my Mac run it? LLM RAM calculator for Mac
crumb: Can my Mac run it?
description: Pick your Mac's memory and chip to see which local AI models it can run, how much RAM each LLM needs, and how fast: Llama, Qwen, Gemma, DeepSeek and gpt-oss.
answer: A local AI model needs about its download size in memory, plus up to 20% for the conversation. An 8 GB Mac runs models up to about 7B parameters, 16 GB up to 14B, 32 GB up to about 30B, and 64 GB runs 70B models, tightly. How fast it writes depends on the chip's memory bandwidth.
checked: 2026-10-15
checkednote: Model sizes checked against Ollama's library
updated: 2026-10-15
layout: wide
---
<!-- web -->
<style>
/* The calculator: settings on the left (a quality toggle, then a box and a slider per setting), the answer on the right */
.calc-wrap { --good: #248a3d; --warn: #c76e00; --bad: #d70015; }
.calc { margin: 34px 0 18px;
  display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(0, 1fr); border: 1px solid var(--line); border-radius: 16px; background: var(--tile); }
@media (prefers-color-scheme: dark) { :root:not([data-theme="light"]) .calc-wrap { --good: #30d158; --warn: #ff9f0a; --bad: #ff453a; } }
:root[data-theme="dark"] .calc-wrap { --good: #30d158; --warn: #ff9f0a; --bad: #ff453a; }
.calc-in { display: grid; gap: 26px; padding: 30px 32px 32px; min-width: 0; }
.calc-row { display: grid; gap: 10px; min-width: 0; margin: 0; padding: 0; border: 0; }
.calc-row > label, .calc-row > legend { padding: 0; font: 600 1.05rem/1.3 var(--sans); letter-spacing: -0.01em; color: var(--ink); }
.calc-row > legend { margin-bottom: 10px; }
.calc-pair { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 28px; align-items: center; }
.calc .seg { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.calc .seg button { height: 48px; border-radius: 10px; border: 1px solid var(--line); background: var(--tile); color: var(--ink); font: 500 1rem/1 var(--sans); cursor: pointer; }
.calc .seg button[aria-pressed="true"] { border-color: var(--bondi); background: var(--bondi-soft); color: var(--bondi-ink); font-weight: 600; }
.calc .seg button:focus-visible { outline: 2px solid var(--bondi); outline-offset: 2px; }
.calc-box { display: flex; align-items: center; height: 52px; border: 1px solid var(--line); border-radius: 10px; background: var(--tile); min-width: 0; overflow: hidden; }
.calc-box:focus-within { border-color: var(--bondi); box-shadow: 0 0 0 3px var(--bondi-soft); }
.calc-box input, .calc-box select { flex: 1; min-width: 0; height: 100%; border: 0; background: transparent; padding: 0 14px 0 16px; font: 500 1.08rem/1 var(--sans); color: var(--ink); font-variant-numeric: tabular-nums; outline: none; appearance: none; -webkit-appearance: none; -moz-appearance: textfield; }
.calc-box input::-webkit-outer-spin-button, .calc-box input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.calc-box select { cursor: pointer; padding-right: 40px; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1.5l5 5 5-5' fill='none' stroke='%2386868b' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 16px center; }
.calc-box select option, .calc-box select optgroup { background: var(--tile); color: var(--ink); }
.calc-box .unit { flex: none; padding: 0 14px 0 4px; color: var(--faint); font-size: .95rem; }
.calc-box input { min-width: 5ch; }
.calc .hint { margin: 0; font-size: .85rem; line-height: 1.4; color: var(--faint); }
.calc input[type="range"] { width: 100%; height: 30px; margin: 0; background: transparent; appearance: none; -webkit-appearance: none; cursor: pointer; }
.calc input[type="range"]::-webkit-slider-runnable-track { height: 7px; border-radius: 4px; background: linear-gradient(90deg, var(--bondi) var(--fill, 10%), var(--line) var(--fill, 10%)); }
.calc input[type="range"]::-moz-range-track { height: 7px; border-radius: 4px; background: var(--line); }
.calc input[type="range"]::-moz-range-progress { height: 7px; border-radius: 4px; background: var(--bondi); }
.calc input[type="range"]::-webkit-slider-thumb { -webkit-appearance: none; width: 30px; height: 30px; margin-top: -11.5px; border-radius: 50%; background: var(--tile); border: 2.5px solid var(--ink); }
.calc input[type="range"]::-moz-range-thumb { width: 25px; height: 25px; border-radius: 50%; background: var(--tile); border: 2.5px solid var(--ink); }
.calc input[type="range"]:focus-visible { outline: 2px solid var(--bondi); outline-offset: 4px; border-radius: 8px; }
/* The answer */
.calc-out { display: grid; align-content: center; justify-items: center; gap: 6px; padding: 32px 28px; border-left: 1px solid var(--line); text-align: center; min-width: 0; }
.calc-out .big { margin: 0; font: 700 clamp(3rem, 6vw, 4rem)/1 var(--display); letter-spacing: -0.04em; color: var(--ink); font-variant-numeric: tabular-nums; }
.calc-out .big small { font-size: .3em; font-weight: 600; letter-spacing: -0.01em; margin-left: 6px; }
.calc-out .big.words { font-size: clamp(2rem, 4vw, 2.6rem); letter-spacing: -0.03em; }
.calc-out .big .approx { font-size: .55em; font-weight: 600; margin-right: 4px; color: var(--soft); }
.calc-out .cap { margin: 2px 0 0; font-weight: 600; color: var(--ink); }
.calc-out .pill { margin: 10px 0 18px; display: inline-flex; align-items: center; gap: 7px; padding: 5px 12px; border-radius: 980px; font-size: .85rem; font-weight: 600; background: var(--tile-2); color: var(--ink); }
.calc-out .pill::before { content: ""; width: 8px; height: 8px; border-radius: 50%; background: var(--dot, var(--line)); }
.calc-out .pill[data-v="well"] { --dot: var(--good); } .calc-out .pill[data-v="tight"] { --dot: var(--warn); } .calc-out .pill[data-v="no"] { --dot: var(--bad); }
.calc-out .lab { margin: 14px 0 2px; font-size: .74rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: var(--soft); }
.calc-out .mid { margin: 0; font: 700 2rem/1.1 var(--display); letter-spacing: -0.03em; color: var(--ink); font-variant-numeric: tabular-nums; }
.calc-out .mid small { font-size: .5em; font-weight: 600; color: var(--soft); letter-spacing: -0.01em; }
.calc-bar { position: relative; display: flex; width: 100%; height: 34px; margin-top: 6px; border-radius: 4px; overflow: hidden; background: var(--line); }
.calc-bar i { display: block; height: 100%; flex: none; }
.calc-bar .m { background: var(--bondi); } .calc-bar .c { background: color-mix(in srgb, var(--bondi) 55%, #fff); } .calc-bar .w { background: color-mix(in srgb, var(--bondi) 25%, #fff); }
.calc-bar .limit { position: absolute; top: 0; bottom: 0; width: 2px; background: var(--bad); }
.calc-legend { width: 100%; display: grid; grid-template-columns: minmax(0, 1fr); gap: 6px; margin-top: 8px; font-size: .82rem; text-align: left; color: var(--soft); font-variant-numeric: tabular-nums; }
.calc-legend span { display: flex; align-items: center; gap: 6px; min-width: 0; }
.calc-legend span::before { content: ""; flex: none; width: 10px; height: 10px; border-radius: 2px; background: var(--sw); }
.calc-legend b { color: var(--ink); font-weight: 600; margin-left: auto; }
.calc-out .after { margin: 12px 0 0; font-size: .88rem; color: var(--soft); }
.calc-vh { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
/* Every model, at the best quality the Mac runs it */
.calc-list { margin: 0 0 18px; padding: 22px 24px; border: 1px solid var(--line); border-radius: 16px; background: var(--tile); font-size: .92rem; }
.calc-list .head { margin: 0; display: flex; justify-content: space-between; gap: 10px; font-weight: 600; color: var(--ink); }
.calc-list .head span + span { font-weight: 500; color: var(--faint); font-size: .85rem; }
.calc-list h3 { margin: 16px 0 6px; font: 600 .95rem/1.3 var(--sans); color: var(--ink); display: flex; align-items: center; gap: 8px; }
.calc-list h3::before { content: ""; width: 9px; height: 9px; border-radius: 50%; background: var(--dot); }
.calc-list [data-v="well"] { --dot: var(--good); } .calc-list [data-v="tight"] { --dot: var(--warn); } .calc-list [data-v="no"] { --dot: var(--bad); }
.calc-list ul { list-style: none; margin: 0; padding: 0; }
.calc-wrap .calc-list li { margin: 0; padding: 4px 0; display: flex; align-items: baseline; gap: 8px; color: var(--ink); font-variant-numeric: tabular-nums; }
.calc-list li .n { flex: 0 1 auto; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.calc-list li .dots { flex: 1 1 12px; min-width: 12px; border-bottom: 1px dotted var(--line); transform: translateY(-4px); }
.calc-list li .v { flex: none; white-space: nowrap; color: var(--soft); }
.calc-list li .q { flex: none; padding: 1px 7px; border-radius: 6px; font-size: .78rem; font-weight: 600; background: var(--bondi-soft); color: var(--bondi-ink); }
.calc-wrap .calc-list [data-v="no"] li { color: var(--soft); }
.calc-list .group-head { display: flex; align-items: baseline; gap: 10px; } .calc-list .group-head h3 { margin-bottom: 0; }
.calc-list .more { margin: 8px 0 0; padding: 0; border: 0; background: none; cursor: pointer; font: 600 .88rem/1.3 var(--sans); color: var(--bondi-ink); }
.calc-list .group-head .more { margin: 0; } .calc-list .more:hover { text-decoration: underline; }
.calc-wrap .calc-list p.foot { margin: 14px 0 0; font-size: .8rem; color: var(--faint); }
.calc-bondi { margin: 0 0 30px; padding: 12px 16px; border-radius: 14px; background: var(--bondi-soft); font-size: .92rem; }
.calc-bondi a { color: var(--bondi-ink); font-weight: 600; }
@media (max-width: 900px) { .calc { grid-template-columns: minmax(0, 1fr); } .calc-out { border-left: 0; border-top: 1px solid var(--line); } }
@media (max-width: 560px) { .calc-in { padding: 22px 18px 24px; gap: 22px; } .calc-pair { grid-template-columns: minmax(0, 1fr); gap: 10px; } .calc .seg { gap: 8px; } .calc-out { padding: 26px 18px; } .calc-list { padding: 18px 16px; } }
</style>
<div class="calc-wrap">
<section class="calc" id="calc" aria-label="LLM RAM calculator">
<div class="calc-in">
<fieldset class="calc-row"><legend>Quality</legend>
<div class="seg" role="group" aria-label="Quality"><button type="button" data-q="4" aria-pressed="true">4-bit</button><button type="button" data-q="8" aria-pressed="false">8-bit</button><button type="button" data-q="16" aria-pressed="false">16-bit</button></div>
<p class="hint">4-bit is the usual download: about a third the size of the full 16-bit model, for a small loss in quality.</p></fieldset>
<div class="calc-row"><label for="calc-ram-n">Your Mac's memory</label>
<div class="calc-pair"><div class="calc-box"><input id="calc-ram-n" type="number" inputmode="numeric" min="4" max="512" step="1" value="16"><span class="unit">GB</span></div>
<input id="calc-ram" type="range" min="0" max="12" step="1" value="1" aria-label="Your Mac's memory" aria-valuetext="16 GB"></div></div>
<div class="calc-row"><label for="calc-chip">Chip</label>
<div class="calc-pair"><div class="calc-box"><select id="calc-chip"><option value="">Not sure</option><optgroup label="M1"><option value="68">M1</option><option value="200">M1 Pro</option><option value="400">M1 Max</option><option value="800">M1 Ultra</option></optgroup><optgroup label="M2"><option value="100">M2</option><option value="200">M2 Pro</option><option value="400">M2 Max</option><option value="800">M2 Ultra</option></optgroup><optgroup label="M3"><option value="100">M3</option><option value="150">M3 Pro</option><option value="300">M3 Max, 30-core GPU</option><option value="400">M3 Max, 40-core GPU</option><option value="819">M3 Ultra</option></optgroup><optgroup label="M4"><option value="120" selected>M4</option><option value="273">M4 Pro</option><option value="410">M4 Max, 32-core GPU</option><option value="546">M4 Max, 40-core GPU</option></optgroup><optgroup label="M5"><option value="153">M5</option><option value="307">M5 Pro</option><option value="460">M5 Max, 32-core GPU</option><option value="614">M5 Max, 40-core GPU</option><option value="1200">M5 Ultra</option></optgroup></select></div><p class="hint">Not sure? Apple menu, then About This Mac shows the chip and the memory.</p></div></div>
<div class="calc-row"><label for="calc-model">Model</label>
<div class="calc-box"><select id="calc-model"></select></div></div>
<div class="calc-row"><label for="calc-size-n">Model size</label>
<div class="calc-pair"><div class="calc-box"><input id="calc-size-n" type="number" inputmode="decimal" min="0.1" max="2000" step="any" value="14"><span class="unit">billion</span></div>
<input id="calc-size" type="range" min="0" max="1000" step="1" value="500" aria-label="Model size" aria-valuetext="14 billion parameters"></div></div>
</div>
<div class="calc-out" aria-hidden="true">
<p class="big" id="calc-big">–</p>
<p class="cap" id="calc-cap">estimated writing speed</p>
<p class="pill" id="calc-pill">–</p>
<p class="lab">Memory needed</p>
<p class="mid" id="calc-need">–</p>
<p class="lab">Memory breakdown</p>
<div class="calc-bar" id="calc-bar"></div>
<div class="calc-legend" id="calc-legend"></div>
<p class="after" id="calc-after"></p>
</div>
<p class="calc-vh" id="calc-say" aria-live="polite"></p>
</section>
<div class="calc-list" id="calc-tape" aria-label="Every model, at the best quality your Mac runs it"></div>
</div>
<!-- models:data --><script type="application/json" id="llm-data">{"checked":"2026-10-15","rules":{"reading":"a 14-inch MacBook Pro (2021) with M1 Max and 64 GB, on macOS 27.0.1 and Ollama 0.35.1, on October 15, 2026","gpuShare":0.81,"well":0.65,"contextShare":0.2,"contextMax":3000000000,"working":250000000,"efficiency":0.46,"bytesPerParam":{"q4":0.61,"q8":1.1,"f16":2},"sizes":[8,16,18,24,32,36,48,64,96,128,192,256,512]},"models":[["Gemma 3 1B",806058400,1,"gemma3:1b",1,4],["DeepSeek-R1 1.5B",1117320512,1,"deepseek-r1:1.5b",1.8,4],["Llama 3.2 1B",1321082688,1,"llama3.2:1b",1.2,8],["Qwen 3.5 0.8B",1322057600,1,"qwen3.5:0.8b",0.75,8],["Llama 3.2 3B",2019377376,1,"llama3.2:3b",3.2,4],["Phi-4 mini 3.8B",2491874624,1,"phi4-mini:3.8b",3.8,4],["Qwen3 4B",2497280480,1,"qwen3:4b",4,4],["Qwen 3.5 2B",2683385216,1,"qwen3.5:2b",1.9,8],["Qwen 3.5 4B",3324162336,1,"qwen3.5:4b",4.2,4],["Gemma 3 4B",3350263392,1,"gemma3:4b",3.9,4],["Mistral 7B",4372811712,1,"mistral:7b",7.2,4],["Gemma 4 E2B",4587144832,1,"gemma4:e2b",4.6,4],["DeepSeek-R1 7B",4683073184,1,"deepseek-r1:7b",7.6,4],["Llama 3.1 8B",4920738944,1,"llama3.1:8b",8,4],["DeepSeek-R1 8B",5225373760,1,"deepseek-r1:8b",8.2,4],["Qwen3 8B",5225374496,1,"qwen3:8b",8.2,4],["Qwen 3.5 9B",6550813952,1,"qwen3.5:9b",9,4],["Gemma 4 E4B",6583644832,1,"gemma4:e4b",7.5,4],["Gemma 4 12B",8021608448,1,"gemma4:12b",11.9,4],["Gemma 3 12B",8164030720,1,"gemma3:12b",11.8,4],["DeepSeek-R1 14B",8988109952,1,"deepseek-r1:14b",14.8,4],["Phi-4 14B",9053114464,1,"phi4:14b",14.7,4],["Qwen3 14B",9276184896,1,"qwen3:14b",14.8,4],["gpt-oss 20B",13793422816,0.1722488038277512,"gpt-oss:20b",20.9,4],["Devstral 24B",14333909728,1,"devstral:24b",23.6,4],["Mistral Small 3.2 24B",15177369888,1,"mistral-small3.2:24b",24,4],["Gemma 3 27B",17413400640,1,"gemma3:27b",27,4],["Qwen 3.5 27B",17478544992,1,"qwen3.5:27b",26.9,4],["Qwen 3.8 27B",17741860480,1,"qwen3.8:27b",27.3,4],["Qwen 3.6 27B",17769065248,1,"qwen3.6:27b",27.3,4],["Qwen3 30B A3B",18556685856,0.09836065573770492,"qwen3:30b-a3b",30.5,4],["Qwen3-Coder 30B A3B",18556688736,0.10819672131147541,"qwen3-coder:30b",30.5,4],["Gemma 4 26B A4B",18731013952,0.15873015873015872,"gemma4:26b",25.2,4],["DeepSeek-R1 32B",19851335552,1,"deepseek-r1:32b",32.8,4],["Qwen3 32B",20201240160,1,"qwen3:32b",32.8,4],["Gemma 4 31B",20385446560,1,"gemma4:31b",30.7,4],["Qwen 3.5 35B A3B",22069580576,0.08645533141210374,"qwen3.5:35b-a3b",34.7,4],["Qwen 3.6 35B A3B",22621302688,0.08450704225352113,"qwen3.6:35b-a3b",35.5,4],["DeepSeek-R1 70B",42520395616,1,"deepseek-r1:70b",70.6,4],["Llama 3.3 70B",42520398528,1,"llama3.3:70b",70.6,4],["gpt-oss 120B",65369800832,0.04366438356164384,"gpt-oss:120b",116.8,4],["Llama 4 Scout",67436800960,0.1565377532228361,"llama4:latest",108.6,4],["Qwen 3.5 122B A10B",81370024480,0.07993605115907275,"qwen3.5:122b-a10b",125.1,4],["Qwen3 235B A22B",142154073664,0.09357720119098256,"qwen3:235b-a22b",235.1,4],["Llama 3.1 405B",243069610720,1,"llama3.1:405b",405.9,4],["Qwen3-Coder 480B A35B",290058824416,0.0728862973760933,"qwen3-coder:480b",480.2,4],["DeepSeek-V3 671B",404430186016,0.05514157973174367,"deepseek-v3:671b",671,4],["DeepSeek-R1 671B",404430186848,0.05514157973174367,"deepseek-r1:671b",671,4]]}</script><!-- /models:data -->
<p class="calc-bondi">Running one? <a href="/beta/">Bondi</a> shows each loaded model in Ollama or LM Studio with the memory it really holds, and unloads it when you're done.</p>
<script>
(() => {
  const data = JSON.parse(document.getElementById('llm-data').textContent);
  const R = data.rules, GiB = 1073741824, SIZES = R.sizes;
  const $ = id => document.getElementById(id);
  const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);
  const BPP = { 4: R.bytesPerParam.q4, 8: R.bytesPerParam.q8, 16: R.bytesPerParam.f16 };
  const ctx = b => Math.min(R.contextShare * b, R.contextMax);
  const need = b => b + ctx(b) + R.working;
  const gb = b => (b / 1e9).toFixed(1) + ' GB';
  const verdict = (n, ram) => n <= R.well * ram ? 'well' : n <= R.gpuShare * ram ? 'tight' : 'no';
  const smallest = (n, share) => { const s = SIZES.find(g => n <= share * g * GiB); return s ? s + ' GB' : 'more than 512 GB'; };
  // A listed model at a quality: Ollama's own download when that's its quality, otherwise worked out from its parameters
  const sizeAt = (m, q) => m[5] === q ? m[1] : m[4] * 1e9 * BPP[q];
  // Writing speed: each new word reads the working part of the model from memory once
  const tps = (b, active, bw) => R.efficiency * bw * 1e9 / (b * active);
  const round = s => s >= 100 ? '100+' : String(s >= 20 ? Math.round(s / 5) * 5 : Math.max(1, Math.round(s)));
  const titles = { well: 'Runs well', tight: 'Fits, tightly: close other apps', no: "Won't fit, even at 4-bit" };
  const pill = { well: 'Runs well', tight: 'Fits, tightly: close other apps', no: "Won't fit in memory" };
  // Model size slider: 0.5 to 1,000 billion, spread evenly by order of magnitude
  const toSize = v => 0.5 * Math.pow(2000, v / 1000), toSlider = b => Math.round(1000 * Math.log(Math.max(0.5, Math.min(1000, b)) / 0.5) / Math.log(2000));
  const nice = b => b < 100 ? +b.toFixed(1) : Math.round(b);
  const checked = new Date(data.checked + 'T12:00:00Z').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  const st = { q: 4, ram: 16, model: null, custom: null, open: { well: false, tight: false, no: false } }; // model: a picked model's name; custom: a typed size; open: list groups shown in full
  const FEW = 6; // models shown per group before "Show all"

  const fill = el => el.style.setProperty('--fill', ((el.value - el.min) / (el.max - el.min) * 100) + '%');
  const render = () => {
    const ram = st.ram * GiB, sel = $('calc-chip'), bw = +sel.value, chipName = bw ? sel.options[sel.selectedIndex].text : '';
    // The model list for the dropdown, grouped by what fits at this quality; until someone picks, the biggest that runs well
    const groups = { well: [], tight: [], no: [] };
    for (const m of data.models) groups[verdict(need(sizeAt(m, st.q)), ram)].push(m);
    groups.well.reverse(); groups.tight.reverse();
    const auto = (groups.well[0] || groups.tight[0] || groups.no[0])[0];
    const current = st.custom !== null ? '__custom' : (st.model || auto);
    $('calc-model').innerHTML = Object.entries(groups).filter(([, l]) => l.length).map(([v, l]) =>
      `<optgroup label="${{ well: 'Runs well', tight: 'Fits, tightly', no: "Won't fit" }[v]} at ${st.q}-bit (${l.length})">`
      + l.map(m => `<option${m[0] === current ? ' selected' : ''}>${esc(m[0])}</option>`).join('') + '</optgroup>').join('')
      + `<optgroup label="Another model"><option value="__custom"${current === '__custom' ? ' selected' : ''}>Another size (type it below)</option></optgroup>`;
    const m = current === '__custom' ? null : data.models.find(x => x[0] === current);
    const params = m ? m[4] : st.custom, active = m ? m[2] : 1, name = m ? m[0] : `A ${nice(params)}B model`;
    if (document.activeElement !== $('calc-size-n')) $('calc-size-n').value = nice(params);
    $('calc-size').value = toSlider(params); fill($('calc-size'));
    $('calc-size').setAttribute('aria-valuetext', nice(params) + ' billion parameters');
    if (document.activeElement !== $('calc-ram-n')) $('calc-ram-n').value = st.ram;
    let idx = SIZES.findIndex(g => g >= st.ram); if (idx < 0) idx = SIZES.length - 1;
    $('calc-ram').value = idx; fill($('calc-ram')); $('calc-ram').setAttribute('aria-valuetext', st.ram + ' GB');
    document.querySelectorAll('.calc .seg button').forEach(b => b.setAttribute('aria-pressed', String(+b.dataset.q === st.q)));
    // The answer
    const bytes = m ? sizeAt(m, st.q) : params * 1e9 * BPP[st.q];
    const n = need(bytes), v = verdict(n, ram);
    if (!(params > 0)) { $('calc-big').textContent = '–'; return; }
    $('calc-big').classList.toggle('words', v === 'no');
    if (v === 'no') { $('calc-big').innerHTML = 'Won\'t fit'; $('calc-cap').textContent = `in ${st.ram} GB at ${st.q}-bit`; }
    else if (!bw) { $('calc-big').innerHTML = '–'; $('calc-cap').textContent = 'pick the chip to see the speed'; }
    else { $('calc-big').innerHTML = `<span class="approx">≈</span>${round(tps(bytes, active, bw))}<small>tokens/s</small>`; $('calc-cap').textContent = 'estimated writing speed'; }
    $('calc-pill').dataset.v = v; $('calc-pill').textContent = pill[v];
    $('calc-need').innerHTML = `${(n / 1e9).toFixed(1)} GB <small>of ${st.ram} GB</small>`;
    // Memory breakdown: the model, the conversation, working space, and what's left for macOS and apps
    const scale = Math.max(ram, n), pc = x => (x / scale * 100).toFixed(2) + '%';
    const parts = [['m', bytes], ['c', ctx(bytes)], ['w', R.working]];
    $('calc-bar').innerHTML = parts.map(([k, x]) => `<i class="${k}" style="width:${pc(x)}"></i>`).join('') + (n > ram ? `<span class="limit" style="left:${pc(ram)}"></span>` : '');
    const free = ram - n;
    $('calc-legend').innerHTML = `<span style="--sw:var(--bondi)">Model <b>${gb(bytes)}</b></span><span style="--sw:color-mix(in srgb, var(--bondi) 55%, #fff)">Conversation <b>${gb(ctx(bytes))}</b></span>`
      + `<span style="--sw:color-mix(in srgb, var(--bondi) 25%, #fff)">Working space <b>${gb(R.working)}</b></span>`
      + (free >= 0 ? `<span style="--sw:var(--line)">Left for macOS and apps <b>${gb(free)}</b></span>` : `<span style="--sw:var(--bad)">Short by <b>${gb(-free)}</b></span>`);
    const wellOn = smallest(n, R.well);
    $('calc-after').textContent = wellOn === 'more than 512 GB' ? `${name} at ${st.q}-bit needs more memory than any Mac has (512 GB at most).`
      : `${name} at ${st.q}-bit runs well on ${wellOn} or more` + (chipName ? `, here on ${chipName}.` : '.');
    const say = `${name} at ${st.q}-bit needs ${gb(n)} of ${st.ram} GB: it ${{ well: 'runs well', tight: 'fits, tightly', no: "won't fit" }[v]}.`
      + (v !== 'no' && bw ? ` Estimated speed about ${round(tps(bytes, active, bw))} tokens a second.` : '');
    if (say !== $('calc-say').textContent) $('calc-say').textContent = say;
    // Every model, at the best quality this Mac runs it
    const best = { well: [], tight: [], no: [] };
    for (const x of data.models) {
      const q = [16, 8, 4].find(q => verdict(need(sizeAt(x, q)), ram) === 'well');
      const qt = q ? null : [16, 8, 4].find(q => verdict(need(sizeAt(x, q)), ram) === 'tight');
      if (q) best.well.push([x, q]); else if (qt) best.tight.push([x, qt]); else best.no.push([x, 4]);
    }
    best.well.reverse(); best.tight.reverse();
    $('calc-tape').innerHTML = `<p class="head"><span>Every model, at the best quality your ${st.ram} GB Mac runs it</span><span>Sizes ${checked}</span></p>`
      + Object.entries(best).filter(([, l]) => l.length).map(([gv, l]) => {
        const shown = gv === 'no' ? (st.open.no ? l : []) : (st.open[gv] ? l : l.slice(0, FEW));
        const rows = '<ul>' + shown.map(([x, q]) => { const b = sizeAt(x, q);
          return `<li><span class="n">${esc(x[0])}</span><span class="dots"></span>` + (gv !== 'no' ? `<span class="q">${q}-bit</span>` : '')
            + `<span class="v">${gb(b)}` + (bw && gv !== 'no' ? ' · ' + (round(tps(b, x[2], bw)) === '100+' ? '100+' : '~' + round(tps(b, x[2], bw))) + ' t/s' : '') + '</span></li>'; }).join('') + '</ul>';
        const head = `<h3>${titles[gv]} (${l.length})</h3>`;
        const toggle = gv === 'no' ? `<button type="button" class="more" data-open="no">${st.open.no ? 'Hide' : 'Show'}</button>`
          : l.length > FEW ? `<button type="button" class="more" data-open="${gv}">${st.open[gv] ? 'Show fewer' : `Show all ${l.length}`}</button>` : '';
        return `<div data-v="${gv}">` + (gv === 'no' ? `<div class="group-head">${head}${toggle}</div>${st.open.no ? rows : ''}` : head + rows + toggle) + '</div>';
      }).join('')
      + `<p class="foot">4-bit sizes are Ollama's downloads; 8-bit and 16-bit are worked out from each model's parameters. ${bw ? 'Speeds (t/s, tokens a second) are estimates from the chip\'s memory bandwidth, checked against a real reading.' : 'Pick the chip to see how fast each model writes.'}</p>`;
  };
  $('calc-tape').addEventListener('click', e => { const b = e.target.closest('[data-open]'); if (b) { st.open[b.dataset.open] = !st.open[b.dataset.open]; render(); } });
  document.querySelector('.calc .seg').addEventListener('click', e => { const b = e.target.closest('[data-q]'); if (b) { st.q = +b.dataset.q; render(); } });
  $('calc-ram').addEventListener('input', e => { st.ram = SIZES[+e.target.value]; render(); });
  $('calc-ram-n').addEventListener('input', e => { const v = Math.round(+e.target.value); if (v >= 4 && v <= 512) { st.ram = v; render(); } });
  $('calc-ram-n').addEventListener('change', () => { $('calc-ram-n').value = st.ram; });
  $('calc-chip').addEventListener('change', render);
  $('calc-model').addEventListener('change', e => { if (e.target.value === '__custom') st.custom = +$('calc-size-n').value || 14; else { st.model = e.target.value; st.custom = null; } render(); });
  $('calc-size').addEventListener('input', e => { st.custom = nice(toSize(+e.target.value)); render(); });
  $('calc-size-n').addEventListener('input', e => { const v = +e.target.value; if (v > 0) { st.custom = v; render(); } });
  render();
})();
</script>
<!-- /web -->
Set your Mac's memory, its chip, a model and a quality, and the calculator shows how much memory the model needs and how fast it would write on your Mac. It also sorts 48 popular local AI models into three groups: the ones that run well, the ones that fit if you close other apps, and the ones that won't fit. Every size is the real download from Ollama's library, and the rules come from real readings on a Mac, described below.

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
| Model | Download | Memory it needs | Runs well on | Fits, tightly, on | At 8-bit, runs well on | At 16-bit, runs well on |
| --- | --- | --- | --- | --- | --- | --- |
| Gemma 3 1B | 0.8 GB, 4-bit | 1.2 GB | 8 GB | 8 GB | 8 GB | 8 GB |
| DeepSeek-R1 1.5B | 1.1 GB, 4-bit | 1.6 GB | 8 GB | 8 GB | 8 GB | 8 GB |
| Llama 3.2 1B | 1.3 GB, 8-bit | 1.8 GB | 8 GB | 8 GB | 8 GB | 8 GB |
| Qwen 3.5 0.8B | 1.3 GB, 8-bit | 1.8 GB | 8 GB | 8 GB | 8 GB | 8 GB |
| Llama 3.2 3B | 2.0 GB, 4-bit | 2.7 GB | 8 GB | 8 GB | 8 GB | 16 GB |
| Phi-4 mini 3.8B | 2.5 GB, 4-bit | 3.2 GB | 8 GB | 8 GB | 8 GB | 16 GB |
| Qwen3 4B | 2.5 GB, 4-bit | 3.2 GB | 8 GB | 8 GB | 8 GB | 16 GB |
| Qwen 3.5 2B | 2.7 GB, 8-bit | 3.5 GB | 8 GB | 8 GB | 8 GB | 8 GB |
| Qwen 3.5 4B | 3.3 GB, 4-bit | 4.2 GB | 8 GB | 8 GB | 16 GB | 16 GB |
| Gemma 3 4B | 3.4 GB, 4-bit | 4.3 GB | 8 GB | 8 GB | 8 GB | 16 GB |
| Mistral 7B | 4.4 GB, 4-bit | 5.5 GB | 8 GB | 8 GB | 16 GB | 32 GB |
| Gemma 4 E2B | 4.6 GB, 4-bit | 5.8 GB | 16 GB | 8 GB | 16 GB | 18 GB |
| DeepSeek-R1 7B | 4.7 GB, 4-bit | 5.9 GB | 16 GB | 8 GB | 16 GB | 32 GB |
| Llama 3.1 8B | 4.9 GB, 4-bit | 6.2 GB | 16 GB | 8 GB | 16 GB | 32 GB |
| DeepSeek-R1 8B | 5.2 GB, 4-bit | 6.5 GB | 16 GB | 8 GB | 16 GB | 32 GB |
| Qwen3 8B | 5.2 GB, 4-bit | 6.5 GB | 16 GB | 8 GB | 16 GB | 32 GB |
| Qwen 3.5 9B | 6.6 GB, 4-bit | 8.1 GB | 16 GB | 16 GB | 18 GB | 32 GB |
| Gemma 4 E4B | 6.6 GB, 4-bit | 8.2 GB | 16 GB | 16 GB | 16 GB | 32 GB |
| Gemma 4 12B | 8.0 GB, 4-bit | 9.9 GB | 16 GB | 16 GB | 24 GB | 48 GB |
| Gemma 3 12B | 8.2 GB, 4-bit | 10.0 GB | 16 GB | 16 GB | 24 GB | 48 GB |
| DeepSeek-R1 14B | 9.0 GB, 4-bit | 11.0 GB | 16 GB | 16 GB | 32 GB | 48 GB |
| Phi-4 14B | 9.1 GB, 4-bit | 11.1 GB | 16 GB | 16 GB | 32 GB | 48 GB |
| Qwen3 14B | 9.3 GB, 4-bit | 11.4 GB | 18 GB | 16 GB | 32 GB | 48 GB |
| gpt-oss 20B (mixture of experts, 3.6B active) | 13.8 GB, 4-bit | 16.8 GB | 32 GB | 24 GB | 48 GB | 96 GB |
| Devstral 24B | 14.3 GB, 4-bit | 17.5 GB | 32 GB | 24 GB | 48 GB | 96 GB |
| Mistral Small 3.2 24B | 15.2 GB, 4-bit | 18.4 GB | 32 GB | 24 GB | 48 GB | 96 GB |
| Gemma 3 27B | 17.4 GB, 4-bit | 20.7 GB | 32 GB | 24 GB | 48 GB | 96 GB |
| Qwen 3.5 27B | 17.5 GB, 4-bit | 20.7 GB | 32 GB | 24 GB | 48 GB | 96 GB |
| Qwen 3.8 27B | 17.7 GB, 4-bit | 21.0 GB | 32 GB | 32 GB | 48 GB | 96 GB |
| Qwen 3.6 27B | 17.8 GB, 4-bit | 21.0 GB | 32 GB | 32 GB | 48 GB | 96 GB |
| Qwen3 30B A3B (mixture of experts, 3B active) | 18.6 GB, 4-bit | 21.8 GB | 32 GB | 32 GB | 64 GB | 96 GB |
| Qwen3-Coder 30B A3B (mixture of experts, 3.3B active) | 18.6 GB, 4-bit | 21.8 GB | 32 GB | 32 GB | 64 GB | 96 GB |
| Gemma 4 26B A4B (mixture of experts, 4B active) | 18.7 GB, 4-bit | 22.0 GB | 32 GB | 32 GB | 48 GB | 96 GB |
| DeepSeek-R1 32B | 19.9 GB, 4-bit | 23.1 GB | 36 GB | 32 GB | 64 GB | 128 GB |
| Qwen3 32B | 20.2 GB, 4-bit | 23.5 GB | 36 GB | 32 GB | 64 GB | 128 GB |
| Gemma 4 31B | 20.4 GB, 4-bit | 23.6 GB | 36 GB | 32 GB | 64 GB | 96 GB |
| Qwen 3.5 35B A3B (mixture of experts, 3B active) | 22.1 GB, 4-bit | 25.3 GB | 48 GB | 32 GB | 64 GB | 128 GB |
| Qwen 3.6 35B A3B (mixture of experts, 3B active) | 22.6 GB, 4-bit | 25.9 GB | 48 GB | 32 GB | 64 GB | 128 GB |
| DeepSeek-R1 70B | 42.5 GB, 4-bit | 45.8 GB | 96 GB | 64 GB | 128 GB | 256 GB |
| Llama 3.3 70B | 42.5 GB, 4-bit | 45.8 GB | 96 GB | 64 GB | 128 GB | 256 GB |
| gpt-oss 120B (mixture of experts, 5.1B active) | 65.4 GB, 4-bit | 68.6 GB | 128 GB | 96 GB | 192 GB | 512 GB |
| Llama 4 Scout (mixture of experts, 17B active) | 67.4 GB, 4-bit | 70.7 GB | 128 GB | 96 GB | 192 GB | 512 GB |
| Qwen 3.5 122B A10B (mixture of experts, 10B active) | 81.4 GB, 4-bit | 84.6 GB | 128 GB | 128 GB | 256 GB | 512 GB |
| Qwen3 235B A22B (mixture of experts, 22B active) | 142.2 GB, 4-bit | 145.4 GB | 256 GB | 192 GB | 512 GB | more than 512 GB |
| Llama 3.1 405B | 243.1 GB, 4-bit | 246.3 GB | 512 GB | 512 GB | more than 512 GB | more than 512 GB |
| Qwen3-Coder 480B A35B (mixture of experts, 35B active) | 290.1 GB, 4-bit | 293.3 GB | 512 GB | 512 GB | more than 512 GB | more than 512 GB |
| DeepSeek-V3 671B (mixture of experts, 37B active) | 404.4 GB, 4-bit | 407.7 GB | more than 512 GB | 512 GB | more than 512 GB | more than 512 GB |
| DeepSeek-R1 671B (mixture of experts, 37B active) | 404.4 GB, 4-bit | 407.7 GB | more than 512 GB | 512 GB | more than 512 GB | more than 512 GB |
<!-- /models:table -->

## Questions

### How much RAM do I need to run a local LLM?

About the model's download size, plus up to 20% for the conversation. As a rule of thumb, an 8 GB Mac runs models up to about 7B parameters, 16 GB up to 14B, 32 GB up to about 30B, and 64 GB runs 70B models, tightly. The calculator above checks 48 popular models against your Mac's memory.

### What LLM can I run on my Mac?

It depends on your Mac's memory. With 8 GB: Llama 3.2 3B, Gemma 3 4B, Qwen 3.5 4B or Mistral 7B. With 16 GB: Qwen 3.5 9B, Gemma 4 12B, Phi-4 14B or DeepSeek-R1 14B. With 32 GB: gpt-oss 20B, Gemma 3 27B, Qwen 3.6 27B or Qwen3 30B A3B. With 64 GB: Llama 3.3 70B or DeepSeek-R1 70B, tightly. With 128 GB: gpt-oss 120B or Qwen 3.5 122B A10B.

### 4-bit vs 8-bit vs 16-bit: which should I use?

The number is how many bits each of the model's parameters is stored in. 16-bit is the full-size model: 2 bytes per parameter, so a 70B model needs about 140 GB. 8-bit is about half that and very close to full quality. 4-bit (Q4), the usual download in Ollama and LM Studio, is about a third of the full size, for a small loss in quality. Use the best one that runs well on your Mac: the list under the calculator shows it for each model.

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
