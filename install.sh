#!/bin/bash
# ============================================================
# Script Instalasi Otomatis - Keuangan Mushola
# ============================================================
# Jalankan: bash install.sh
# ============================================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}"
echo "╔════════════════════════════════════════╗"
echo "║   🕌 Keuangan Mushola - Installer      ║"
echo "╚════════════════════════════════════════╝"
echo -e "${NC}"

# ── 1. Cek requirements ────────────────────────────────────
echo -e "${YELLOW}▶ Memeriksa requirements...${NC}"

if ! command -v php &> /dev/null; then
    echo -e "${RED}✗ PHP tidak ditemukan. Install PHP 8.2+${NC}"; exit 1
fi

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo -e "${GREEN}✓ PHP $PHP_VERSION${NC}"

if ! command -v composer &> /dev/null; then
    echo -e "${RED}✗ Composer tidak ditemukan. Install dari https://getcomposer.org${NC}"; exit 1
fi
echo -e "${GREEN}✓ Composer ditemukan${NC}"

if ! command -v mysql &> /dev/null && ! command -v mysqladmin &> /dev/null; then
    echo -e "${YELLOW}⚠ MySQL tidak ditemukan di PATH. Pastikan MySQL berjalan.${NC}"
else
    echo -e "${GREEN}✓ MySQL ditemukan${NC}"
fi

# ── 2. Install PHP dependencies ────────────────────────────
echo -e "\n${YELLOW}▶ Menginstall PHP dependencies...${NC}"
composer install --no-interaction --prefer-dist --optimize-autoloader

# ── 3. Setup .env ──────────────────────────────────────────
echo -e "\n${YELLOW}▶ Menyiapkan file .env...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ File .env dibuat dari .env.example${NC}"
else
    echo -e "${YELLOW}⚠ File .env sudah ada, tidak ditimpa${NC}"
fi

# ── 4. Generate app key ────────────────────────────────────
echo -e "\n${YELLOW}▶ Generating application key...${NC}"
php artisan key:generate --ansi

# ── 5. Prompt database setup ──────────────────────────────
echo -e "\n${YELLOW}▶ Konfigurasi Database${NC}"
echo -e "Masukkan konfigurasi database MySQL:"
read -p "Host [127.0.0.1]: " DB_HOST
DB_HOST=${DB_HOST:-127.0.0.1}
read -p "Port [3306]: " DB_PORT
DB_PORT=${DB_PORT:-3306}
read -p "Database [mushola_keuangan]: " DB_DATABASE
DB_DATABASE=${DB_DATABASE:-mushola_keuangan}
read -p "Username [root]: " DB_USERNAME
DB_USERNAME=${DB_USERNAME:-root}
read -s -p "Password: " DB_PASSWORD
echo

# Update .env
sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
sed -i "s/DB_PORT=.*/DB_PORT=$DB_PORT/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env

# ── 6. Create database ────────────────────────────────────
echo -e "\n${YELLOW}▶ Membuat database...${NC}"
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" ${DB_PASSWORD:+-p"$DB_PASSWORD"} \
    -e "CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null \
    && echo -e "${GREEN}✓ Database '$DB_DATABASE' siap${NC}" \
    || echo -e "${YELLOW}⚠ Tidak bisa membuat database otomatis. Buat manual: CREATE DATABASE $DB_DATABASE;${NC}"

# ── 7. Run migrations & seed ─────────────────────────────
echo -e "\n${YELLOW}▶ Menjalankan migrasi & seeder...${NC}"
php artisan migrate --seed --force
echo -e "${GREEN}✓ Migrasi dan data awal berhasil${NC}"

# ── 8. Storage link ───────────────────────────────────────
echo -e "\n${YELLOW}▶ Membuat symlink storage...${NC}"
php artisan storage:link
echo -e "${GREEN}✓ Storage link dibuat${NC}"

# ── 9. Setup Google OAuth reminder ───────────────────────
echo -e "\n${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "  ⚙️  LANGKAH SELANJUTNYA (WAJIB)"
echo -e "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${YELLOW}1. Setup Google OAuth:${NC}"
echo "   • Buka https://console.cloud.google.com/"
echo "   • Buat project → APIs & Services → Credentials"
echo "   • Buat OAuth 2.0 Client ID (Web Application)"
echo "   • Tambahkan Authorized redirect URI:"
echo "     http://localhost:8000/auth/google/callback"
echo "   • Salin Client ID & Secret ke file .env:"
echo "     GOOGLE_CLIENT_ID=..."
echo "     GOOGLE_CLIENT_SECRET=..."
echo ""
echo -e "${YELLOW}2. Jalankan aplikasi:${NC}"
echo "   php artisan serve"
echo ""
echo -e "${YELLOW}3. Buka browser: http://localhost:8000${NC}"
echo "   Login dengan Google, lalu jalankan:"
echo ""
echo -e "${YELLOW}4. Set diri Anda sebagai Admin:${NC}"
echo "   php artisan mushola:set-admin your@email.com"
echo ""
echo -e "${GREEN}✅ Instalasi selesai!${NC}"
echo ""
