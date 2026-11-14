#!/bin/sh
set -e

# Ensure only prefork MPM is enabled
a2dismod -f mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true
# Railway sets $PORT — make Apache listen on it
PORT="${PORT:-8080}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Expose the app's settings to PHP via Apache (only variables that are set)
VARS=""
for v in SITE_URL SITE_EMAIL MAIL_DRIVER MAIL_FROM RESEND_API_KEY SMTP_HOST SMTP_PORT SMTP_USER SMTP_PASS DATA_DIR RAILWAY_ENVIRONMENT RAILWAY_VOLUME_MOUNT_PATH MYSQLHOST MYSQLUSER MYSQLPASSWORD MYSQLPORT MYSQLDATABASE MYSQL_URL; do
  eval "val=\${$v:-}"
  if [ -n "$val" ]; then VARS="$VARS $v"; fi
done
if [ -n "$VARS" ]; then echo "PassEnv$VARS" > /etc/apache2/conf-enabled/primecare-env.conf; fi

# Volume (mounted as root by Railway) must be writable by Apache
DATA_DIR="${DATA_DIR:-/data}"
mkdir -p "$DATA_DIR"
chown -R www-data:www-data "$DATA_DIR"
chmod 775 "$DATA_DIR"

exec docker-php-entrypoint "$@"
