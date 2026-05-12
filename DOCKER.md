# 🐳 Podman Compose — Development Environment

Dokumentasi lengkap untuk menjalankan project **CashApp** (Laravel 12) menggunakan Podman Compose.

## Arsitektur

```
┌─────────────────────────────────────────────────────┐
│                    Host Machine                     │
│                                                     │
│   Browser ──► :8080 (Nginx) ──► :9000 (PHP-FPM)    │
│   Browser ──► :5173 (Vite HMR)                     │
│   DB Client ► :3307 (MariaDB)                       │
│                                                     │
│   Source code di-mount langsung ke container         │
│   (perubahan kode langsung aktif tanpa rebuild)     │
└─────────────────────────────────────────────────────┘
```

## Prasyarat

- [Podman](https://podman.io/getting-started/installation) ≥ 4.0
- [podman-compose](https://github.com/containers/podman-compose) ≥ 1.0

### Instalasi (Arch/Manjaro)

```bash
sudo pacman -S podman podman-compose
```

### Instalasi (Ubuntu/Debian)

```bash
sudo apt install podman
pip3 install podman-compose
```

---

## Quick Start

```bash
# 1. Build & jalankan semua container
podman-compose up --build -d

# 2. Install PHP dependencies
podman-compose exec app composer install

# 3. Install Node dependencies
podman-compose exec node npm install

# 4. Generate app key (pertama kali)
podman-compose exec app php artisan key:generate

# 5. Jalankan migrasi database
podman-compose exec app php artisan migrate

# 6. Buka di browser
#    http://localhost:8080
```

---

## Services

| Service | Container Name | Image | Port | Deskripsi |
|---------|---------------|-------|------|-----------|
| `app` | `cashapp-app` | `Dockerfile.dev` | 9000 (internal) | PHP 8.3 FPM — menjalankan Laravel |
| `nginx` | `cashapp-nginx` | `nginx:alpine` | **8080** → 80 | Web server & reverse proxy |
| `db` | `cashapp-db` | `mariadb:11` | **3307** → 3306 | MariaDB database server |
| `node` | `cashapp-node` | `Dockerfile.dev` | **5173** → 5173 | Vite dev server (HMR) |

---

## Perintah Umum

### Container Management

```bash
# Start semua container
podman-compose up -d

# Start dengan rebuild image
podman-compose up --build -d

# Stop semua container
podman-compose down

# Stop + hapus volume (reset database)
podman-compose down -v

# Restart satu service
podman-compose restart app

# Lihat status container
podman-compose ps
```

### Logs

```bash
# Lihat semua logs
podman-compose logs -f

# Lihat log service tertentu
podman-compose logs -f app
podman-compose logs -f nginx
podman-compose logs -f db
podman-compose logs -f node
```

### Laravel Artisan

```bash
# Jalankan artisan command
podman-compose exec app php artisan migrate
podman-compose exec app php artisan db:seed
podman-compose exec app php artisan tinker
podman-compose exec app php artisan queue:work
podman-compose exec app php artisan cache:clear
podman-compose exec app php artisan config:clear

# Buat model/controller/migration
podman-compose exec app php artisan make:model NamaModel -mcr
podman-compose exec app php artisan make:controller NamaController
```

### Composer

```bash
podman-compose exec app composer install
podman-compose exec app composer require nama/package
podman-compose exec app composer update
```

### NPM

> ⚠️ **Jangan jalankan `npm install` dari host.** Karena image menggunakan Alpine (musl), install package harus dilakukan dari dalam container agar native binary (seperti Rollup) kompatibel.

```bash
# Install package baru (container harus sedang berjalan)
podman-compose exec node npm install nama-package

# Atau tambahkan manual ke package.json, lalu restart:
podman-compose restart node   # otomatis npm install saat start

# Build untuk production
podman-compose exec node npm run build
```

---

## Akses Database

### Dari Aplikasi Laravel

Konfigurasi di `.env` sudah diset otomatis:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=cashapp
DB_USERNAME=cashapp
DB_PASSWORD=secret
```

### Dari Host (DBeaver, TablePlus, HeidiSQL, dll.)

| Parameter | Value |
|-----------|-------|
| Host | `localhost` |
| Port | `3307` |
| Database | `cashapp` |
| Username | `cashapp` |
| Password | `secret` |

### Dari Terminal

```bash
# Masuk ke MySQL CLI di dalam container
podman-compose exec db mariadb -u cashapp -psecret cashapp
```

---

## Struktur File

```
cashapp/
├── Dockerfile.dev          # Image PHP + Node untuk development
├── podman-compose.yml      # Definisi semua services
├── .dockerignore           # File yang di-exclude dari build
├── docker/
│   └── nginx.conf          # Konfigurasi Nginx
├── .env                    # Environment variables (sudah diupdate)
├── vite.config.js          # Vite config (sudah diupdate untuk HMR)
└── ...                     # File Laravel lainnya
```

---

## Troubleshooting

### Permission Error pada `storage/` atau `bootstrap/cache/`

```bash
podman-compose exec app chmod -R 775 storage bootstrap/cache
podman-compose exec app chown -R laravel:laravel storage bootstrap/cache
```

### Vite HMR Tidak Berfungsi

1. Pastikan port 5173 tidak digunakan aplikasi lain
2. Cek apakah container `node` berjalan: `podman-compose ps`
3. Restart container node: `podman-compose restart node`

### Error `@rollup/rollup-linux-x64-musl` Not Found

Error ini terjadi ketika `node_modules` di-install dari **host** (glibc) lalu di-mount ke container **Alpine** (musl). Native binary rollup tidak cocok dengan arsitektur container.

**Solusi:** Hapus `node_modules` dan install ulang dari dalam container:

```bash
# Hapus node_modules dari host
rm -rf node_modules package-lock.json

# Install ulang dari dalam container (menggunakan image yang sama)
podman run --rm -v "$(pwd):/var/www/html" -w /var/www/html localhost/cashapp_node:latest sh -c "npm install"

# Restart container node
podman-compose restart node
```

> ⚠️ **Penting:** Jangan jalankan `npm install` dari host karena akan menginstall binary glibc. Selalu install dari dalam container.

### Database Connection Refused

1. Pastikan container `db` sudah selesai inisialisasi:
   ```bash
   podman-compose logs db
   ```
2. Tunggu hingga muncul pesan `ready for connections`
3. Pastikan `.env` menggunakan `DB_HOST=db` (bukan `localhost`)

### Container Tidak Bisa Start

```bash
# Cek error detail
podman-compose logs <nama-service>

# Rebuild dari awal
podman-compose down
podman-compose build --no-cache
podman-compose up -d
```

### Reset Total (Fresh Start)

```bash
podman-compose down -v          # Hapus container + volume
podman-compose build --no-cache # Rebuild image dari awal
podman-compose up -d            # Start ulang
podman-compose exec app composer install
# Install node_modules dari dalam container (JANGAN dari host)
podman run --rm -v "$(pwd):/var/www/html" -w /var/www/html localhost/cashapp_node:latest sh -c "npm install"
podman-compose restart node
podman-compose exec app php artisan key:generate
podman-compose exec app php artisan migrate
```
