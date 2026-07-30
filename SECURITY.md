# Security Baseline and Operations

Questo repository applica hardening su piu livelli (Docker, Apache/PHP, WordPress, tema).

## Baseline OWASP-like implementata

- Security headers HTTP lato web server
- Riduzione fingerprinting server/PHP
- XML-RPC disabilitato
- Blocco enumerazione utenti anonimi da REST API
- Endpoint autore ridotto per limitare user enumeration
- Login endpoint protetto in profile `prod` con rate limit Nginx
- Database non pubblicato su porta host

## Runtime checks

Esegui:

```bash
chmod +x scripts/security/smoke-security-check.sh
scripts/security/smoke-security-check.sh http://localhost:8080
```

## Hardening ancora raccomandato in produzione

1. Reverse proxy TLS reale (Let's Encrypt) e redirect automatico HTTP->HTTPS.
2. WAF/CDN con challenge bot su `/wp-login.php` e regole anti-bot.
3. MFA obbligatoria per admin/editor.
4. Backup cifrati giornalieri e test restore mensile.
5. Alerting su login falliti, cambi plugin, cambi utenti admin.
6. Aggiornamenti core/plugin/tema con finestra di patch regolare.

## Scope e limiti

Le verifiche automatiche qui introdotte sono smoke test e non sostituiscono penetration test completo.
