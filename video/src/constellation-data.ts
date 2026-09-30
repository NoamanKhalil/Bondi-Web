// The star films' data: one real reading from Bondi's engine (`Bondi --dump-engine`), written to reading.json
// by `npm run reading`. Every count is what Bondi measured and every icon is the app's own; nothing is made up.
import reading from "./reading.json";

export const readingDate: string = reading.date;
export const totalProcesses: number = reading.totalProcesses;
export const totalApps: number = reading.totalApps;

/** Apps with a Finder icon, largest first. `icon` is a file in public/icons. */
export const groups: { name: string; icon: string; processes: number }[] = reading.groups;

/** The apps without an icon (background helpers) and their processes: stars that fade out as the rest gather. */
export const otherApps: number = reading.otherApps;
export const otherProcesses: number = reading.otherProcesses;

/** Process names as Activity Monitor lists them, from the same reading. */
export const processNames: string[] = reading.processNames;

/** Each icon's own colour (the weighted average of its colourful pixels, brightened), measured from the icon
 *  files. Stars turn this colour as they reach their app, and it glows under the icon. */
export const iconHues: Record<string, string> = Object.fromEntries(reading.groups.map((g) => [g.icon, g.hue]));
