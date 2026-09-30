// Turns one reading from Bondi's engine into the website films' data. Run through refresh-reading.sh.
//
// Input: the text `Bondi --dump-engine` prints (PROCESSES, GROUP and PROC lines).
// Output:
//   src/reading.json   counts, the apps with icons (largest first), each icon's colour, process names
//   public/icons/*.png each app's real Finder icon (256 px), plus Bondi's own "macOS" tile
// Nothing is invented: every count is Bondi's, every icon is the app's own.

import AppKit

let args = CommandLine.arguments
guard args.count == 4 else { print("usage: refresh-reading.swift <dump.txt> <icons dir> <reading.json>"); exit(1) }
let dump = try! String(contentsOfFile: args[1], encoding: .utf8)
let iconsDir = args[2], outFile = args[3]
let maxIcons = 32 // the film's layout holds about this many; smaller apps join "other"

// MARK: Read the dump

var total = 0, groupCount = 0
var groups: [(name: String, processes: Int)] = []
var processes: [(name: String, memory: Double)] = []
for line in dump.split(separator: "\n", omittingEmptySubsequences: true).map(String.init) {
    if line.hasPrefix("PROCESSES ") {
        for part in line.split(separator: " ") {
            if part.hasPrefix("total=") { total = Int(part.dropFirst(6)) ?? 0 }
            if part.hasPrefix("groups=") { groupCount = Int(part.dropFirst(7)) ?? 0 }
        }
    } else if line.hasPrefix("GROUP\t") {
        let f = line.split(separator: "\t").map(String.init)
        let procs = f.first { $0.hasPrefix("procs=") }.flatMap { Int($0.dropFirst(6)) } ?? 0
        groups.append((f[1], procs))
    } else if line.hasPrefix("  PROC\t") {
        let f = line.split(separator: "\t").map(String.init)
        let memory = f.first { $0.hasPrefix("mem=") }.flatMap { Double($0.dropFirst(4)) } ?? 0
        if f.count > 2 { processes.append((f[2], memory)) }
    }
}
guard total > 0, !groups.isEmpty else { print("No PROCESSES/GROUP lines in the dump. Was it made with --dump-engine?"); exit(1) }

// MARK: Find each app's bundle

func bundle(for name: String) -> String? {
    let fm = FileManager.default
    let home = NSHomeDirectory()
    let folders = ["/Applications", "/Applications/Utilities", "/System/Applications", "/System/Applications/Utilities",
                   "/System/Library/CoreServices", "\(home)/Applications"]
    for folder in folders {
        for candidate in [name, name.replacingOccurrences(of: " ", with: "")] {
            let path = "\(folder)/\(candidate).app"
            if fm.fileExists(atPath: path) { return path }
        }
    }
    // Anywhere else Spotlight knows an app by that name (Bondi's own debug build, for example).
    let task = Process()
    task.executableURL = URL(fileURLWithPath: "/usr/bin/mdfind")
    task.arguments = ["kMDItemContentType == 'com.apple.application-bundle' && kMDItemDisplayName == '\(name.replacingOccurrences(of: "'", with: ""))'"]
    let pipe = Pipe(); task.standardOutput = pipe
    try? task.run(); task.waitUntilExit()
    let found = String(data: pipe.fileHandleForReading.readDataToEndOfFile(), encoding: .utf8) ?? ""
    return found.split(separator: "\n").map(String.init).first { $0.hasSuffix(".app") }
}

func slug(_ name: String) -> String { name.lowercased().filter { $0.isLetter || $0.isNumber } }

// MARK: Icons and their colours

func save(_ image: NSImage, to path: String, size: CGFloat = 256) {
    let rep = NSBitmapImageRep(bitmapDataPlanes: nil, pixelsWide: Int(size), pixelsHigh: Int(size), bitsPerSample: 8, samplesPerPixel: 4,
                               hasAlpha: true, isPlanar: false, colorSpaceName: .deviceRGB, bytesPerRow: 0, bitsPerPixel: 0)!
    NSGraphicsContext.saveGraphicsState(); NSGraphicsContext.current = NSGraphicsContext(bitmapImageRep: rep)
    image.draw(in: NSRect(x: 0, y: 0, width: size, height: size)); NSGraphicsContext.restoreGraphicsState()
    try! rep.representation(using: .png, properties: [:])!.write(to: URL(fileURLWithPath: path))
}

/// The icon's main colour: the average of its colourful pixels weighted by how colourful they are, brightened
/// so it reads as a glow on a dark ground. Grey icons get a soft grey.
func hue(ofPNG path: String) -> String {
    let rep = NSBitmapImageRep(data: try! Data(contentsOf: URL(fileURLWithPath: path)))!
    var r = 0.0, g = 0.0, b = 0.0, w = 0.0, grey = 0.0, n = 0.0
    for y in stride(from: 0, to: rep.pixelsHigh, by: 2) {
        for x in stride(from: 0, to: rep.pixelsWide, by: 2) {
            guard let c = rep.colorAt(x: x, y: y)?.usingColorSpace(.sRGB), c.alphaComponent > 0.6 else { continue }
            grey += c.brightnessComponent; n += 1
            guard c.saturationComponent > 0.3, c.brightnessComponent > 0.25 else { continue }
            let weight = c.saturationComponent * c.saturationComponent * c.brightnessComponent
            r += c.redComponent * weight; g += c.greenComponent * weight; b += c.blueComponent * weight; w += weight
        }
    }
    if w < 40 { let v = Int(min(0.8, max(0.55, grey / max(n, 1))) * 255); return String(format: "#%02x%02x%02x", v, v, v) }
    let color = NSColor(srgbRed: r / w, green: g / w, blue: b / w, alpha: 1)
    let lifted = NSColor(hue: color.hueComponent, saturation: min(1, max(0.55, color.saturationComponent)),
                         brightness: max(0.85, color.brightnessComponent), alpha: 1).usingColorSpace(.sRGB)!
    return String(format: "#%02x%02x%02x", Int(lifted.redComponent * 255), Int(lifted.greenComponent * 255), Int(lifted.blueComponent * 255))
}

/// Bondi's own tile for the macOS group: its SF Symbol on a graphite app-icon shape.
func macOSTile() -> NSImage {
    NSImage(size: NSSize(width: 256, height: 256), flipped: false) { rect in
        let path = NSBezierPath(roundedRect: rect.insetBy(dx: 25, dy: 25), xRadius: 46, yRadius: 46)
        NSGradient(starting: NSColor(white: 0.36, alpha: 1), ending: NSColor(white: 0.16, alpha: 1))!.draw(in: path, angle: -90)
        let config = NSImage.SymbolConfiguration(pointSize: 104, weight: .medium).applying(.init(paletteColors: [.white]))
        let symbol = NSImage(systemSymbolName: "square.stack.3d.up.fill", accessibilityDescription: nil)!.withSymbolConfiguration(config)!
        let s = symbol.size
        symbol.draw(in: NSRect(x: rect.midX - s.width / 2, y: rect.midY - s.height / 2, width: s.width, height: s.height))
        return true
    }
}

// Start clean, but keep files that aren't per-app (the end card's Bondi Blue icon).
let fm = FileManager.default
for file in (try? fm.contentsOfDirectory(atPath: iconsDir)) ?? [] where file.hasSuffix(".png") && file != "bondi-blue.png" {
    try? fm.removeItem(atPath: "\(iconsDir)/\(file)")
}

var shown: [[String: Any]] = []
for group in groups.sorted(by: { $0.processes > $1.processes }) where shown.count < maxIcons {
    let key = slug(group.name)
    let file = "\(iconsDir)/\(key).png"
    if group.name == "macOS" {
        save(macOSTile(), to: file)
    } else if let app = bundle(for: group.name) {
        let icon = NSWorkspace.shared.icon(forFile: app); icon.size = NSSize(width: 256, height: 256)
        save(icon, to: file)
    } else {
        continue // no app bundle (a background helper): it joins "other"
    }
    shown.append(["name": group.name, "icon": key, "processes": group.processes, "hue": hue(ofPNG: file)])
}

// Process names as Activity Monitor lists them: the biggest ones by memory, each once, short enough to read.
var names: [String] = []
for p in processes.sorted(by: { $0.memory > $1.memory }) where p.name.count <= 34 && !names.contains(p.name) && names.count < 38 {
    names.append(p.name)
}

let shownProcesses = shown.reduce(0) { $0 + ($1["processes"] as! Int) }
let formatter = DateFormatter(); formatter.dateFormat = "yyyy-MM-dd"
let reading: [String: Any] = [
    "date": formatter.string(from: Date()),
    "machine": Host.current().localizedName ?? "this Mac",
    "totalProcesses": total,
    "totalApps": groupCount,
    "otherApps": groupCount - shown.count,
    "otherProcesses": total - shownProcesses,
    "groups": shown,
    "processNames": names,
]
let data = try! JSONSerialization.data(withJSONObject: reading, options: [.prettyPrinted, .sortedKeys])
try! data.write(to: URL(fileURLWithPath: outFile))
print("\(total) processes, \(groupCount) apps; \(shown.count) with icons, \(groupCount - shown.count) others (\(total - shownProcesses) processes).")
