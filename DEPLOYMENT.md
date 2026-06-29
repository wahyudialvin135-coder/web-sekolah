# 🎓 Web Sekolah - Sistem Monitoring Akreditasi Sekolah

Aplikasi Laravel + React untuk monitoring dan manajemen akreditasi sekolah dengan fitur:
- 📊 Dashboard monitoring akreditasi
- 🗺️ Peta interaktif sekolah
- 📋 Laporan akreditasi
- 👥 Manajemen pengguna & role

## 🚀 Deployment Guide

### Option 1: Railway (Recommended for Laravel)
Railway adalah platform terbaik untuk deploy full-stack Laravel apps.

#### Setup Railway:
1. **Buka [railway.app](https://railway.app)** dan login/signup dengan GitHub
2. **Klik "New Project"** → **Deploy from GitHub repo**
3. **Pilih repository** `web-sekolah` Anda
4. **Konfigurasi Environment Variables:**
   - `APP_KEY`: Generate dengan `php artisan key:generate`
   - `APP_ENV`: `production`
   - `APP_DEBUG`: `false`
   - `DB_CONNECTION`: `pgsql`
   - `DB_HOST`: Railway auto-inject
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Railway auto-inject

5. **Klik "Deploy"** - Railway akan automatically:
   - Build Docker image
   - Deploy ke production
   - Set up database (PostgreSQL)
   - Run migrations

**URL Publik**: Akan tersedia di dashboard Railway

---

### Option 2: Vercel (Frontend Only)
Jika Anda ingin deploy frontend React ke Vercel + backend ke Railway.

#### Setup Vercel:
1. **Buka [vercel.com](https://vercel.com)** dan login dengan GitHub
2. **Import project** `web-sekolah`
3. **Setup build command:**
   ```
   npm run build
   ```
4. **Output directory:** `dist`
5. **Deploy!**

---

### Option 3: Fly.io (Alternative)
1. **Install Fly CLI**: `brew install flyctl` (atau download dari fly.io)
2. **Login**: `flyctl auth login`
3. **Deploy**: 
   ```bash
   flyctl launch
   flyctl deploy
   ```

---

## 📝 Setup Local Development

```bash
# 1. Clone repository
git clone https://github.com/SIDG(#*/web-sekolah.git
cd web-sekolah

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Setup database
php artisan migrate --seed

# 5. Build assets
npm run dev

# 6. Run server
php artisan serve
```

**Akses**: http://localhost:8000

---

## 🐳 Docker Deployment

```bash
# Build image
docker build -t web-sekolah .

# Run container
docker run -p 8000:8000 \
  -e APP_KEY=your-key \
  -e DB_CONNECTION=pgsql \
  web-sekolah

# Atau gunakan docker-compose
docker-compose up -d
```

---

## 🔐 Environment Variables

Buat `.env` file dengan template dari `.env.example`:

```env
APP_NAME=WebSekolah
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=base64:xxxxx

DB_CONNECTION=pgsql
DB_HOST=your-database-host
DB_PORT=5432
DB_DATABASE=web_sekolah
DB_USERNAME=postgres
DB_PASSWORD=your-password

CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

---

## 📊 Arsitektur

```
web-sekolah/
├── app/               # Laravel backend
├── resources/js/      # React frontend
├── public/            # Static files
├── database/          # Migrations & seeders
├── routes/            # API routes
└── Dockerfile         # Container config
```

---

## 🧪 Testing

```bash
# Unit tests
php artisan test

# Feature tests
php artisan test --testsuite=Feature
```

---

## 📱 Support

- **Issues**: Buka issue di GitHub
- **Database**: PostgreSQL (Railway)
- **Cache**: Database
- **Storage**: Local storage di server

---

## 📄 License

MIT License

---

## ✅ Checklist Deploy

- [x] Git repository siap
- [x] Dockerfile configured
- [x] Environment variables documented
- [x] GitHub Actions setup
- [x] Database migrations ready
- [ ] Push ke GitHub
- [ ] Connect Railway
- [ ] Setup database
- [ ] Test URL publik

---

**Status**: Siap untuk production! 🎉

Deploy sekarang dengan Railway: https://railway.app
