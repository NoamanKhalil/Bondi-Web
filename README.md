# Bondi-Web

Everything for **trybondi.app**: the website, the license server, and the website films. The Mac app is in
its own repo, [TryBondi](https://github.com/NoamanKhalil/TryBondi), kept next to this one on disk.

- `public_html/`: the website and the license API, admin and buy pages (see the table below)
- `bondi/`, `sql/`: the license server's private code and database setup
- `video/`: the website films (Remotion). Refresh, render and publish them with `npm run reading`,
  `npm run render:all` and `npm run assets` in `video/`; details in `.claude/skills/website-video/SKILL.md`
- `dev/`: local tests

## License server

Runs on Hostinger with PHP and MySQL; nothing else to install. It starts and checks trials, sells through
Paddle, emails license keys, activates one Mac per license, and has an admin page for revoking keys.

| Part | On Hostinger | What it is |
| --- | --- | --- |
| `bondi/` | `domains/trybondi.app/bondi/` (next to `public_html`, not inside it) | The program and its settings; can't be opened from the web |
| `public_html/api/` | `public_html/api/` | The API the app and Paddle talk to |
| `public_html/admin/` | `public_html/admin/` | Admin page (password) |
| `public_html/buy/` | `public_html/buy/` | Buy page that opens Paddle's checkout, and the thank-you page |
| `sql/` | Run in phpMyAdmin | Database setup |
| `dev/` | Don't upload | Local testing only |

## Set it up

1. **PHP version.** hPanel → Advanced → PHP Configuration: 8.1 or newer (8.3 is fine).
2. **Database.** hPanel → Databases → Management: create a database and a user. In phpMyAdmin, Import
   `sql/001_schema.sql`, then `sql/003_checkout_claims.sql`, then `sql/004_signups.sql`. (`002` only if you
   imported `001` before the one-Mac change on 2026-09-29.) Already imported the others? Just import `004`.
3. **Email.** hPanel → Emails: create `licenses@trybondi.app` (keys are sent from it) and
   `support@trybondi.app`.
4. **Upload.** hPanel → Files → File Manager, open `domains/trybondi.app/`:
   - upload the `bondi` folder there, so it sits next to `public_html`;
   - upload the `api`, `admin` and `buy` folders into `public_html`, and the website (`index.html`,
     `assets`, `privacy`, `terms`, `eula`, `refunds`).
5. **Settings.** In File Manager, open `bondi/`, copy `config.example.php` to `config.php`, and edit it:
   - `db`: the database name, user and password from step 2;
   - `admin_password_hash`: on your Mac, in Terminal, run
     `php -r 'echo password_hash("your password here", PASSWORD_DEFAULT), PHP_EOL;'` and paste the result;
   - leave `test_mode` as `false`.
   Never put `config.php` in git or in `public_html`.
6. **Check.** Open `https://trybondi.app/api/offer`: it should show `"price":"6.99"` and
   `"launch_remaining":250`. Open `https://trybondi.app/admin/` and sign in.

## Connect Paddle (sandbox first)

1. **Product.** Catalog → Products → New product "Bondi"; add two one-time prices, $6.99 and $29.99.
2. **Price IDs.** In phpMyAdmin → SQL, with the two `pri_…` IDs from Paddle:
   ```sql
   UPDATE price_tiers SET paddle_price_id = 'pri_…' WHERE tier = 'launch';
   UPDATE price_tiers SET paddle_price_id = 'pri_…' WHERE tier = 'regular';
   ```
3. **Keys.** Developer tools → Authentication: create an API key (read access to customers is enough) and a
   client-side token. Put them in `config.php` as `paddle.api_key` and `paddle.client_token`.
4. **Notifications.** Developer tools → Notifications → New destination: URL `https://trybondi.app/api/paddle`,
   events `transaction.completed`, `adjustment.created`, `adjustment.updated`. Copy its secret key into
   `config.php` as `paddle.webhook_secret`.
5. **Checkout link.** Checkout → Checkout settings: set the default payment link to `https://trybondi.app/buy/`.
6. **Try it.** Open `https://trybondi.app/buy/`, buy with one of Paddle's test cards, and check that the key
   email arrives and the license appears in the admin page.

When everything works in sandbox, create the same product and prices in Paddle's live account, repeat
steps 2 to 5 with the live IDs and keys, and set `paddle.environment` to `production`. In live mode Paddle
also asks you to approve the domain (Checkout → Website approval).

## Update sign-ups

The website's "Sign up for updates" form saves name, email, IP address and country in the `signups` table.
See them in the admin page under **Sign-ups** (count by country, search, remove, **Download all as CSV**
for your email tool).

Country comes from the IP when your host or CDN adds a country header (Cloudflare's `CF-IPCountry`, or
Apache's GeoIP module); otherwise from the visitor's time zone, which is right for almost everyone. The
admin page marks those "(time zone)". No outside lookup service sees visitors' IPs. If you put the site
behind Cloudflare, also set `client_ip_header` to `HTTP_CF_CONNECTING_IP` in `config.php` so the saved
IP is the visitor's, not Cloudflare's.

## What it keeps

Trials: an anonymous device ID (a hash made on the Mac) and the start date. Buyers: email and Paddle's IDs.
Licenses and activations: key and token hashes only, the Mac's name, dates. Nothing about apps or usage.
Update sign-ups: name, email, IP address, country and time zone.

## Test it locally

`dev/test.sh` starts a throwaway MySQL and PHP's built-in server on your Mac, runs 51 checks
(trial, checkout, a signed fake Paddle payment, one Mac per license, recovery, refund, admin revoke and
restore, update sign-ups), and removes everything afterwards. It needs Homebrew's `mysql` and `php`.
