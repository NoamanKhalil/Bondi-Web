// Every figure and sentence here is real: measured by Bondi on the owner's MacBook Pro (M1 Max, 64 GB)
// on 2026-09-29, or written by Bondi in its Phase 7 tests. Nothing is made up for the film.

/** Process names as Activity Monitor lists them (a real sample from that Mac). */
export const processes = [
  "Google Chrome Helper (Renderer)", "mds_stores", "WindowServer", "Code Helper (Plugin)", "kernel_task",
  "Google Chrome Helper (GPU)", "mdworker_shared", "com.apple.WebKit.WebContent", "photoanalysisd", "coreaudiod",
  "Code Helper (Renderer)", "cloudd", "bird", "Google Chrome Helper (Renderer)", "launchd", "trustd",
  "nsurlsessiond", "Google Chrome Helper", "fseventsd", "Safari", "Notes", "Finder", "Dock", "ControlCenter",
  "Google Chrome Helper (Renderer)", "mediaanalysisd", "sharingd", "Code Helper", "com.apple.WebKit.Networking",
  "Google Chrome Helper (Renderer)", "distnoted", "cfprefsd", "airportd", "bluetoothd", "powerd", "logd",
  "WallpaperAgent", "NotificationCenter", "Google Chrome Helper (Renderer)", "Spotlight", "chronod",
  "TGOnDeviceInferenceProviderService", "com.apple.geod", "Code Helper (GPU)", "softwareupdated",
  "Google Chrome Helper (Renderer)", "syspolicyd", "XprotectService", "corespotlightd", "apsd",
];

export const processCount = 931;
export const appCount = 39;

export const apps = [
  { name: "Google Chrome", memory: "12.84 GB", processes: 75, share: 1 },
  { name: "macOS", memory: "8.67 GB", processes: 715, share: 0.68 },
  { name: "Visual Studio Code", memory: "4.07 GB", processes: 44, share: 0.32 },
  { name: "Safari", memory: "1.47 GB", processes: 16, share: 0.12 },
  { name: "Notes", memory: "526.6 MB", processes: 9, share: 0.04 },
  { name: "Finder", memory: "462.9 MB", processes: 3, share: 0.036 },
];

export const sentences = [
  { lead: "Memory is critically low.", rest: " Bionic holds 21.49 GB with Qwen3.8 27B loaded." },
  { lead: "A dev server is sitting idle.", rest: " Vite in shop has been idle for 3h 20m and holds 612.0 MB." },
];

export const question = "Which app used the most memory this week?";
export const answer =
  "Over the last 7 days, Google Chrome used the most memory, 10.18 GB on average, then macOS (5.81 GB) and Visual Studio Code (3.19 GB).";
