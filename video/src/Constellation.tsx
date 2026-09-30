import { AbsoluteFill, Easing, Img, interpolate, random, spring, staticFile, useCurrentFrame, useVideoConfig } from "remotion";
import { groups, otherProcesses, processNames, totalApps, totalProcesses } from "./constellation-data";

// Website film: every process on a real Mac as a star, pulled into the app it belongs to. The stars for
// each app are exactly its process count from Bondi's engine, so the picture is the real grouping.

export const CONSTELLATION_FPS = 30;
export const CONSTELLATION_FRAMES = 15 * CONSTELLATION_FPS;
/** The website's intro over the app window: no end card, it holds on the icons and the page fades it away. */
export const INTRO_FRAMES = 10 * CONSTELLATION_FPS;

const sans = '-apple-system, BlinkMacSystemFont, "SF Pro Display", "Helvetica Neue", sans-serif';
const mono = 'ui-monospace, "SF Mono", Menlo, monospace';
const bondi = "#2cc0de";
const clamp = { extrapolateLeft: "clamp", extrapolateRight: "clamp" } as const;
const ease = Easing.bezier(0.22, 0.8, 0.2, 1);
const inOut = Easing.bezier(0.6, 0, 0.25, 1);

// MARK: Layout (worked out once): icons sized by process count, packed along a spiral from the middle.

type Node = { name: string; icon: string | null; processes: number; size: number; x: number; y: number; rank: number };

const sizeFor = (processes: number) => 62 + 24 * Math.log2(Math.max(1, processes));

const layout = (W: number, H: number): Node[] => {
  const list = groups
    .map((g) => ({ name: g.name, icon: g.icon as string | null, processes: g.processes, size: sizeFor(g.processes) }))
    .sort((a, b) => b.size - a.size);
  const placed: Node[] = [];
  const cx = W / 2;
  const cy = 300 + (H - 320) / 2;
  const stretch = Math.max(1, (W - 140) / (H - 320)); // spiral as wide as the space
  list.forEach((g, rank) => {
    const r = g.size / 2 + (g.size > 150 ? 62 : 30); // room for the count (and name, on big icons) underneath
    for (let step = 0; step < 20000; step++) {
      const angle = step * 0.21 + rank * 0.9;
      const radius = step * 0.55;
      const x = cx + Math.cos(angle) * radius * stretch;
      const y = cy + Math.sin(angle) * radius;
      const inside = x - r > 70 && x + r < W - 70 && y - r > 300 && y + r < H - 20;
      const clear = placed.every((p) => Math.hypot(p.x - x, p.y - y) > r + p.size / 2 + (p.size > 150 ? 62 : 30));
      if (inside && clear) {
        placed.push({ ...g, x, y, rank });
        return;
      }
    }
  });
  return placed;
};

// MARK: Stars: one per process, each belonging to one app.

type Star = { node: Node | null; sx: number; sy: number; tx: number; ty: number; delay: number; curve: number; size: number; phase: number };

const makeStars = (nodes: Node[], W: number, H: number): Star[] => nodes.flatMap((node, n): Star[] =>
  Array.from({ length: node.processes }, (_, i) => {
    const seed = `${n}-${i}`;
    const a = random(`a${seed}`) * Math.PI * 2;
    const d = Math.sqrt(random(`d${seed}`)) * node.size * 0.36;
    return {
      node,
      sx: random(`x${seed}`) * W,
      sy: random(`y${seed}`) * H,
      tx: node.x + Math.cos(a) * d,
      ty: node.y + Math.sin(a) * d,
      delay: random(`t${seed}`) * 42,
      curve: (random(`c${seed}`) - 0.5) * 420,
      size: 1.6 + random(`s${seed}`) * 2.6,
      phase: random(`p${seed}`) * Math.PI * 2,
    };
  }),
).concat(
  // The 11 background helpers without an icon (14 processes) are stars too; they fade out as the rest gather.
  Array.from({ length: otherProcesses }, (_, i): Star => ({
    node: null, sx: random(`ox${i}`) * W, sy: random(`oy${i}`) * H, tx: random(`ox${i}`) * W, ty: random(`oy${i}`) * H,
    delay: 0, curve: 0, size: 1.6 + random(`os${i}`) * 2.6, phase: random(`op${i}`) * Math.PI * 2,
  })),
);

type Scene = { W: number; H: number; nodes: Node[]; stars: Star[]; named: { name: string; star: Star; start: number }[] };
const scenes = new Map<string, Scene>();
const sceneFor = (W: number, H: number): Scene => {
  const key = `${W}x${H}`;
  if (!scenes.has(key)) {
    const nodes = layout(W, H);
    const stars = makeStars(nodes, W, H);
    // A few stars wear their real process name while they drift.
    const named = processNames.map((name, i) => ({ name, star: stars[Math.floor(random(`n${i}`) * stars.length)], start: 4 + (i % 12) * 5 }));
    scenes.set(key, { W, H, nodes, stars, named });
  }
  return scenes.get(key)!;
};

// MARK: Timeline (frames at 30 fps)

const T = {
  gatherStart: 78, // stars begin to move to their apps
  gatherLength: 92,
  iconsStart: 158, // icons arrive, biggest first
  swapHeadline: 170,
  countsStart: 214,
  outroStart: 340,
};

const Headline = ({ frame, outro }: { frame: number; outro: number }) => {
  const count = Math.round(interpolate(frame, [8, 64], [0, totalProcesses], { ...clamp, easing: ease }));
  const first = interpolate(frame, [4, 22, T.swapHeadline - 14, T.swapHeadline], [0, 1, 1, 0], clamp);
  const second = interpolate(frame, [T.swapHeadline, T.swapHeadline + 20, outro, outro + 14], [0, 1, 1, 0], clamp);
  const line = (opacity: number, big: string, small: string, color: string) => (
    <div style={{ position: "absolute", left: 0, right: 0, top: 96, textAlign: "center", opacity,
                  transform: `translateY(${(1 - opacity) * 16}px)` }}>
      <div style={{ fontFamily: sans, fontWeight: 700, fontSize: 92, letterSpacing: "-0.035em", color: "#f5f5f7",
                    fontVariantNumeric: "tabular-nums" }}>
        <span style={{ color }}>{big}</span>
      </div>
      <div style={{ fontFamily: sans, fontWeight: 500, fontSize: 32, color: "#a1a1a6", marginTop: 10, letterSpacing: "-0.01em" }}>{small}</div>
    </div>
  );
  return (
    <>
      {line(first, `${count.toLocaleString("en-US")} processes.`, "What Activity Monitor lists on this Mac.", "#f5f5f7")}
      {line(second, `${totalApps} apps you know.`, "Bondi puts every process under the app that started it.", "#f5f5f7")}
    </>
  );
};

const Stars = ({ frame, scene }: { frame: number; scene: Scene }) => (
  <svg width={scene.W} height={scene.H} style={{ position: "absolute", inset: 0 }}>
    {scene.stars.map((s, i) => {
      const t = interpolate(frame, [T.gatherStart + s.delay, T.gatherStart + s.delay + T.gatherLength], [0, 1], { ...clamp, easing: inOut });
      // Curve the path sideways so the whole sky swirls in rather than sliding.
      const dx = s.tx - s.sx;
      const dy = s.ty - s.sy;
      const len = Math.hypot(dx, dy) || 1;
      const bend = Math.sin(Math.PI * t) * s.curve;
      const drift = (1 - t) * Math.sin(frame / 40 + s.phase) * 6;
      const x = s.sx + dx * t + (-dy / len) * bend + drift;
      const y = s.sy + dy * t + (dx / len) * bend;
      const arrival = s.node ? T.iconsStart + s.node.rank * 2.4 : T.gatherStart + 10;
      const absorbed = interpolate(frame, [arrival + 4, arrival + (s.node ? 16 : 50)], [1, 0], clamp);
      const twinkle = 0.45 + 0.55 * Math.abs(Math.sin(frame / 11 + s.phase));
      const opacity = twinkle * absorbed * interpolate(frame, [0, 14], [0, 1], clamp);
      if (opacity <= 0.01) return null;
      const r = s.size * (1 + t * 0.4);
      return <circle key={i} cx={x} cy={y} r={r} fill={t > 0.02 ? "#bff3ff" : "#ffffff"} opacity={opacity} />;
    })}
  </svg>
);

const Names = ({ frame, scene }: { frame: number; scene: Scene }) => (
  <>
    {scene.named.map(({ name, star, start }, i) => {
      const opacity = interpolate(frame, [start, start + 10, start + 34, start + 46], [0, 0.75, 0.75, 0], clamp) *
        interpolate(frame, [T.gatherStart - 10, T.gatherStart], [1, 0], clamp);
      if (opacity <= 0) return null;
      return (
        <div key={i} style={{ position: "absolute", left: star.sx + 10, top: star.sy - 9, fontFamily: mono, fontSize: 24,
                              color: "#8e8e93", opacity, whiteSpace: "nowrap" }}>
          {name}
        </div>
      );
    })}
  </>
);

const Icons = ({ frame, fps, scene, outroStart }: { frame: number; fps: number; scene: Scene; outroStart: number }) => (
  <>
    {scene.nodes.map((node) => {
      const arrival = T.iconsStart + node.rank * 2.4;
      const pop = spring({ frame: frame - arrival, fps, config: { damping: 13, stiffness: 120, mass: 0.7 } });
      const float = Math.sin((frame + node.rank * 17) / 34) * 4 * interpolate(frame, [arrival, arrival + 30], [0, 1], clamp);
      const outro = interpolate(frame, [outroStart, outroStart + 26], [1, 0.08], { ...clamp, easing: ease });
      const blur = interpolate(frame, [outroStart, outroStart + 26], [0, 6], clamp);
      const glow = interpolate(frame, [arrival, arrival + 10, arrival + 40], [0, 0.9, 0.25], clamp);
      const counts = interpolate(frame, [T.countsStart + node.rank * 1.2, T.countsStart + node.rank * 1.2 + 14], [0, 1], clamp);
      const size = node.size;
      if (pop <= 0.001) return null;
      return (
        <div key={node.name} style={{ position: "absolute", left: node.x - size / 2, top: node.y - size / 2 + float, width: size, height: size,
                                      opacity: outro, filter: blur > 0 ? `blur(${blur}px)` : undefined, transform: `scale(${pop})` }}>
          <div style={{ position: "absolute", inset: -size * 0.18, borderRadius: "50%",
                        background: `radial-gradient(circle, rgba(44,192,222,${0.35 * glow}) 0%, transparent 65%)` }} />
          <Img src={staticFile(`icons/${node.icon}.png`)} style={{ position: "absolute", inset: 0, width: size, height: size }} />
          <div style={{ position: "absolute", top: size - size * 0.04, left: "50%", transform: "translateX(-50%)", opacity: counts,
                        fontFamily: sans, fontSize: size > 150 ? 22 : 18, fontWeight: 600, color: "#f5f5f7", whiteSpace: "nowrap",
                        background: "rgba(44,44,48,.92)", border: "1px solid rgba(255,255,255,.1)", borderRadius: 999,
                        padding: size > 150 ? "3px 12px" : "1px 8px", fontVariantNumeric: "tabular-nums" }}>
            {size > 150 ? `${node.name} · ${node.processes}` : node.processes}
          </div>
        </div>
      );
    })}
  </>
);

const EndCard = ({ frame, fps }: { frame: number; fps: number }) => {
  const s = spring({ frame: frame - (T.outroStart + 8), fps, config: { damping: 15, stiffness: 110 } });
  const text = interpolate(frame, [T.outroStart + 16, T.outroStart + 34], [0, 1], { ...clamp, easing: ease });
  if (s <= 0.001) return null;
  return (
    <AbsoluteFill style={{ display: "grid", placeItems: "center" }}>
      <div style={{ display: "grid", justifyItems: "center", gap: 26 }}>
        <Img src={staticFile("icons/bondi-blue.png")} style={{ width: 196, height: 196, transform: `scale(${s})`,
                                                          filter: "drop-shadow(0 20px 60px rgba(44,192,222,.45))" }} />
        <div style={{ opacity: text, transform: `translateY(${(1 - text) * 14}px)`, textAlign: "center" }}>
          <div style={{ fontFamily: sans, fontWeight: 700, fontSize: 84, letterSpacing: "-0.035em", color: "#f5f5f7" }}>Bondi for Apple Mac</div>
          <div style={{ fontFamily: sans, fontWeight: 500, fontSize: 32, color: bondi, marginTop: 8 }}>Beta launching soon · trybondi.app</div>
        </div>
      </div>
    </AbsoluteFill>
  );
};

export const Constellation = ({ endCard = true }: { endCard?: boolean }) => {
  const frame = useCurrentFrame();
  const { fps, width, height, durationInFrames } = useVideoConfig();
  const scene = sceneFor(width, height);
  const outroStart = endCard ? T.outroStart : durationInFrames + 100; // the intro never reaches its outro
  // The film fades from and to black so the website's loop joins without a jump; the intro only fades in.
  const fade = interpolate(frame, endCard ? [0, 12, durationInFrames - 16, durationInFrames - 1] : [0, 12, 13, 14], endCard ? [0, 1, 1, 0] : [0, 1, 1, 1], clamp);
  return (
    <AbsoluteFill style={{ background: "radial-gradient(ellipse at 50% 60%, #0b1a20 0%, #000 70%)", opacity: fade }}>
      <Stars frame={frame} scene={scene} />
      <Names frame={frame} scene={scene} />
      <Icons frame={frame} fps={fps} scene={scene} outroStart={outroStart} />
      <Headline frame={frame} outro={outroStart} />
      {endCard && <EndCard frame={frame} fps={fps} />}
    </AbsoluteFill>
  );
};
