// The blog's thumbnails: one calm, neutral card per article (1200 × 630, also its link preview when shared),
// drawn as HTML and photographed with headless Chrome. Writes public_html/assets/blog/<slug>.jpg, and <slug>-dark.jpg,
// which the site shows in dark mode (the light one is the link preview).
//   node dev/blog-thumbs.mjs            every article in content/blog
//   node dev/blog-thumbs.mjs <slug>…    only these
// Then php dev/build-blog.php, which uses a thumbnail wherever one exists.
import { readFileSync, readdirSync, writeFileSync, mkdirSync, existsSync, rmSync } from 'node:fs';
import { execFileSync, spawn } from 'node:child_process';
import { tmpdir } from 'node:os';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const out = join(root, 'public_html/assets/blog');
mkdirSync(out, { recursive: true });
const icon = 'data:image/png;base64,' + readFileSync(join(root, 'public_html/assets/icon-blue.png')).toString('base64');
const TOPIC = { slow: 'Slow Mac', memory: 'Memory', heat: 'Heat and fans', processes: 'Processes', tools: 'Mac tools', bondi: 'Bondi' };
// A faint line drawing per topic, in Bondi Blue
const GLYPH = {
  slow: '<path d="M60 250a190 190 0 0 1 380 0" /><path d="M250 250l95-120" /><circle cx="250" cy="250" r="14" />',
  memory: '<rect x="90" y="120" width="320" height="200" rx="22" /><path d="M140 120V80M200 120V80M260 120V80M320 120V80M380 120V80M140 320v40M200 320v40M260 320v40M320 320v40M380 320v40" /><rect x="150" y="170" width="200" height="100" rx="12" />',
  heat: '<circle cx="250" cy="220" r="170" /><path d="M250 220c-20-80 10-130 70-140-10 60-30 100-70 140zM250 220c80-20 130 10 140 70-60-10-100-30-140-70zM250 220c20 80-10 130-70 140 10-60 30-100 70-140zM250 220c-80 20-130-10-140-70 60 10 100 30 140 70z" /><circle cx="250" cy="220" r="18" />',
  processes: '<rect x="80" y="90" width="340" height="56" rx="14" /><rect x="80" y="176" width="340" height="56" rx="14" /><rect x="80" y="262" width="340" height="56" rx="14" /><path d="M110 118h120M110 204h170M110 290h90" />',
  tools: '<rect x="80" y="110" width="96" height="96" rx="20" /><rect x="202" y="110" width="96" height="96" rx="20" /><rect x="324" y="110" width="96" height="96" rx="20" /><rect x="80" y="232" width="218" height="96" rx="20" /><rect x="324" y="232" width="96" height="96" rx="20" />',
  bondi: '<path d="M40 260c60-60 120-60 180 0s120 60 180 0 120-60 180 0" /><path d="M40 330c60-60 120-60 180 0s120 60 180 0 120-60 180 0" />',
};
const esc = s => s.replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);
// The short title: up to the first question mark or colon ("Why is my Mac so slow?")
const short = t => { const m = t.match(/^[^?:]*\?/) || t.match(/^[^:]*(?=:)/); return (m ? m[0] : t).trim(); };

const THEMES = {
  light: { bg: 'radial-gradient(900px 520px at 88% 0%, #e2f2f5 0%, rgba(226,242,245,0) 70%), linear-gradient(160deg, #f8f8f6 0%, #f1f3f4 100%)', ink: '#1d1d1f', label: '#00708a', soft: '#515154', glyph: '#0095b6', glyphOpacity: .13 },
  dark: { bg: 'radial-gradient(900px 520px at 88% 0%, #12343b 0%, rgba(18,52,59,0) 70%), linear-gradient(160deg, #1d1d1f 0%, #161617 100%)', ink: '#f5f5f7', label: '#5fd4ea', soft: '#b4b4b9', glyph: '#2cc0de', glyphOpacity: .16 },
};
const page = (title, topic, c) => `<!doctype html><html><head><meta charset="utf-8"><style>
html, body { margin: 0; }
.t { position: relative; width: 1200px; height: 630px; overflow: hidden; box-sizing: border-box; padding: 76px 84px;
  background: ${c.bg};
  font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Helvetica Neue", sans-serif; display: flex; flex-direction: column; }
.g { position: absolute; right: -40px; top: 120px; width: 500px; height: 440px; fill: none; stroke: ${c.glyph}; stroke-width: 10; stroke-linecap: round; stroke-linejoin: round; opacity: ${c.glyphOpacity}; }
.label { margin: 0; font-size: 28px; font-weight: 600; letter-spacing: .01em; color: ${c.label}; }
h1 { margin: 22px 0 0; max-width: 820px; font-size: 76px; line-height: 1.04; font-weight: 700; letter-spacing: -0.035em; color: ${c.ink}; }
.brand { margin-top: auto; display: flex; align-items: center; gap: 16px; font-size: 28px; font-weight: 600; color: ${c.soft}; }
.brand img { width: 52px; height: 52px; border-radius: 12px; }
</style></head><body><div class="t"><svg class="g" viewBox="0 0 500 440" aria-hidden="true">${GLYPH[topic] || GLYPH.bondi}</svg>
<p class="label">${esc(TOPIC[topic] || 'Bondi blog')}</p><h1>${esc(short(title))}</h1><p class="brand"><img src="${icon}" alt="">Bondi blog</p></div></body></html>`;

// Articles
const want = process.argv.slice(2);
const posts = readdirSync(join(root, 'content/blog')).filter(f => f.endsWith('.md')).map(f => {
  const head = readFileSync(join(root, 'content/blog', f), 'utf8').split('\n---\n')[0];
  const get = k => (head.match(new RegExp(`^${k}:\\s*(.*)$`, 'm')) || [])[1]?.replace(/^"(.*)"$/, '$1') || '';
  return { slug: f.slice(0, -3), title: get('title'), topic: get('topic') };
}).filter(p => !want.length || want.includes(p.slug));

// Chrome, driven over its debugging protocol
const chrome = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const profile = join(tmpdir(), 'bondi-thumbs-chrome');
const proc = spawn(chrome, ['--headless=new', '--remote-debugging-port=9411', `--user-data-dir=${profile}`, '--hide-scrollbars', 'about:blank'], { stdio: 'ignore' });
const sleep = ms => new Promise(r => setTimeout(r, ms));
let tabs; for (let i = 0; i < 50 && !tabs?.length; i++) { try { tabs = await (await fetch('http://127.0.0.1:9411/json')).json(); } catch {} await sleep(200); }
const ws = new WebSocket(tabs.find(t => t.type === 'page').webSocketDebuggerUrl);
let id = 0; const pending = new Map();
const send = (method, params = {}) => new Promise(res => { const i = ++id; pending.set(i, res); ws.send(JSON.stringify({ id: i, method, params })); });
ws.onmessage = e => { const m = JSON.parse(e.data); if (pending.has(m.id)) { pending.get(m.id)(m.result); pending.delete(m.id); } };
await new Promise(r => { ws.onopen = r; });
await send('Emulation.setDeviceMetricsOverride', { width: 1200, height: 630, deviceScaleFactor: 1, mobile: false });
await send('Page.enable');
for (const p of posts) {
  for (const [theme, c] of Object.entries(THEMES)) {
    const name = theme === 'light' ? p.slug : `${p.slug}-dark`;
    const html = join(tmpdir(), `bondi-thumb-${name}.html`);
    writeFileSync(html, page(p.title, p.topic, c));
    await send('Page.navigate', { url: 'file://' + html }); await sleep(500);
    const shot = await send('Page.captureScreenshot', { format: 'png' });
    const png = join(tmpdir(), `bondi-thumb-${name}.png`);
    writeFileSync(png, Buffer.from(shot.data, 'base64'));
    execFileSync('sips', ['-s', 'format', 'jpeg', '-s', 'formatOptions', '86', png, '--out', join(out, `${name}.jpg`)], { stdio: 'ignore' });
    rmSync(html); rmSync(png);
  }
  console.log(`assets/blog/${p.slug}.jpg (and -dark)  ${p.title}`);
}
ws.close(); proc.kill();
await new Promise(r => proc.once('exit', r)); // let Chrome finish before its profile goes
try { rmSync(profile, { recursive: true, force: true }); } catch {}
