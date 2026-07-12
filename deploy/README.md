# Deploy geolicense-laravel ke DigitalOcean ($6 droplet)

Runbook dari nol → production. Target: **Ubuntu 24.04, 1 GB RAM / 1 vCPU / 25 GB**,
Nginx + PHP-FPM 8.4 + MySQL 8, HTTPS Let's Encrypt, auto-deploy via GitHub Actions.

> Domain sudah terisi (`geolicense.my.id`). Ganti `SERVER_IP` dengan IP droplet
> di semua perintah di bawah. User non-root memakai nama **`alexistdev`**.
> Jalankan berurutan.

Isi folder `deploy/`:

| File | Guna |
|---|---|
| `deploy.sh` | skrip deploy di server (dipanggil CI/CD atau manual) |
| `nginx-geolicense.conf` | server block Nginx |
| `env.production.example` | template `.env` production |
| `mysql-geolicense.cnf` | tuning MySQL hemat RAM |
| `php-fpm-geolicense.conf` | pool PHP-FPM (ondemand, hemat RAM) |
| `../.github/workflows/deploy.yml` | workflow auto-deploy |

---

## 1. Buat droplet

DigitalOcean → **Create → Droplet**:
- Image: **Ubuntu 24.04 (LTS) x64**
- Plan: **Basic → Regular → $6/mo** (1 GB / 1 vCPU / 25 GB)
- Region: **Singapore (SGP1)**
- Authentication: **SSH Key** (upload public key laptopmu — jangan pakai password)
- Hostname: `geolicense`

Catat **SERVER_IP** droplet.

## 2. DNS

Di penyedia domain, buat **A record**: `geolicense.my.id` → `SERVER_IP` (TTL rendah dulu,
mis. 300 s). Tunggu propagasi: `dig +short geolicense.my.id` harus mengembalikan `SERVER_IP`.

## 3. Hardening awal (SSH sebagai root)

```bash
ssh root@SERVER_IP

# --- Swap 2 GB (penting: cegah OOM saat build Vite di RAM 1 GB) ---
fallocate -l 2G /swapfile
chmod 600 /swapfile
mkswap /swapfile
swapon /swapfile
echo '/swapfile none swap sw 0 0' >> /etc/fstab
sysctl -w vm.swappiness=10
echo 'vm.swappiness=10' >> /etc/sysctl.conf

# --- User non-root `alexistdev` ---
adduser --disabled-password --gecos "" alexistdev
usermod -aG sudo alexistdev
passwd alexistdev                                           # WAJIB: set password utk `sudo` (login tetap via key)
rsync --archive --chown=alexistdev:alexistdev ~/.ssh /home/alexistdev   # salin SSH key root ke alexistdev

# --- Update sistem + fail2ban + firewall ---
apt update && apt -y upgrade
apt -y install fail2ban ufw
ufw allow OpenSSH
ufw --force enable            # 'Nginx Full' dibuka setelah Nginx terpasang (step 4)

# --- Nonaktifkan login root & password (uji dulu SSH sebagai alexistdev di terminal lain!) ---
sed -i 's/^#\?PermitRootLogin.*/PermitRootLogin no/' /etc/ssh/sshd_config
sed -i 's/^#\?PasswordAuthentication.*/PasswordAuthentication no/' /etc/ssh/sshd_config
systemctl restart ssh
```

> ⚠️ Sebelum menutup sesi root, **buka terminal baru** dan pastikan
> `ssh alexistdev@SERVER_IP` berhasil **dan** `sudo whoami` mencetak `root`.
> Setelah ini semua langkah sebagai `alexistdev`. (Akses root darurat: DigitalOcean web console.)

## 4. Install stack (sebagai `alexistdev`, pakai `sudo`)

```bash
ssh alexistdev@SERVER_IP

# PHP 8.4 via PPA ondrej
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt -y install php8.4-fpm php8.4-mysql php8.4-mbstring php8.4-xml \
  php8.4-curl php8.4-zip php8.4-bcmath php8.4-gd php8.4-intl php8.4-opcache \
  nginx mysql-server git unzip curl

# 'Nginx Full' (80 + 443)
sudo ufw allow 'Nginx Full'

# Node.js 22 LTS
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt -y install nodejs

# Composer
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm /tmp/composer-setup.php

# Certbot
sudo apt -y install certbot python3-certbot-nginx

# Cek versi
php -v && node -v && composer --version && nginx -v
```

## 5. MySQL

```bash
sudo mysql_secure_installation   # set root password, hapus anon/testdb, dsb

# Buat database + user (ganti STRONG_PASSWORD dengan password kuat)
sudo mysql <<'SQL'
CREATE DATABASE geolicense_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'geolicense'@'127.0.0.1' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON geolicense_laravel.* TO 'geolicense'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL
```

Tuning memori MySQL dipasang setelah repo di-clone (step 7), atau langsung:
`sudo cp /var/www/geolicense/deploy/mysql-geolicense.cnf /etc/mysql/mysql.conf.d/geolicense.cnf && sudo systemctl restart mysql`

## 6. Deploy key server → GitHub (agar `git pull` repo privat jalan)

```bash
# Sebagai alexistdev, buat key khusus untuk menarik repo
ssh-keygen -t ed25519 -C "geolicense-vps deploy key" -f ~/.ssh/id_ed25519 -N ""
cat ~/.ssh/id_ed25519.pub
```
Salin output → GitHub repo **`geolicense-laravel` → Settings → Deploy keys → Add
deploy key** (centang *Allow write access* TIDAK perlu — cukup read).
Uji: `ssh -T git@github.com` (ketik `yes`).

## 7. First deploy (manual, sekali)

```bash
sudo mkdir -p /var/www/geolicense
sudo chown alexistdev:alexistdev /var/www/geolicense
git clone git@github.com:alexistdev/geolicense-laravel.git /var/www/geolicense
cd /var/www/geolicense

# .env production
cp deploy/env.production.example .env
nano .env
#   - DB_PASSWORD = STRONG_PASSWORD (sama seperti step 5)
#   - APP_URL     = https://geolicense.my.id
#   - GEOLICENSE_TOKEN_SECRET = hasil `openssl rand -hex 32`   (WAJIB diganti)

composer install --no-dev --optimize-autoloader --no-interaction
php artisan key:generate
php artisan migrate --seed --force        # seed sekali (bikin admin awal — lihat CATATAN KEAMANAN)
npm ci && NODE_OPTIONS=--max-old-space-size=640 npm run build
php artisan storage:link
php artisan optimize

# Izin direktori yang ditulis Laravel → grup www-data (pemilik PHP-FPM)
sudo chown -R alexistdev:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Izinkan deploy.sh me-reload PHP-FPM tanpa password (untuk CI)
echo 'alexistdev ALL=(root) NOPASSWD: /usr/bin/systemctl reload php8.4-fpm, /usr/bin/systemctl reload nginx' \
  | sudo tee /etc/sudoers.d/geolicense-alexistdev
sudo chmod 440 /etc/sudoers.d/geolicense-alexistdev

# Pasang tuning MySQL & pool PHP-FPM
sudo cp deploy/mysql-geolicense.cnf /etc/mysql/mysql.conf.d/geolicense.cnf
sudo systemctl restart mysql
sudo cp deploy/php-fpm-geolicense.conf /etc/php/8.4/fpm/pool.d/www.conf
sudo systemctl restart php8.4-fpm
```

## 8. Nginx + HTTPS

```bash
sudo cp /var/www/geolicense/deploy/nginx-geolicense.conf /etc/nginx/sites-available/geolicense
sudo ln -sf /etc/nginx/sites-available/geolicense /etc/nginx/sites-enabled/geolicense
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx

# HTTPS otomatis (menambah blok 443, redirect 80->443, auto-renew)
sudo certbot --nginx -d geolicense.my.id --redirect --agree-tos -m alexistdev@gmail.com --no-eff-email
```
Uji: `curl -I https://geolicense.my.id/up` → `HTTP/2 200`.

## 9. CI/CD (GitHub Actions auto-deploy)

Buat **key kedua** khusus untuk Actions SSH ke server (beda dari deploy key repo):

```bash
# Di laptop (atau di server lalu ambil private-nya sekali):
ssh-keygen -t ed25519 -C "gha-deploy" -f ~/.ssh/gha_geolicense -N ""

# Daftarkan PUBLIC key ke server agar Actions bisa masuk sebagai `alexistdev`
ssh alexistdev@SERVER_IP "echo '$(cat ~/.ssh/gha_geolicense.pub)' >> ~/.ssh/authorized_keys"
```

GitHub repo **Settings → Secrets and variables → Actions → New repository secret**:

| Secret | Nilai |
|---|---|
| `SSH_HOST` | `SERVER_IP` |
| `SSH_USER` | `alexistdev` |
| `SSH_PORT` | `22` |
| `SSH_PRIVATE_KEY` | isi lengkap file `~/.ssh/gha_geolicense` (PRIVATE key) |

Uji:
```bash
git commit --allow-empty -m "ci: test deploy"
git push origin main
```
Buka tab **Actions** → job **Deploy to VPS** harus hijau, dan smoke-test `/up` = 200.

## 10. Verifikasi

- `curl -I https://geolicense.my.id/up` → `200`, gembok TLS valid di browser.
- Login admin di `https://geolicense.my.id`.
- License API dari mesin lain:
  ```bash
  curl -X POST https://geolicense.my.id/api/v1/licenses/activate \
    -H 'Content-Type: application/json' \
    -d '{"licenseKey":"<key dari halaman License>","machineId":"M1","osInfo":"linux"}'
  ```
- `free -h` saat build → swap terpakai, RAM idle < ~600 MB.

---

## ⚠️ CATATAN KEAMANAN (wajib sebelum go-live)

- **Akun demo dari seeder** (`alexistdev@gmail.com`, `admin@gmail.com`,
  `user@gmail.com`, semua password `1234`). Setelah `migrate --seed`:
  **ganti password admin** dan **hapus/nonaktifkan `user@gmail.com`**.
  Alternatif: jalankan `php artisan migrate --force` **tanpa** `--seed`, lalu
  buat admin manual via `php artisan tinker`.
- **`GEOLICENSE_TOKEN_SECRET` wajib diganti** dengan `openssl rand -hex 32`.
  Nilai contoh di `.env.example` bersifat publik → token lisensi bisa dipalsukan.
- `APP_DEBUG=false`, `APP_ENV=production`.
- MySQL bind `127.0.0.1` saja; ufw hanya buka 22/80/443; root SSH nonaktif.

## Deploy berikutnya

Cukup `git push origin main` → GitHub Actions menjalankan `deploy.sh`.
Manual (kalau perlu): `ssh alexistdev@SERVER_IP 'bash /var/www/geolicense/deploy/deploy.sh'`.

## Ditunda (tambah saat perlu)
- **Supervisor/queue worker** — kalau ada job `ShouldQueue`/email antrian.
- **Cron `schedule:run`** — kalau ada scheduled task.
- **SMTP** (Brevo/Mailgun) — kalau mulai kirim email betulan.
- **Redis / droplet lebih besar** — kalau trafik naik / swap sering thrash.
