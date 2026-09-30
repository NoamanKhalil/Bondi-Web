// One real reading from Bondi's engine (`Bondi --dump-engine`) on the owner's MacBook Pro, 2026-09-29:
// 955 processes, grouped into 43 apps. Every count below is what Bondi measured; nothing is made up.

export const totalProcesses = 955;
export const totalApps = 43;

/** Apps with a Finder icon (exported from that Mac), largest first. `icon` is a file in public/icons. */
export const groups: { name: string; icon: string; processes: number }[] = [
  { name: "macOS", icon: "macos", processes: 719 },
  { name: "Google Chrome", icon: "chrome", processes: 80 },
  { name: "Visual Studio Code", icon: "vscode", processes: 44 },
  { name: "Bionic", icon: "bionic", processes: 10 },
  { name: "TextEdit", icon: "textedit", processes: 10 },
  { name: "Notes", icon: "notes", processes: 9 },
  { name: "Docker", icon: "docker", processes: 9 },
  { name: "Mail", icon: "mail", processes: 8 },
  { name: "iPhone Mirroring", icon: "iphonemirroring", processes: 7 },
  { name: "Preview", icon: "preview", processes: 6 },
  { name: "Messages", icon: "messages", processes: 5 },
  { name: "Finder", icon: "finder", processes: 3 },
  { name: "Activity Monitor", icon: "activitymonitor", processes: 3 },
  { name: "Bondi", icon: "bondi", processes: 3 },
  { name: "News", icon: "news", processes: 3 },
  { name: "Home", icon: "home", processes: 2 },
  { name: "Journal", icon: "journal", processes: 2 },
  { name: "Weather", icon: "weather", processes: 2 },
  { name: "Find My", icon: "findmy", processes: 2 },
  { name: "Voice Memos", icon: "voicememos", processes: 2 },
  { name: "Terminal", icon: "terminal", processes: 1 },
  { name: "OneDrive", icon: "onedrive", processes: 1 },
  { name: "Stocks", icon: "stocks", processes: 1 },
  { name: "Calendar", icon: "calendar", processes: 1 },
  { name: "Shortcuts", icon: "shortcuts", processes: 1 },
  { name: "Podcasts", icon: "podcasts", processes: 1 },
  { name: "Photos", icon: "photos", processes: 1 },
  { name: "Safari", icon: "safari", processes: 1 },
  { name: "Reminders", icon: "reminders", processes: 1 },
  { name: "Clock", icon: "clock", processes: 1 },
  { name: "Tips", icon: "tips", processes: 1 },
  { name: "System Settings", icon: "settings", processes: 1 },
];

/** The other 11 apps are background helpers (XProtect, NTFS for Mac, simulator services and so on). */
export const otherApps = totalApps - groups.length;
export const otherProcesses = totalProcesses - groups.reduce((sum, g) => sum + g.processes, 0);

/** Process names as Activity Monitor lists them, from the same Mac. */
export const processNames = [
  "Google Chrome Helper (Renderer)", "mds_stores", "WindowServer", "Code Helper (Plugin)", "kernel_task",
  "Google Chrome Helper (GPU)", "mdworker_shared", "com.apple.WebKit.WebContent", "photoanalysisd", "coreaudiod",
  "Code Helper (Renderer)", "cloudd", "bird", "launchd", "trustd", "nsurlsessiond", "fseventsd", "Dock",
  "ControlCenter", "mediaanalysisd", "sharingd", "distnoted", "cfprefsd", "airportd", "bluetoothd", "powerd",
  "WallpaperAgent", "NotificationCenter", "chronod", "com.apple.geod", "Code Helper (GPU)", "syspolicyd",
  "XprotectService", "corespotlightd", "apsd", "com.docker.backend", "Mail Web Content", "Messages Helper",
];

/** Each icon's own colour (the weighted average of its colourful pixels, brightened), measured from the
 *  icon files. Stars turn this colour as they reach their app, and it glows under the icon. */
export const iconHues: Record<string, string> = {
  activitymonitor: "#61d8a4",
  bionic: "#61bcd8",
  bondi: "#42c3d8",
  "bondi-blue": "#18bdd8",
  calendar: "#fe595a",
  chrome: "#d8cf5d",
  clock: "#f8a001",
  docker: "#1da3ef",
  finder: "#04affd",
  findmy: "#06d877",
  home: "#feb514",
  iphonemirroring: "#6a8fec",
  journal: "#9b61d8",
  macos: "#8c8c8c",
  mail: "#3ba0f9",
  messages: "#40d867",
  news: "#fe6378",
  notes: "#fedd42",
  onedrive: "#18adf7",
  photos: "#d8ba52",
  podcasts: "#a961d8",
  preview: "#6eaafc",
  reminders: "#d86179",
  safari: "#4b9ff0",
  settings: "#afafaf",
  shortcuts: "#a661d8",
  stocks: "#0ab1da",
  terminal: "#8c8c8c",
  textedit: "#cccccc",
  tips: "#feca0c",
  voicememos: "#d86165",
  vscode: "#129bde",
  weather: "#3b9ed8",
};
