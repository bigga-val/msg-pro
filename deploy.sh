#!/bin/bash
set -e

USERNAME="cp2543655p59"
REPO="https://github.com/bigga-val/msg-pro.git"
APP_DIR="/home/$USERNAME/rapide-app"
DB_NAME="${USERNAME}_rapide"
DB_USER="${USERNAME}_ruser"
DB_PASS=$(openssl rand -base64 12 | tr -dc 'a-zA-Z0-9' | head -c 16)

echo ">>> 1. Clone du repo..."
if [ -d "$APP_DIR/.git" ]; then
  cd $APP_DIR && git pull origin main
else
  git clone $REPO $APP_DIR
fi

echo ">>> 2. Creation BDD..."
uapi Mysql create_database name=$DB_NAME 2>&1 | grep -E "result|error" || true
uapi Mysql create_user name=$DB_USER password="$DB_PASS" 2>&1 | grep -E "result|error" || true
uapi Mysql set_privileges_on_database user=$DB_USER database=$DB_NAME privileges=ALL%20PRIVILEGES 2>&1 | grep -E "result|error" || true

echo ">>> 3. Configuration .env.local..."
cat > $APP_DIR/.env.local << ENVEOF
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=$(openssl rand -hex 16)
DATABASE_URL="mysql://${DB_USER}:${DB_PASS}@127.0.0.1:3306/${DB_NAME}?serverVersion=mariadb-10.4.10"
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
RECAPTCHA3_KEY=6LfUJ0UsAAAAAN6V75teV6YuXPCS2cfe7GR7xeg7
RECAPTCHA3_SECRET=6LfUJ0UsAAAAANb0M_NqBKOMAvvwJ52dzjQfFvN6
SMS_API_USERNAME=insoftwaresarl
SMS_API_PASSWORD=yelQamHM7rpf
MAILER_HOST=mail.msg-pro.com
MAILER_USERNAME=info@msg-pro.com
MAILER_PASSWORD=Insoft@123
ENVEOF

echo ">>> 4. Composer install..."
cd $APP_DIR
php /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction

echo ">>> 5. Cache et migrations..."
php bin/console cache:clear --env=prod --no-interaction
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=prod
php bin/console assets:install public --env=prod --no-interaction

echo ">>> 6. Document root vers public/..."
uapi SubDomain addsubdomain domain=test rootdomain=msg-pro.com dir=rapide-app/public 2>&1 | grep -E "result|error" || true

echo ">>> 7. Permissions..."
chmod -R 755 $APP_DIR/var $APP_DIR/public

echo ""
echo "=============================="
echo "  DEPLOYMENT TERMINE !"
echo "=============================="
echo "  DB  : $DB_NAME"
echo "  User: $DB_USER"
echo "  Pass: $DB_PASS"
echo "=============================="
