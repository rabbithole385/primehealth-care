PRIME CARE HEALTH SOLUTIONS — website (PHP 8 + SQLite, no framework)
Runs on Railway (Docker) AND on cPanel/shared hosting from the same files.

============ DEPLOY ON RAILWAY ============
1. Put these files in a GitHub repo (files at the repo root), then in Railway:
   New Project → Deploy from GitHub repo.  (Or from this folder run:  railway up)
   Railway finds the Dockerfile + railway.json automatically.
2. ATTACH A VOLUME (important): service → right-click → Attach Volume → mount path:  /data
   Without it, appointments, messages and your admin login are wiped on every redeploy.
3. Settings → Networking → Generate Domain (or add primecarehealthsolutions.online as a custom domain
   and add the CNAME record Railway shows you at your DNS provider).
4. Variables (service → Variables):
     SITE_URL        https://www.primecarehealthsolutions.online
     SITE_EMAIL      inbox that receives form notifications (comma-separate several)
   Email — Railway blocks SMTP on Free/Trial/Hobby plans, so use Resend (HTTPS API):
     RESEND_API_KEY  re_xxxxxxxx           (resend.com → API Keys; verify your domain there)
     MAIL_FROM       no-reply@primecarehealthsolutions.online
   Pro plan only, if you prefer SMTP instead:
     MAIL_DRIVER=smtp  SMTP_HOST  SMTP_PORT(587 or 465)  SMTP_USER  SMTP_PASS  MAIL_FROM
5. Open /admin/ → create your admin account → Settings → "Send test email".
Health check: /health.php

============ DEPLOY ON cPANEL (Namecheap) ============
Upload the zip to public_html → Extract → visit /admin/. Email uses PHP mail().
(Dockerfile, railway.json and docker-entrypoint.sh are simply ignored there.)
Edit defaults in includes/config.php. Uncomment the HTTPS lines in .htaccess once SSL is on.

PAGES: Home, About, Our Founder, Services, Locations, Contact, Book Appointment (3-step), 404
ADMIN: /admin/ — appointments (search/filter/status/CSV), messages, subscribers, password, test email
LOGO FILES: assets/img/logo-icon.png, logo-full.png, logo-full-light.png, favicon.png
FOUNDER PHOTO: add assets/img/founder.jpg and swap the "IW" monogram in founder.php / index.php
Powered by DWO — digitalweboracleict.com
