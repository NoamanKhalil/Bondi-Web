import { AbsoluteFill, Easing, interpolate, Sequence, spring, useCurrentFrame, useVideoConfig } from "remotion";
import { loadFont as loadDisplay } from "@remotion/google-fonts/BricolageGrotesque";
import { loadFont as loadBody } from "@remotion/google-fonts/IBMPlexSans";
import { loadFont as loadMono } from "@remotion/google-fonts/IBMPlexMono";
import { answer, appCount, apps, processCount, processes, question, sentences } from "./data";

const display = loadDisplay("normal", { weights: ["700", "800"], subsets: ["latin"] }).fontFamily;
const body = loadBody("normal", { weights: ["400", "500", "600"], subsets: ["latin"] }).fontFamily;
const mono = loadMono("normal", { weights: ["400", "500"], subsets: ["latin"] }).fontFamily;

export const FPS = 30;
export const FILM_FRAMES = 24 * FPS;

// Bondi's website palette (dark): the 1998 iMac's Bondi Blue on deep ink.
const color = {
  ground: "#06161b",
  surface: "#0d242b",
  line: "#1d3d47",
  ink: "#e4f3f6",
  soft: "#9dbac3",
  bondi: "#2cc0de",
  memory: "#9b7ae0",
};

const clamp = { extrapolateLeft: "clamp", extrapolateRight: "clamp" } as const;
const ease = Easing.bezier(0.2, 0.7, 0.2, 1);

/** Fades a scene in over its first frames and out over its last. */
const useSceneFade = (length: number, fade = 12) => {
  const frame = useCurrentFrame();
  return interpolate(frame, [0, fade, length - fade, length], [0, 1, 1, 0], clamp);
};

const Caption = ({ children, delay = 0 }: { children: React.ReactNode; delay?: number }) => {
  const frame = useCurrentFrame();
  const t = interpolate(frame - delay, [0, 18], [0, 1], { ...clamp, easing: ease });
  return (
    <div style={{ fontFamily: display, fontWeight: 800, fontSize: 84, lineHeight: 1.04, letterSpacing: "-0.03em",
                  color: color.ink, opacity: t, transform: `translateY(${(1 - t) * 24}px)` }}>
      {children}
    </div>
  );
};

/** Types text out, a few characters a frame. */
const typed = (text: string, frame: number, start: number, perFrame = 1.6) =>
  text.slice(0, Math.max(0, Math.floor((frame - start) * perFrame)));

// Scene 1: Activity Monitor's wall of processes, and the count.
const Processes = ({ length }: { length: number }) => {
  const frame = useCurrentFrame();
  const fade = useSceneFade(length);
  const count = Math.round(interpolate(frame, [10, 90], [0, processCount], { ...clamp, easing: ease }));
  const rows = Array.from({ length: 4 }, () => processes).flat();
  return (
    <AbsoluteFill style={{ opacity: fade }}>
      <div style={{ position: "absolute", right: 100, top: 0, bottom: 0, width: 680, overflow: "hidden",
                    maskImage: "linear-gradient(transparent, black 20%, black 80%, transparent)" }}>
        <div style={{ transform: `translateY(${-frame * 5}px)`, display: "grid", gridTemplateColumns: "1fr 1fr", gap: "10px 28px",
                      fontFamily: mono, fontSize: 19, color: color.soft, opacity: 0.55 }}>
          {rows.map((name, i) => <div key={i} style={{ whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" }}>{name}</div>)}
        </div>
      </div>
      <div style={{ position: "absolute", left: 140, top: 300, width: 820, display: "grid", gap: 28 }}>
        <div style={{ fontFamily: mono, fontSize: 30, color: color.bondi, letterSpacing: "0.08em" }}>ACTIVITY MONITOR</div>
        <Caption>Your Mac is running <span style={{ color: color.bondi, fontFamily: mono, fontWeight: 500 }}>{count}</span> processes.</Caption>
        <div style={{ fontFamily: body, fontSize: 36, color: color.soft }}>Can you tell which one is slowing it down?</div>
      </div>
    </AbsoluteFill>
  );
};

// Scene 2: the same Mac, grouped into apps.
const Apps = ({ length }: { length: number }) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const fade = useSceneFade(length);
  const count = Math.round(interpolate(frame, [0, 40], [processCount, appCount], { ...clamp, easing: ease }));
  return (
    <AbsoluteFill style={{ opacity: fade }}>
      <div style={{ position: "absolute", left: 140, top: 300, width: 760, display: "grid", gap: 28 }}>
        <div style={{ fontFamily: mono, fontSize: 30, color: color.bondi, letterSpacing: "0.08em" }}>BONDI</div>
        <Caption>The same Mac, as <span style={{ color: color.bondi, fontFamily: mono, fontWeight: 500 }}>{count}</span> apps you know.</Caption>
        <div style={{ fontFamily: body, fontSize: 36, color: color.soft }}>Every helper counted under the app it belongs to.</div>
      </div>
      <div style={{ position: "absolute", right: 120, top: 200, width: 860, background: color.surface, border: `2px solid ${color.line}`,
                    borderRadius: 32, padding: "28px 36px", display: "grid", gap: 6 }}>
        {apps.map((app, i) => {
          const s = spring({ frame: frame - 8 - i * 5, fps, config: { damping: 18, stiffness: 120 } });
          return (
            <div key={app.name} style={{ display: "grid", gridTemplateColumns: "1fr 190px 210px", alignItems: "center", gap: 24,
                                         padding: "18px 0", borderTop: i ? `1px solid ${color.line}` : "none",
                                         opacity: s, transform: `translateX(${(1 - s) * 60}px)` }}>
              <div style={{ fontFamily: body, fontWeight: 600, fontSize: 34, color: color.ink }}>
                {app.name}
                <div style={{ fontFamily: body, fontWeight: 400, fontSize: 22, color: color.soft }}>{app.processes} processes</div>
              </div>
              <div style={{ height: 12, borderRadius: 6, background: color.line }}>
                <div style={{ height: 12, borderRadius: 6, background: color.memory, width: `${Math.max(4, app.share * 100 * s)}%` }} />
              </div>
              <div style={{ fontFamily: mono, fontSize: 32, color: color.ink, textAlign: "right", whiteSpace: "nowrap" }}>{app.memory}</div>
            </div>
          );
        })}
      </div>
    </AbsoluteFill>
  );
};

const Sparkle = ({ size = 40 }: { size?: number }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" aria-hidden>
    <path d="M12 1.5l2.3 6.6 6.7 2.4-6.7 2.4L12 19.5l-2.3-6.6L3 10.5l6.7-2.4z" fill={color.bondi} />
    <path d="M19.5 15.5l.9 2.4 2.4.9-2.4.9-.9 2.4-.9-2.4-2.4-.9 2.4-.9z" fill={color.bondi} />
  </svg>
);

// Scene 3: what Bondi says, in one sentence with real figures.
const Says = ({ length }: { length: number }) => {
  const frame = useCurrentFrame();
  const fade = useSceneFade(length);
  return (
    <AbsoluteFill style={{ opacity: fade }}>
      <div style={{ position: "absolute", left: 140, right: 140, top: 150, display: "grid", gap: 44 }}>
        <Caption>One sentence. Real numbers.</Caption>
        {sentences.map((sentence, i) => {
          const start = 20 + i * 70;
          const full = sentence.lead + sentence.rest;
          const text = typed(full, frame, start, 2.2);
          const shown = frame >= start;
          return (
            <div key={i} style={{ display: "flex", gap: 28, alignItems: "flex-start", background: color.surface,
                                  border: `2px solid ${color.line}`, borderRadius: 28, padding: "36px 44px", opacity: shown ? 1 : 0 }}>
              <Sparkle size={44} />
              <div style={{ fontFamily: body, fontSize: 44, lineHeight: 1.35, color: color.ink, minHeight: 60 }}>
                <b style={{ fontWeight: 600 }}>{text.slice(0, sentence.lead.length)}</b>
                {text.slice(sentence.lead.length)}
              </div>
            </div>
          );
        })}
        <div style={{ fontFamily: body, fontSize: 34, color: color.soft, opacity: interpolate(frame, [150, 165], [0, 1], clamp) }}>
          Bondi measures every figure. The on-device AI only writes the words.
        </div>
      </div>
    </AbsoluteFill>
  );
};

// Scene 4: ask about the last 30 days.
const Ask = ({ length }: { length: number }) => {
  const frame = useCurrentFrame();
  const fade = useSceneFade(length);
  const q = typed(question, frame, 10, 1.8);
  const answerOpacity = interpolate(frame, [50, 62], [0, 1], clamp);
  return (
    <AbsoluteFill style={{ opacity: fade }}>
      <div style={{ position: "absolute", left: 140, right: 140, top: 170, display: "grid", gap: 40 }}>
        <Caption>Ask about the last 30 days.</Caption>
        <div style={{ fontFamily: body, fontSize: 42, color: color.ink, border: `2px solid ${color.bondi}`, borderRadius: 22,
                      padding: "26px 34px", background: color.surface }}>
          {q}<span style={{ opacity: frame % 20 < 10 ? 1 : 0, color: color.bondi }}>|</span>
        </div>
        <div style={{ display: "flex", gap: 28, opacity: answerOpacity, transform: `translateY(${(1 - answerOpacity) * 16}px)` }}>
          <Sparkle size={44} />
          <div style={{ fontFamily: body, fontSize: 40, lineHeight: 1.4, color: color.ink }}>{answer}</div>
        </div>
      </div>
    </AbsoluteFill>
  );
};

// Scene 5: the name and where to get it.
const EndCard = ({ length }: { length: number }) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const s = spring({ frame, fps, config: { damping: 16 } });
  const fade = interpolate(frame, [0, 12], [0, 1], clamp);
  return (
    <AbsoluteFill style={{ opacity: fade, alignItems: "center", justifyContent: "center", gap: 34, flexDirection: "column" }}>
      <div style={{ width: 150, height: 150, borderRadius: 40, transform: `scale(${0.8 + 0.2 * s})`,
                    background: `repeating-linear-gradient(0deg, rgba(255,255,255,0.28) 0 6px, transparent 6px 15px), ${color.bondi}` }} />
      <div style={{ fontFamily: display, fontWeight: 800, fontSize: 150, color: color.ink, letterSpacing: "-0.04em", lineHeight: 1 }}>Bondi</div>
      <div style={{ fontFamily: body, fontSize: 46, color: color.soft }}>The AI that tells you why your Mac is slow.</div>
      <div style={{ fontFamily: mono, fontSize: 34, color: color.bondi, marginTop: 10 }}>trybondi.app</div>
    </AbsoluteFill>
  );
};

const scenes = [
  { component: Processes, length: 150 },
  { component: Apps, length: 180 },
  { component: Says, length: 190 },
  { component: Ask, length: 120 },
  { component: EndCard, length: 80 },
];

export const BondiFilm = () => {
  let from = 0;
  return (
    <AbsoluteFill style={{ background: color.ground }}>
      {/* The iMac G3's translucent shell, as faint horizontal stripes. */}
      <AbsoluteFill style={{ background: "repeating-linear-gradient(0deg, rgba(44,192,222,0.035) 0 3px, transparent 3px 9px)" }} />
      {scenes.map(({ component: Scene, length }) => {
        const start = from;
        from += length;
        return (
          <Sequence key={start} from={start} durationInFrames={length}>
            <Scene length={length} />
          </Sequence>
        );
      })}
    </AbsoluteFill>
  );
};
