#!/bin/bash
# Local end-to-end test of the license server: a throwaway MySQL, PHP's built-in server, and fake
# Paddle notifications signed like the real ones. Nothing is sent anywhere; test mode writes emails to
# bondi/mail.log. Everything is removed at the end. Usage: dev/test.sh
# SNAP=<folder> dev/test.sh also saves the admin pages there as HTML, to look at.
set -u
cd "$(dirname "$0")/.."
T=$(mktemp -d)
SOCK=$T/mysql.sock
PORT=8099
BASE=http://127.0.0.1:$PORT
SECRET=pdl_ntfset_test_secret
PHP_PID=
cleanup() {
  kill $PHP_PID 2>/dev/null
  mysqladmin -uroot --socket=$SOCK shutdown 2>/dev/null
  rm -rf "$T" bondi/mail.log
}
trap cleanup EXIT

mysqld --initialize-insecure --datadir=$T/data --log-error=$T/init.log >/dev/null 2>&1
mysqld --datadir=$T/data --socket=$SOCK --port=33098 --mysqlx=OFF --log-error=$T/err.log --pid-file=$T/pid >/dev/null 2>&1 &
for i in $(seq 1 30); do mysql -uroot --socket=$SOCK -e 'select 1' >/dev/null 2>&1 && break; sleep 1; done
mysql -uroot --socket=$SOCK -e "create database bondi; create user 'bondi'@'localhost' identified by 'test'; grant all on bondi.* to 'bondi'@'localhost';"
for f in sql/001_schema.sql sql/002_one_mac_per_license.sql sql/003_checkout_claims.sql sql/004_signups.sql; do mysql -uroot --socket=$SOCK bondi < $f; done
mysql -uroot --socket=$SOCK bondi -e "update price_tiers set paddle_price_id='pri_launch' where tier='launch'; update price_tiers set paddle_price_id='pri_regular' where tier='regular';"
cat > $T/config.php <<CONF
<?php
return ['base_url' => '$BASE', 'db' => ['host' => 'localhost;unix_socket=$SOCK', 'name' => 'bondi', 'user' => 'bondi', 'pass' => 'test'],
  'paddle' => ['environment' => 'sandbox', 'api_key' => '', 'client_token' => 'test_token', 'webhook_secret' => '$SECRET'],
  'mail_from' => 'Bondi <licenses@example.com>', 'support_email' => 'support@example.com',
  'admin_username' => 'owner', 'admin_password' => 'admin-test', 'trial_days' => 7, 'test_mode' => true,
  'client_ip_header' => 'HTTP_X_TEST_IP'];
CONF
BONDI_CONFIG=$T/config.php php -S 127.0.0.1:$PORT -t "$PWD/public_html" "$PWD/dev/router.php" >$T/php.log 2>&1 &
PHP_PID=$!
sleep 1

DEV=$(printf 'test-mac' | shasum -a 256 | cut -c1-64)
DEV2=$(printf 'second-mac' | shasum -a 256 | cut -c1-64)
post() { curl -s -X POST "$BASE/api/$1" -H 'Content-Type: application/json' -d "$2"; echo; }
paddle() { # event json
  local ts=$(date +%s); local sig=$(printf '%s:%s' "$ts" "$1" | openssl dgst -sha256 -hmac "$SECRET" | sed 's/.*= //')
  curl -s -X POST "$BASE/api/paddle" -H "Paddle-Signature: ts=$ts;h1=$sig" -H 'Content-Type: application/json' -d "$1"; echo
}
PASS=0; FAILS=0
expect() { if echo "$2" | grep -q "$3"; then PASS=$((PASS+1)); echo "ok   $1"; else FAILS=$((FAILS+1)); echo "FAIL $1: $2"; fi; }

expect "offer is the launch price" "$(curl -s $BASE/api/offer)" '"price":"6.99".*"launch_remaining":250'
expect "trial starts" "$(post trial "{\"device_hash\":\"$DEV\",\"app_version\":\"1.0\"}")" '"ends_at"'
FIRST=$(post trial "{\"device_hash\":\"$DEV\"}" | sed 's/.*"started_at":"\([^"]*\)".*/\1/')
sleep 1
expect "trial doesn't restart" "$(post trial "{\"device_hash\":\"$DEV\"}")" "\"started_at\":\"$FIRST\""
expect "bad device ID refused" "$(post trial '{"device_hash":"nope"}')" '"error":"device"'

CHECKOUT=$(post checkout "{\"device_hash\":\"$DEV\",\"device_name\":\"Test MacBook\"}")
expect "checkout gives a claim" "$CHECKOUT" '"url":".*/buy/?claim='
CLAIM=$(echo "$CHECKOUT" | sed 's/.*"claim":"\([a-f0-9]*\)".*/\1/')
CLAIM_HASH=$(printf '%s' "$CLAIM" | shasum -a 256 | cut -c1-64)
expect "buy page shows the price" "$(curl -s "$BASE/buy/?claim=$CLAIM")" "$CLAIM_HASH"
expect "claim waits for payment" "$(post claim "{\"claim\":\"$CLAIM\"}")" '"status":"pending"'

expect "unsigned notification refused" "$(curl -s -X POST $BASE/api/paddle -d '{}')" '"error":"signature"'
TXN='{"event_id":"evt_1","event_type":"transaction.completed","occurred_at":"2026-09-29T12:00:00Z","data":{"id":"txn_1","customer_id":"ctm_1","currency_code":"USD","items":[{"price":{"id":"pri_launch"}}],"details":{"totals":{"grand_total":"699"}},"custom_data":{"claim":"'$CLAIM_HASH'"}}}'
expect "payment makes a license" "$(paddle "$TXN")" '"status":"ok"'
expect "retried notification ignored" "$(paddle "$TXN")" 'already_processed'
expect "key emailed" "$(cat bondi/mail.log)" 'BONDI-[0-9A-Z]\{5\}-'
KEY=$(grep -o 'BONDI-[0-9A-Z-]*' bondi/mail.log | head -1)
CLAIMED=$(post claim "{\"claim\":\"$CLAIM\"}")
expect "claim activates this Mac" "$CLAIMED" '"status":"active".*"token"'
TOKEN=$(echo "$CLAIMED" | sed 's/.*"token":"\([a-f0-9]*\)".*/\1/')
expect "claim works only once" "$(post claim "{\"claim\":\"$CLAIM\"}")" '"status":"claimed"'
expect "offer counts the sale" "$(curl -s $BASE/api/offer)" '"launch_remaining":249'
expect "check says active" "$(post check "{\"token\":\"$TOKEN\",\"device_hash\":\"$DEV\"}")" '"status":"active"'

LOWER=$(echo "$KEY" | tr 'A-Z' 'a-z' | sed 's/-/ /g')
expect "second Mac refused (one per license)" "$(post activate "{\"key\":\"$LOWER\",\"device_hash\":\"$DEV2\",\"device_name\":\"Other Mac\"}")" '"error":"in_use".*Test MacBook'
expect "deactivate frees it" "$(post deactivate "{\"token\":\"$TOKEN\"}")" '"deactivated"'
expect "check after deactivate" "$(post check "{\"token\":\"$TOKEN\",\"device_hash\":\"$DEV\"}")" '"status":"deactivated"'
ACT2=$(post activate "{\"key\":\"$LOWER\",\"device_hash\":\"$DEV2\",\"device_name\":\"Other Mac\"}")
expect "pasted key with spaces works on the new Mac" "$ACT2" '"status":"active"'
TOKEN2=$(echo "$ACT2" | sed 's/.*"token":"\([a-f0-9]*\)".*/\1/')
expect "wrong key refused" "$(post activate "{\"key\":\"BONDI-00000-00000-00000-00000\",\"device_hash\":\"$DEV2\"}")" 'invalid_key'

expect "recover answers the same for unknown emails" "$(post recover '{"email":"nobody@example.com"}')" '"status":"sent"'
post recover '{"email":"test+ctm_1@example.com"}' >/dev/null
NEWKEY=$(grep -o 'BONDI-[0-9A-Z-]*' bondi/mail.log | tail -1)
expect "recovery emails a different key" "$NEWKEY" "BONDI-"; [ "$NEWKEY" != "$KEY" ] && echo "ok   new key differs" || { echo "FAIL key not rotated"; FAILS=$((FAILS+1)); }
expect "old key stops working" "$(post activate "{\"key\":\"$KEY\",\"device_hash\":\"$DEV\"}")" 'invalid_key'
expect "activated Mac keeps working after recovery" "$(post check "{\"token\":\"$TOKEN2\",\"device_hash\":\"$DEV2\"}")" '"status":"active"'

REFUND='{"event_id":"evt_2","event_type":"adjustment.updated","data":{"action":"refund","status":"approved","type":"full","transaction_id":"txn_1"}}'
expect "refund notification" "$(paddle "$REFUND")" '"status":"ok"'
expect "refunded license locks" "$(post check "{\"token\":\"$TOKEN2\",\"device_hash\":\"$DEV2\"}")" '"status":"refunded"'
expect "refund reopens a launch slot" "$(curl -s $BASE/api/offer)" '"launch_remaining":250'
UNKNOWN='{"event_id":"evt_3","event_type":"transaction.completed","data":{"id":"txn_2","customer_id":"ctm_2","items":[{"price":{"id":"pri_mystery"}}]}}'
expect "unknown price kept for retry" "$(paddle "$UNKNOWN")" '"error":"processing"'

# Website sign-ups
expect "sign-up saved" "$(post signup '{"name":"Ada Lovelace","email":"Ada@Example.com","time_zone":"America/Toronto"}')" '"status":"ok"'
expect "sign-up needs a name" "$(post signup '{"name":"  ","email":"x@example.com"}')" '"error":"name"'
expect "sign-up needs a real email" "$(post signup '{"name":"X","email":"not-an-email"}')" '"error":"email"'
expect "bots filling the hidden field are ignored" "$(post signup '{"name":"Bot","email":"bot@example.com","website":"spam"}')" '"status":"ok"'
curl -s -X POST "$BASE/api/signup" -H 'Content-Type: application/json' -H 'CF-IPCountry: DE' -d '{"name":"Grace","email":"grace@example.com","time_zone":"Asia/Calcutta"}' >/dev/null
post signup '{"name":"Ada L.","email":"ada@example.com","time_zone":"Asia/Calcutta"}' >/dev/null
ROWS=$(mysql -uroot --socket=$SOCK bondi -N -e "select concat_ws('|', email, name, ip, ifnull(country,'-'), ifnull(country_source,'-')) from signups order by email")
expect "one row per email, name updated, email lowercased" "$ROWS" 'ada@example.com|Ada L.|127.0.0.1|IN|timezone'
expect "country from the CDN header wins over the time zone" "$ROWS" 'grace@example.com|Grace|127.0.0.1|DE|ip'
[ "$(echo "$ROWS" | wc -l | tr -d ' ')" = "2" ] && { PASS=$((PASS+1)); echo "ok   bot not saved"; } || { FAILS=$((FAILS+1)); echo "FAIL rows: $ROWS"; }

# Admin page
J=$T/cookies
expect "admin needs a password" "$(curl -s -c $J -b $J $BASE/admin/)" 'Sign in'
expect "wrong password refused" "$(curl -s -c $J -b $J -d 'do=login&username=owner&password=nope' $BASE/admin/)" 'Wrong username or password'
expect "wrong username refused" "$(curl -s -c $J -b $J -d 'do=login&username=someone&password=admin-test' $BASE/admin/)" 'Wrong username or password'
curl -s -c $J -b $J -d 'do=login&username=owner&password=admin-test' $BASE/admin/ -o /dev/null
DASH=$(curl -s -c $J -b $J $BASE/admin/)
expect "admin dashboard" "$DASH" 'Launch licenses sold'
expect "admin shows the failed notification" "$DASH" 'not processed'
CSRF=$(echo "$DASH" | grep -o 'name="csrf" value="[a-f0-9]*"' | head -1 | sed 's/.*value="//;s/"//')
expect "admin search by email" "$(curl -s -c $J -b $J "$BASE/admin/?q=ctm_1")" 'test+ctm_1@example.com'
expect "admin refuses a forged form" "$(curl -s -c $J -b $J -L -d "do=restore&license=1&csrf=wrong" $BASE/admin/)" 'form expired'
expect "admin restores" "$(curl -s -c $J -b $J -L -d "do=restore&license=1&csrf=$CSRF" $BASE/admin/)" 'License #1 restored'
expect "restored license works" "$(post check "{\"token\":\"$TOKEN2\",\"device_hash\":\"$DEV2\"}")" '"status":"active"'
expect "admin revokes" "$(curl -s -c $J -b $J -L -d "do=revoke&license=1&csrf=$CSRF" $BASE/admin/)" 'License #1 revoked'
expect "revoked license locks" "$(post check "{\"token\":\"$TOKEN2\",\"device_hash\":\"$DEV2\"}")" '"status":"revoked"'
COMP=$(curl -s -c $J -b $J -L -d "do=comp&email=friend@example.com&send=1&csrf=$CSRF" $BASE/admin/)
expect "admin gives a free license" "$COMP" 'New key (shown once)'
expect "free license emailed" "$(cat bondi/mail.log)" 'To: friend@example.com'
COMPID=$(mysql -uroot --socket=$SOCK bondi -N -e "select l.id from licenses l join customers c on c.id=l.customer_id where c.email='friend@example.com'")
expect "licenses list has a revoke button per row" "$(curl -s -c $J -b $J $BASE/admin/)" 'value="revoke"'
LISTED=$(curl -s -c $J -b $J -L -d "do=revoke&license=$COMPID&back=list&csrf=$CSRF" $BASE/admin/)
expect "revoke from the list stays on the list" "$LISTED" "License #$COMPID revoked.*Latest licenses"
expect "revoked row offers restore" "$LISTED" 'value="restore"'

expect "admin lists sign-ups" "$(curl -s -c $J -b $J "$BASE/admin/?signups")" 'grace@example.com'
expect "admin sign-ups by country" "$(curl -s -c $J -b $J "$BASE/admin/?signups")" 'By country: .*Germany 1'
expect "admin sign-ups CSV" "$(curl -s -c $J -b $J "$BASE/admin/?signups&csv")" '"Ada L.",ada@example.com,IN,India,timezone,Asia/Calcutta,127.0.0.1'
NEWS="subject=Beta%20for%20{name}&message=Hi%20{name},%0A%0ASee%20https://trybondi.app&q="
expect "sign-ups page can write to everyone" "$(curl -s -c $J -b $J "$BASE/admin/?signups")" 'Write to everyone on the list'
expect "test email goes to support" "$(curl -s -c $J -b $J -L -d "do=news_test&$NEWS&csrf=$CSRF" $BASE/admin/)" 'Test sent to support@example.com'
expect "test email is marked" "$(cat bondi/mail.log)" 'Subject: \[Test\] Beta for'
expect "update emails sent" "$(curl -s -c $J -b $J -L -d "do=news_send&$NEWS&csrf=$CSRF" $BASE/admin/)" 'Sent to 2 people'
expect "update email uses their name" "$(cat bondi/mail.log)" 'Subject: Beta for Ada L.'
expect "update email says how to stop" "$(cat bondi/mail.log)" 'reply with "unsubscribe"'
expect "sending again skips who already has it" "$(curl -s -c $J -b $J -L -d "do=news_send&$NEWS&csrf=$CSRF" $BASE/admin/)" 'Sent to 0 people'
GRACE=$(mysql -uroot --socket=$SOCK bondi -N -e "select id from signups where email='grace@example.com'")
expect "admin removes a sign-up" "$(curl -s -c $J -b $J -L -d "do=delete_signup&signup=$GRACE&csrf=$CSRF" $BASE/admin/)" 'Removed grace@example.com'

# Country from the visitor's IP address, looked up in bondi/ip-country-*.bin
LOOKUPS=$(php -r 'function config($p, $d = null) { return $d; } require "bondi/lib/signups.php";
  foreach (["8.8.8.8", "81.2.69.160", "2001:4860:4860::8888", "::ffff:8.8.8.8", "127.0.0.1", "10.1.2.3", "::1", "nonsense"] as $ip) echo $ip, "=", ip_country($ip) ?? "none", " ";')
expect "IP lookups (v4, v6, mapped, private)" "$LOOKUPS" '8.8.8.8=US 81.2.69.160=GB 2001:4860:4860::8888=US ::ffff:8.8.8.8=US 127.0.0.1=none 10.1.2.3=none ::1=none nonsense=none'
curl -s -X POST "$BASE/api/signup" -H 'Content-Type: application/json' -H 'X-Test-IP: 81.2.69.160' -d '{"name":"Brit","email":"brit@example.com","time_zone":"Asia/Tokyo"}' >/dev/null
expect "sign-up country comes from the IP before the time zone" "$(mysql -uroot --socket=$SOCK bondi -N -e "select concat_ws('|', ip, country, country_source) from signups where email='brit@example.com'")" '81.2.69.160|GB|ip'
mysql -uroot --socket=$SOCK bondi -e "insert into signups (email, name, ip, country, country_source, time_zone) values ('old@example.com', 'Old', '8.8.8.8', 'JP', 'timezone', 'Asia/Tokyo')"
expect "admin shows country names" "$(curl -s -c $J -b $J "$BASE/admin/?signups")" 'United Kingdom'
expect "older sign-ups get their IP's country" "$(mysql -uroot --socket=$SOCK bondi -N -e "select concat_ws('|', country, country_source) from signups where email='old@example.com'")" 'US|ip'

if [ -n "${SNAP:-}" ]; then
  curl -s -c $J -b $J $BASE/admin/ > "$SNAP/admin-licenses.html"
  curl -s -c $J -b $J "$BASE/admin/?signups" > "$SNAP/admin-signups.html"
fi
grep -iE "fatal|warning|deprecated" $T/php.log | head -5
echo "$PASS passed, $FAILS failed"
exit $FAILS
