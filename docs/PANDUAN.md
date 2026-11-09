# Panduan Maivile

## Menjalankan di komputer sendiri

```cmd
composer install
copy .env.example .env
php artisan key:generate
:: isi DB_* di .env, buat database maivile (utf8mb4), lalu:
php artisan migrate --seed
php artisan serve
```

Akun demo (HANYA untuk lokal):

| Role  | Login                          | Password      |
|-------|--------------------------------|---------------|
| Admin | `admin@maivile.test`           | `Admin#12345` |
| Siswa | `2024001`, `2024002`, `2024003` | `Siswa#12345` |

## Pemasangan di server produksi

1. **Jangan jalankan seeder demo.** Pakai `php artisan migrate --force`, bukan `--seed`.
   Seeder hanya berisi akun dan data contoh.
2. **Buat Admin pertama:**
   ```
   php artisan maivile:create-admin
   ```
   Kata sandi diminta lewat prompt (tidak tampil di layar) dan harus 8+ karakter
   dengan huruf besar, huruf kecil, dan angka.
3. **Isi `.env` produksi:**
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://alamat-sekolah-anda
   APP_TIMEZONE=Asia/Jakarta
   SESSION_SECURE_COOKIE=true      # bila memakai HTTPS
   ```
   `APP_DEBUG=true` di produksi membocorkan isi `.env` lewat halaman error.
4. **Arahkan root web server ke folder `public/`.** Folder lain (app, storage,
   .env) tidak boleh bisa dibuka dari browser.
5. **Izin tulis** untuk `storage/` dan `bootstrap/cache/`.
6. **Optimasi:**
   ```
   php artisan optimize
   ```
   Setelah mengubah `.env` atau route, jalankan `php artisan optimize:clear`
   lalu `php artisan optimize` lagi.

## Berkas dan cadangan (backup)

- Semua berkas unggahan (dokumen, lampiran, bukti serah terima) ada di
  `storage/app/private`. Berkas ini **tidak** bisa dibuka lewat URL langsung,
  hanya lewat aplikasi setelah pemeriksaan hak akses.
- Cadangkan dua hal secara rutin: **database MySQL** dan folder
  **`storage/app/private`**. Cadangan database tanpa folder itu membuat
  daftar dokumen tetap ada tetapi file-nya hilang.
- Uji pemulihan cadangan minimal sekali, jangan hanya menyimpannya.

## Aset tampilan

Bootstrap, Bootstrap Icons, dan font Inter dilayani dari `public/vendor/`,
jadi aplikasi tidak butuh internet untuk tampil normal. Lisensi tiap paket
ada di foldernya masing-masing.

## Perintah berguna

| Perintah | Fungsi |
|---|---|
| `php artisan maivile:create-admin` | Buat akun Admin |
| `php artisan test` | Jalankan semua test |
| `php artisan route:list --except-vendor` | Lihat daftar route |
| `php artisan optimize:clear` | Bersihkan semua cache |
