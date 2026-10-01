# Bondi license API: what the Mac app needs

For the session building Phase 8 (trial, buying and licenses) in `../TryBondi`. The server side is done and live
at `https://trybondi.app/api/`; this is its contract, taken from `public_html/api/index.php` and `bondi/lib/`.
Build the app side against it. Don't change the server from the app repo; ask the owner for a change in
Bondi-Web instead.

Product rules come from the app's `docs/PLAN.md` (Flow 1 FR-04 to FR-07, Flow 8 BY-01 to BY-10, Phase 8).

## Basics

- Every call: `https://trybondi.app/api/<action>`. `offer` and `interest` are GET; everything else is POST with a
  JSON body (`Content-Type: application/json`).
- Answers are JSON. Success is HTTP 200. Failures have an HTTP error status and
  `{"error": "<code>", "message": "<plain English for the user>", ...}`. The `message` is written to be shown
  as-is in the app.
- Every endpoint can answer `429 {"error": "rate_limited"}` (limits are per IP address) and
  `500 {"error": "server"}`. Treat both, and no network at all, as "server unreachable" (see Offline).
- Keep the base URL in one place, with a Debug-only override for testing against a local server.

## What the app sends (and nothing else)

- `device_hash`: an anonymous device ID, 64 lowercase hex characters: SHA-256 of a fixed prefix plus the Mac's
  hardware UUID, for example `sha256("bondi-device|" + IOPlatformUUID)`. Never send the UUID itself. It must
  stay the same across reinstalls, so reinstalling can't restart the trial.
- `device_name`: the Mac's name (`Host.current().localizedName`), up to 100 characters. Sent only with
  `checkout` and `activate`; the user sees it in "in use on <name>".
- `app_version` with `trial`; the license `key`, the activation `token` or the checkout `claim` where needed;
  the email for `recover`.

The privacy policy promises exactly this: an anonymous device ID, the Mac's name when activating, and the
license key or token. Nothing about apps or usage. The app's Data settings promise to list each request.

## Endpoints

### `POST trial` — start or read this Mac's 7-day trial (FR-04, FR-05, BY-01)
Body `{"device_hash", "app_version"}` → `{"started_at", "ends_at", "server_time"}` (ISO 8601, UTC).
The first call starts the trial; later calls return the same dates (the server keys it on `device_hash`).
Work out days left from `ends_at` against `server_time`, not the Mac's clock. Limit: 30 per hour.

### `GET offer` — the current price (BY-02, BY-03)
→ `{"tier": "launch"|"regular", "price": "6.99", "currency": "USD", "launch_remaining": 250 | null, "server_time"}`.
Show the price from here; never hard-code it. `launch_remaining` is null once the launch price is over.

### `POST checkout` — before opening the browser to buy (BY-03, BY-04)
Body `{"device_hash", "device_name"}` → `{"claim", "url", "tier", "price", "currency", "launch_remaining"}`.
Keep `claim` (Keychain) and open `url` in the browser: the trybondi.app buy page with Paddle's checkout. Limit: 20 per hour.

### `POST claim` — after paying, activate this Mac without typing a key (BY-05, BY-06)
Body `{"claim"}` →
- `{"status": "pending"}`: not paid yet (or Paddle hasn't told the server). Ask again.
- `{"status": "active", "key_last4", "email", "token", "key"}`: bought and activated on this Mac. Keep `token`
  (Keychain). `key` is the full key, returned this once so the Thank You screen can show it; it's also emailed.
- `{"status": "claimed", "message"}`: already used (for example by an earlier poll). If this Mac has no token, show the message and the key field.
- `404 not_found`: unknown or older than 2 days. `409` with `error` = `in_use`/`refunded`/`revoked`: show `message`, then the key field (BY-07).

Ask right after `bondi://open` arrives and whenever Bondi becomes active again, and also poll: every 5 seconds
for 2 minutes, then every 15 seconds, stopping after 15 minutes (the limit is 120 per hour). Forget the claim once
it's settled.

### `POST activate` — a pasted or emailed key (BY-07)
Body `{"key", "device_hash", "device_name"}` → `{"status": "active", "key_last4", "email", "token"}`.
Send the text as typed: the server ignores case, spaces, line breaks and dashes, and reads O/I/L as 0/1.
Activating the same Mac again is fine (it gets a fresh token). Errors:
- `404 invalid_key`
- `409 in_use`, with `device_name` of the Mac that holds it ("Choose Deactivate This Mac there first")
- `403 refunded` or `403 revoked`
- Limit: 10 per 10 minutes.

### `POST check` — is this Mac's license still good
Body `{"token", "device_hash"}` →
- `{"status": "active", "key_last4", "email", "server_time"}`: keep going.
- `{"status": "refunded" | "revoked", ...}`: lock, with a polite message (PLAN: refund issued).
- `{"status": "deactivated", "server_time"}`: this Mac no longer holds the license (moved to another Mac,
  deactivated in the admin page, or an unknown token). Delete the token and show the key field.
Check about once a day while Bondi runs, and at launch if the last good check is older than a day. Limit: 120 per hour.

### `POST deactivate` — Deactivate This Mac (BY-09)
Body `{"token"}` → always `{"status": "deactivated"}`. Delete the token, then lock. Safe to repeat.

### `POST recover` — Find My Key (BY-10)
Body `{"email"}` → always `{"status": "sent", "message"}`, whether or not the email bought Bondi (so no one can
test addresses). `400 email` if it isn't an email address. A new key is emailed; the old key stops working,
and Macs already activated keep working (their tokens are unaffected). Limit: 10 per hour.

## Links into the app

Register the `bondi` URL scheme (Info.plist `CFBundleURLTypes`):
- `bondi://activate?key=BONDI-XXXXX-XXXXX-XXXXX-XXXXX`: from the license email's "Activate on this Mac" button.
  Open License settings with the key filled in and activate it.
- `bondi://open`: from the buy page's "Open Bondi" button after an in-app purchase. Bring Bondi forward and
  ask `claim` straight away.

## Storage

- Keychain: `token` and a pending `claim`.
- Settings (fine in UserDefaults): `ends_at`, `key_last4`, `email`, the last check result and when it succeeded.
- Never store the full key after activation. Show it masked as `••••<key_last4>`.

## Offline and errors (PLAN)

- First run with no network (FR-07): "You're offline. Bondi works now; we'll start your trial when you're
  back online." A local 3-day grace period, then ask `trial` when the network is back.
- Licensed, but the server is unreachable (or answers 429/500): keep working for 14 days from the last
  successful `check`, then ask to reconnect. Never lock because of a network problem alone.
- After the trial (BY-02): the menu bar keeps showing CPU; everything else waits for a license.

## Wording to update in the app

`SettingsView.swift` (License and Data tabs) says "Activating and deactivating are the only times Bondi contacts
the license server". With this design Bondi also starts the trial, checks the license about once a day and
asks after a purchase. Change it to match the privacy policy: "Bondi contacts trybondi.app only to start your
trial, check your license and look for updates. It sends an anonymous device ID, never anything about your apps."

## Testing

- Live server, today: trials work. Paddle isn't set up yet, so `checkout` and `claim` can't complete a real
  purchase. For activate, check, deactivate and recover, the owner can make a free license in the admin page
  (trybondi.app/admin, "Give a free license") and email its key to a test address.
- After a test, the owner can revoke the license or deactivate the test Mac in the admin page; Bondi's next
  `check` should then lock it.
- Paddle sandbox (Phase 8 "Paddle sandbox purchase activates Bondi through the bondi:// link") needs the owner's
  Paddle keys in the server's config first.
