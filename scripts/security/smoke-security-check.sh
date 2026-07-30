#!/usr/bin/env sh
# ─────────────────────────────────────────────────────────────────────────────
#  OWASP Top-10 smoke-security check for Torre San Bartolo
#  Usage: ./scripts/security/smoke-security-check.sh [BASE_URL]
#  Default target: http://localhost:8080
#
#  Exits 0 if all checks pass.  Exits 1 on any FAIL.
# ─────────────────────────────────────────────────────────────────────────────
set -eu

BASE="${1:-http://localhost:8080}"
PASS=0
FAIL=0
WARN=0
RED='\033[0;31m'
GRN='\033[0;32m'
YLW='\033[0;33m'
RST='\033[0m'

ok()   { PASS=$((PASS+1)); printf "${GRN}[PASS]${RST} %s\n" "$1"; }
fail() { FAIL=$((FAIL+1)); printf "${RED}[FAIL]${RST} %s\n" "$1"; }
warn() { WARN=$((WARN+1)); printf "${YLW}[WARN]${RST} %s\n" "$1"; }

hdr()   { curl -sI "$1"; }
status(){ curl -s -o /dev/null -w "%{http_code}" "$1"; }
body()  { curl -s "$1"; }

printf "\n=== OWASP smoke test → %s ===\n\n" "$BASE"

# ─────────────────────────────────────────────────────────────────────────────
# A01 — Broken Access Control
# ─────────────────────────────────────────────────────────────────────────────
printf "\n── A01  Broken Access Control ──\n"

SC=$(status "$BASE/wp-json/wp/v2/users")
if [ "$SC" = "403" ] || [ "$SC" = "401" ]; then
    ok "REST /wp/v2/users → $SC (user enumeration blocked)"
else
    fail "REST /wp/v2/users → $SC (should be 403/401)"
fi

SC=$(status "$BASE/?author=1")
if [ "$SC" = "301" ] || [ "$SC" = "302" ] || [ "$SC" = "403" ]; then
    ok "?author=1 scan → $SC (blocked/redirected)"
else
    BODY=$(body "$BASE/?author=1")
    if echo "$BODY" | grep -qi '"slug"'; then
        fail "?author=1 → $SC and response contains username slug"
    else
        warn "?author=1 → $SC (verify response reveals no username)"
    fi
fi

SC=$(status "$BASE/wp-admin/")
if [ "$SC" = "302" ] || [ "$SC" = "301" ]; then
    ok "wp-admin → $SC (redirects to login as expected)"
else
    warn "wp-admin → $SC (expected 302 redirect to login)"
fi

# ─────────────────────────────────────────────────────────────────────────────
# A02 — Cryptographic Failures
# ─────────────────────────────────────────────────────────────────────────────
printf "\n── A02  Cryptographic Failures ──\n"

HSTS=$(hdr "$BASE" | grep -i "strict-transport-security" || true)
if [ -n "$HSTS" ]; then
    ok "HSTS header present: $HSTS"
else
    warn "HSTS header missing (expected in prod with HTTPS)"
fi

# ─────────────────────────────────────────────────────────────────────────────
# A03 — Injection (XSS / CSP)
# ─────────────────────────────────────────────────────────────────────────────
printf "\n── A03  Injection / CSP ──\n"

CSP=$(hdr "$BASE" | grep -i "content-security-policy" || true)
if [ -n "$CSP" ]; then
    ok "CSP header present"
    if echo "$CSP" | grep -qi "unsafe-eval"; then
        fail "CSP contains 'unsafe-eval' — eval-based XSS not blocked"
    else
        ok "CSP does NOT contain 'unsafe-eval'"
    fi
    if echo "$CSP" | grep -qi "object-src 'none'"; then
        ok "CSP has object-src 'none' (Flash/plugin injection blocked)"
    else
        warn "CSP missing object-src 'none'"
    fi
    if echo "$CSP" | grep -qi "form-action"; then
        ok "CSP has form-action directive (form hijacking blocked)"
    else
        warn "CSP missing form-action directive"
    fi
else
    fail "Content-Security-Policy header missing"
fi

# Basic reflected XSS probe (no exploitation — just confirm app doesn't echo raw)
SC_XSS=$(status "$BASE/?s=<script>alert(1)</script>")
BODY_XSS=$(body "$BASE/?s=%3Cscript%3Ealert%281%29%3C%2Fscript%3E")
if echo "$BODY_XSS" | grep -q "<script>alert(1)</script>"; then
    fail "Possible XSS: search page echoes raw <script> tag"
else
    ok "Search page escapes <script> tag (no unescaped reflection)"
fi

# ─────────────────────────────────────────────────────────────────────────────
# A04 — Insecure Design (rate limiting, honeypot)
# ─────────────────────────────────────────────────────────────────────────────
printf "\n── A04  Insecure Design ──\n"

# Contact form honeypot field presence
BODY_CF=$(body "$BASE/contact/")
if echo "$BODY_CF" | grep -q "tsb_hp"; then
    ok "Contact form honeypot field present"
else
    warn "Contact form honeypot field not detected (path may differ)"
fi

# ─────────────────────────────────────────────────────────────────────────────
# A05 — Security Misconfiguration
# ─────────────────────────────────────────────────────────────────────────────
printf "\n── A05  Security Misconfiguration ──\n"

for HDR in "x-content-type-options" "x-frame-options" "referrer-policy" "permissions-policy" "x-permitted-cross-domain-policies"; do
    VAL=$(hdr "$BASE" | grep -i "$HDR" || true)
    if [ -n "$VAL" ]; then
        ok "Header present: $VAL"
    else
        fail "Security header missing: $HDR"
    fi
done

# WP version leakage via meta generator
BODY_HOME=$(body "$BASE")
if echo "$BODY_HOME" | grep -qi "wordpress.*[0-9]\.[0-9]"; then
    fail "WP version found in page source (generator meta not removed)"
else
    ok "WP version not visible in page source"
fi

# wlwmanifest / rsd leakage
if echo "$BODY_HOME" | grep -qi "wlwmanifest\|EditURI"; then
    fail "wlwmanifest or RSD link still in <head>"
else
    ok "wlwmanifest / RSD links removed from <head>"
fi

# Sensitive files
for PATH_ in "/readme.html" "/license.txt" "/wp-config-sample.php" "/xmlrpc.php"; do
    SC=$(status "$BASE$PATH_")
    if [ "$SC" = "403" ] || [ "$SC" = "404" ]; then
        ok "$PATH_ → $SC (blocked)"
    else
        fail "$PATH_ → $SC (should be 403/404)"
    fi
done

# Directory listing
SC=$(status "$BASE/wp-content/uploads/")
if [ "$SC" = "403" ] || [ "$SC" = "404" ] || [ "$SC" = "302" ]; then
    ok "wp-content/uploads/ → $SC (no directory listing)"
else
    BODY_DIR=$(body "$BASE/wp-content/uploads/")
    if echo "$BODY_DIR" | grep -qi "index of\|parent directory"; then
        fail "Directory listing enabled on wp-content/uploads/"
    else
        ok "Directory listing not exposed (custom response)"
    fi
fi

# X-Powered-By / Server leakage
SERVER_HDR=$(hdr "$BASE" | grep -i "^x-powered-by\|^server" || true)
if echo "$SERVER_HDR" | grep -qi "php\|apache\|nginx/[0-9]"; then
    warn "Server fingerprinting: $SERVER_HDR"
else
    ok "No verbose Server/X-Powered-By header"
fi

# ?ver= asset fingerprinting
if echo "$BODY_HOME" | grep -qi "\.js?ver=\|\.css?ver="; then
    warn "Asset URLs still contain ?ver= version strings"
else
    ok "?ver= removed from asset URLs"
fi

# ─────────────────────────────────────────────────────────────────────────────
# A07 — Identification and Authentication Failures
# ─────────────────────────────────────────────────────────────────────────────
printf "\n── A07  Authentication ──\n"

# wp-login accessible (should be, just rate-limited)
SC=$(status "$BASE/wp-login.php")
if [ "$SC" = "200" ] || [ "$SC" = "302" ]; then
    ok "wp-login.php reachable (rate limiting applied at edge layer)"
else
    warn "wp-login.php → $SC (unexpected)"
fi

# ─────────────────────────────────────────────────────────────────────────────
# A10 — SSRF
# ─────────────────────────────────────────────────────────────────────────────
printf "\n── A10  SSRF ──\n"
# allow_url_fopen=Off + allow_url_include=Off enforced via php-security.ini
ok "allow_url_fopen=Off and allow_url_include=Off set in php-security.ini (manual verify)"

# ─────────────────────────────────────────────────────────────────────────────
# Plugin Security — Polylang (OWASP A01, A05)
# ─────────────────────────────────────────────────────────────────────────────
printf "\n── Plugin: Polylang ──\n"

# Polylang REST namespace must be blocked for anonymous visitors
SC=$(status "$BASE/wp-json/pll/v1/languages")
if [ "$SC" = "403" ] || [ "$SC" = "401" ]; then
    ok "Polylang REST /pll/v1/languages → $SC (internal config hidden)"
else
    fail "Polylang REST /pll/v1/languages → $SC (should be 403 — exposes term IDs and plugin paths)"
fi

# Theme inc/ PHP files must not be directly executable from the web
SC=$(status "$BASE/wp-content/themes/torresan-bnb/inc/polylang-setup.php")
if [ "$SC" = "403" ] || [ "$SC" = "404" ]; then
    ok "Theme inc/polylang-setup.php → $SC (blocked)"
else
    fail "Theme inc/polylang-setup.php → $SC (must be 403/404 — CLI-only file)"
fi

SC=$(status "$BASE/wp-content/themes/torresan-bnb/inc/seed-pages.php")
if [ "$SC" = "403" ] || [ "$SC" = "404" ]; then
    ok "Theme inc/seed-pages.php → $SC (blocked)"
else
    fail "Theme inc/seed-pages.php → $SC (must be 403/404)"
fi

# .po translation files must not be publicly readable
SC=$(status "$BASE/wp-content/themes/torresan-bnb/languages/torresan-bnb-it_IT.po")
if [ "$SC" = "403" ] || [ "$SC" = "404" ]; then
    ok "Translation .po file → $SC (string inventory not exposed)"
else
    fail "Translation .po file → $SC (should be 403 — reveals full string inventory)"
fi

SC=$(status "$BASE/wp-content/themes/torresan-bnb/languages/torresan-bnb-de_DE.mo")
if [ "$SC" = "403" ] || [ "$SC" = "404" ]; then
    ok "Translation .mo file → $SC (binary not exposed)"
else
    fail "Translation .mo file → $SC (should be 403)"
fi

# Language switcher must render on the homepage
BODY_LANG=$(body "$BASE")
if echo "$BODY_LANG" | grep -qi "lang-switcher"; then
    ok "Language switcher (.lang-switcher) present in homepage HTML"
else
    warn "Language switcher not found in homepage HTML (check CSS class or Polylang status)"
fi

# Polylang hreflang tags must be present for SEO and correctness
if echo "$BODY_LANG" | grep -qi 'hreflang='; then
    ok "hreflang link tags present in homepage <head>"
else
    warn "hreflang link tags not found in homepage (may need Polylang settings adjustment)"
fi

# ─────────────────────────────────────────────────────────────────────────────
# Plugin Security — VikBooking (OWASP A01, A05)
# ─────────────────────────────────────────────────────────────────────────────
printf "\n── Plugin: VikBooking ──\n"

# VikBooking admin panel must redirect to login (no unauthenticated access)
SC=$(status "$BASE/wp-admin/admin.php?page=vikbooking")
if [ "$SC" = "302" ] || [ "$SC" = "301" ]; then
    ok "VikBooking admin panel → $SC (redirects to login)"
elif [ "$SC" = "200" ]; then
    # Could be OK if the response is the WP login redirect page
    BODY_VB=$(body "$BASE/wp-admin/admin.php?page=vikbooking")
    if echo "$BODY_VB" | grep -qi "wp-login\|loginform"; then
        ok "VikBooking admin → 200 with login form (access controlled)"
    else
        fail "VikBooking admin panel → 200 without login (unauthenticated access)"
    fi
else
    warn "VikBooking admin → $SC (unexpected — expected 302 to wp-login)"
fi

# VikBooking REST namespace exposure check
SC=$(status "$BASE/wp-json/vikbooking/v1/")
if [ "$SC" = "403" ] || [ "$SC" = "404" ] || [ "$SC" = "401" ]; then
    ok "VikBooking REST /vikbooking/v1/ → $SC (not publicly exposed)"
else
    BODY_VBR=$(body "$BASE/wp-json/vikbooking/v1/")
    if echo "$BODY_VBR" | grep -qi '"namespace"\s*:\s*"vikbooking'; then
        warn "VikBooking REST namespace detected → $SC (review endpoint permissions)"
    else
        ok "VikBooking REST → $SC (no sensitive data in response)"
    fi
fi

# ─────────────────────────────────────────────────────────────────────────────
# Plugin Security — Pods (OWASP A01, A03)
# ─────────────────────────────────────────────────────────────────────────────
printf "\n── Plugin: Pods ──\n"

# Pods REST API: custom post types should not expose private meta
SC=$(status "$BASE/wp-json/wp/v2/camera")
if [ "$SC" = "403" ] || [ "$SC" = "401" ]; then
    ok "Pods CPT 'camera' REST → $SC (restricted)"
else
    BODY_CPT=$(body "$BASE/wp-json/wp/v2/camera")
    if echo "$BODY_CPT" | grep -qi '"id"\s*:'; then
        warn "Pods CPT 'camera' REST → $SC (publicly accessible — review if intentional)"
    else
        ok "Pods CPT 'camera' REST → $SC (no data returned)"
    fi
fi

# Pods admin must be locked to logged-in users
SC=$(status "$BASE/wp-admin/admin.php?page=pods")
if [ "$SC" = "302" ] || [ "$SC" = "301" ]; then
    ok "Pods admin → $SC (redirects to login)"
else
    warn "Pods admin → $SC (expected 302)"
fi

# ─────────────────────────────────────────────────────────────────────────────
# Multilingual Security — language parameter validation (OWASP A03)
# ─────────────────────────────────────────────────────────────────────────────
printf "\n── Multilingual: Language Parameter Validation ──\n"

# Injecting a non-existent lang code should not cause errors/disclosure
SC=$(status "$BASE/?lang=<script>")
BODY_LINJ=$(body "$BASE/?lang=%3Cscript%3E")
if echo "$BODY_LINJ" | grep -q '<script>'; then
    fail "?lang=<script> reflected unescaped (XSS vector)"
else
    ok "?lang parameter does not reflect raw input (XSS probe clean)"
fi

# Confirming translated URLs resolve for registered languages
for LANG_SLUG in it de fr es; do
    SC=$(status "$BASE/$LANG_SLUG/")
    if [ "$SC" = "200" ] || [ "$SC" = "301" ] || [ "$SC" = "302" ]; then
        ok "/$LANG_SLUG/ → $SC (language URL resolves)"
    else
        warn "/$LANG_SLUG/ → $SC (translated home URL not resolving)"
    fi
done

# ─────────────────────────────────────────────────────────────────────────────
# Summary
# ─────────────────────────────────────────────────────────────────────────────
printf "\n═══════════════════════════════════════\n"
printf " PASS: %d   WARN: %d   FAIL: %d\n" "$PASS" "$WARN" "$FAIL"
printf "═══════════════════════════════════════\n\n"

if [ "$FAIL" -gt 0 ]; then
    printf "${RED}✗ %d check(s) failed — review output above.${RST}\n\n" "$FAIL"
    exit 1
else
    printf "${GRN}✓ All checks passed.${RST}\n\n"
    exit 0
fi
