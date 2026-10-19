# Panduan Menjalankan Mindfora

> Dokumen ini adalah runbook mandiri. Buka file ini setiap kali mau menjalankan / menghentikan / men-deploy aplikasi. Tidak perlu tanya AI lagi untuk hal-hal yang sudah tertulis di sini.
>
> **Shell:** Windows PowerShell 5.1 (bawaan Windows 10/11). Semua perintah sudah diuji di PS 5.1. PowerShell 7+ juga kompatibel.
> **Penting untuk PS 5.1:** Tidak support `&&` untuk chain command. Pakai `;` sebagai gantinya (jalankan berurutan, tidak peduli sukses/gagal).

## 1. Prasyarat (cek sekali sebelum pertama kali jalan)

```powershell
# PHP 8.3 + extension yang dibutuhkan Laravel 12
php -v                                  # harus >= 8.2
php -m | findstr /I "pdo_sqlite pdo_mysql openssl mbstring fileinfo gd"   # pastikan semua muncul

# Composer
composer --version

# Node + npm (untuk build asset Vite)
node --version                          # >= 20
npm --version

# cloudflared (untuk tunnel publik). Kalau belum ada:
winget install --id Cloudflare.cloudflared --scope user --silent --accept-source-agreements --accept-package-agreements
# Setelah install, TUTUP & BUKA ULANG terminal supaya PATH terbaca.
```

Path cloudflared setelah install via winget (user scope):
```
C:\Users\<username>\AppData\Local\Microsoft\WinGet\Packages\Cloudflare.cloudflared_Microsoft.Winget.Source_8wekyb3d8bbwe\cloudflared.exe
```

## 2. Setup Awal (hanya sekali, atau setelah reset repo)

```powershell
cd D:\PROJECT\FREELANCE\mindfora

composer install --no-interaction --prefer-dist
copy .env.example .env                 # kalau .env belum ada
php artisan key:generate
npm install
npm run build                          # compile asset Vite untuk produksi
```

## 3. Reset Database (jalankan kalau ada perubahan schema / migration)

```powershell
php artisan migrate:fresh --seed
```

Setelah selesai, cek akun default di `database/seeders/DatabaseSeeder.php` untuk login.

## 4. Menjalankan Aplikasi — 3 Terminal yang WAJIB hidup

Aplikasi butuh **3 proses** berjalan bersamaan. Buka **3 terminal terpisah** (PowerShell / Windows Terminal), masing-masing jalankan 1 perintah di bawah. Jangan tutup terminal selama masih mau pakai aplikasi.

### Terminal 1 — Laravel Server (HTTP)

```powershell
cd D:\PROJECT\FREELANCE\mindfora
php artisan serve --host=127.0.0.1 --port=8211
```

Output yang menandakan sukses:
```
Server running on [http://127.0.0.1:8211]
```

Akses lokal: http://127.0.0.1:8211

### Terminal 2 — Queue Worker (WAJIB untuk fitur upload file)

Tanpa worker ini, upload training material/thumbnail **stuck di status "processing" selamanya**.

```powershell
cd D:\PROJECT\FREELANCE\mindfora
php artisan queue:work --queue=default --tries=3 --timeout=120 --sleep=3
```

Output yang menandakan sukses:
```
[2026-09-02 21:36:00]  Running queue worker...
```

Worker ini akan diam menunggu job. Itu normal — bukan error. Begitu ada upload, log job akan muncul di sini.

### Terminal 3 — Cloudflare Tunnel (untuk akses publik dari luar)

```powershell
cloudflared tunnel --url http://127.0.0.1:8211 --no-autoupdate
```

> **Kalau error "cloudflared tidak dikenali"**, berarti PATH belum terbaca. Tutup & buka ulang terminal, atau pakai path lengkap:
> ```powershell
> & "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\Cloudflare.cloudflared_Microsoft.Winget.Source_8wekyb3d8bbwe\cloudflared.exe" tunnel --url http://127.0.0.1:8211 --no-autoupdate
> ```
> `&` (call operator) WAJIB di awal kalau pakai path lengkap. Tanpa `&`, PowerShell anggap path sebagai string biasa → error "Unexpected token".
> `$env:LOCALAPPDATA` otomatis resolve ke `C:\Users\<username>\AppData\Local` — tidak perlu ganti `<username>` manual.

Output yang menandakan sukses:
```
Your quick Tunnel has been created! Visit it at (it may take some time to be reachable):
https://xxxx-xxxx-xxxx-xxxx.trycloudflare.com
```

Buka URL itu di browser mana saja (HP / laptop lain / kirim ke klien). URL ini **acak & berubah tiap restart tunnel**.

## 5. Menghentikan Aplikasi

Tutup ketiga terminal di atas (atau tekan `Ctrl+C` di masing-masing terminal). Tidak ada data yang hilang — database tersimpan di file SQLite / MySQL server.

## 6. Troubleshooting Cepat

| Gejala | Penyebab | Solusi |
|--------|----------|--------|
| Upload file stuck di "processing" | Queue worker mati | Jalankan Terminal 2 (queue:work) |
| URL publik tidak bisa diakses | cloudflared tunnel mati | Restart Terminal 3, ambil URL baru |
| Asset CSS/JS 404 di URL publik | Asset belum di-build | `npm run build` lalu refresh |
| Halaman blank / error 500 | Cache config kotor | `php artisan config:clear; php artisan cache:clear; php artisan view:clear` (pakai `;` bukan `&&` — PowerShell 5.1 tidak support `&&`) |
| Error "No application encryption key" | APP_KEY kosong | `php artisan key:generate` |
| Livewire endpoint `http://` (mixed content) | TrustProxies tidak aktif | Cek `bootstrap/app.php` ada `$middleware->trustProxies(at: '*')` |
| URL publik berubah tiap start | Quick tunnel = URL acak | Untuk URL stabil, pakai named tunnel (butuh akun Cloudflare gratis) |
| Chat konsultasi tidak realtime | Reverb WebSocket tidak di-tunnel | Pesan tetap terkirim (HTTP), tapi lawan bicara harus refresh halaman. Untuk realtime, perlu tunnel terpisah untuk port 8080 |
| `php artisan serve` error "port sudah dipakai" | Port 8211 masih ditempati proses lama | `Get-Process php | Stop-Process` lalu jalankan ulang |

## 7. Build Asset (setiap habis ubah CSS/JS/Blade yang pakai Vite)

```powershell
cd D:\PROJECT\FREELANCE\mindfora
npm run build
```

Untuk development dengan auto-reload:
```powershell
npm run dev
```
Lalu akses via `http://127.0.0.1:8211` (Vite dev server otomatis inject HMR).

## 8. Cek Status Proses (kalau ragu ada yang mati)

```powershell
# Cek port 8211 (Laravel server)
netstat -ano | findstr ":8211" | findstr "LISTENING"

# Cek proses php & cloudflared yang sedang jalan
Get-CimInstance Win32_Process -Filter "Name='php.exe' OR Name='cloudflared.exe'" | Select-Object ProcessId, Name, CommandLine | Format-List
```

Harus ada minimal:
- 1 `php.exe` dengan command line `-S 127.0.0.1:8211` (Laravel server)
- 1 `php.exe` dengan command line `artisan queue:work` (queue worker)
- 1 `cloudflared.exe` dengan command line `tunnel --url http://127.0.0.1:8211` (tunnel)

## 9. Konfigurasi Penting (jangan diubah kecuali tahu konsekuensinya)

| File | Setting | Nilai saat ini |
|------|---------|----------------|
| `.env` | `APP_ENV` | `local` |
| `.env` | `APP_DEBUG` | `false` (saat live via tunnel) — set `true` hanya saat debug lokal |
| `.env` | `APP_URL` | `http://localhost:8211` |
| `.env` | `QUEUE_CONNECTION` | `database` (butuh worker) |
| `.env` | `BROADCAST_CONNECTION` | `reverb` (chat realtime) |
| `.env` | `FILESYSTEM_DISK` | `local` |
| `.env` | `REVERB_PORT` | `8080` (tidak di-tunnel quick) |
| `bootstrap/app.php` | `trustProxies(at: '*')` | WAJIB aktif supaya asset HTTPS di balik tunnel |

## 10. Catatan Produksi

Panduan ini untuk **demo / development**. Untuk produksi sebenarnya:
- Pakai web server asli (Nginx/Apache + PHP-FPM), bukan `php artisan serve`
- Queue worker via Supervisor / systemd, bukan terminal manual
- cloudflared pakai **named tunnel** (akun Cloudflare gratis) supaya URL stabil & ada dashboard monitoring
- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` = domain final
- Asset di-build sekali (`npm run build`) lalu di-serve static oleh web server
- Database pakai MySQL/PostgreSQL, bukan SQLite
- Storage file pakai S3 / disk terpisah, bukan local
- HTTPS via Cloudflare proxy (otomatis) atau Let's Encrypt

## 11. Quick Start (copy-paste, 3 terminal)

**Terminal 1:**
```powershell
cd D:\PROJECT\FREELANCE\mindfora; php artisan serve --host=127.0.0.1 --port=8211
```

**Terminal 2:**
```powershell
cd D:\PROJECT\FREELANCE\mindfora; php artisan queue:work --queue=default --tries=3 --timeout=120 --sleep=3
```

**Terminal 3:**
```powershell
cloudflared tunnel --url http://127.0.0.1:8211 --no-autoupdate
```
> Kalau `cloudflared` tidak dikenali (PATH belum terbaca), tutup & buka ulang terminal. Atau pakai: `& "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\Cloudflare.cloudflared_Microsoft.Winget.Source_8wekyb3d8bbwe\cloudflared.exe" tunnel --url http://127.0.0.1:8211 --no-autoupdate`
