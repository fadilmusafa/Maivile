# Cara Menjalankan Maivile (lokal)

Folder proyek: `C:\Users\ASM1\maivile`

## 1. Buka di VS Code

1. Buka VS Code, lalu **File > Open Folder** dan pilih `C:\Users\ASM1\maivile`.
2. Buka terminal: tekan **Ctrl + `** (tombol di bawah Esc) atau menu **Terminal > New Terminal**.
3. Kalau terminal bertipe PowerShell dan ada perintah yang error, klik tanda panah di sebelah tombol **+** di panel terminal, lalu pilih **Command Prompt**.

## 2. Hidupkan MySQL

MySQL harus menyala sebelum aplikasi dijalankan. Buka XAMPP / Laragon / aplikasi MySQL Anda, lalu **Start** MySQL.
Pengaturan database ada di file `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

## 3. Jalankan aplikasi

Di terminal VS Code:

```cmd
php artisan serve
```

Buka **http://127.0.0.1:8000** di browser.
Untuk berhenti, klik terminal lalu tekan **Ctrl + C**.

## 4. Akun demo (khusus lokal)

| Role | Login | Kata sandi |
|---|---|---|
| Admin / Petugas | `admin@maivile.test` | `Admin#12345` |
| Siswa 1 (Alya) | `2024001` | `Siswa#12345` |
| Siswa 2 (Bima) | `2024002` | `Siswa#12345` |
| Siswa 3 (Citra) | `2024003` | `Siswa#12345` |
| Siswa 4 (Fadhil) | `20262006` | `123` |

Siswa login memakai **NIS** (atau emailnya). Admin bisa login memakai email atau NIP `198001012005011001`.
Akun demo ini hanya untuk percobaan lokal. Jangan dipakai di server sungguhan.

Akun siswa baru dibuat lewat halaman **Daftar** (perlu disetujui Admin di menu **Pengguna > Menunggu persetujuan**)
atau langsung oleh Admin di menu **Pengguna**.

## 5. Perintah yang sering dipakai

| Perintah | Fungsi |
|---|---|
| `php artisan serve` | Menjalankan aplikasi |
| `php artisan migrate` | Menambah tabel/kolom baru (data aman) |
| `php artisan optimize:clear` | Membersihkan cache |
| `php artisan view:clear` | Membersihkan cache tampilan |
| `php artisan config:clear` | Memuat ulang `.env` |
| `php artisan test` | Menjalankan semua test |
| `php artisan route:list --except-vendor` | Melihat daftar route |
| `php artisan maivile:create-admin` | Membuat akun Admin baru |
| `php artisan maivile:files-check` | Memeriksa berkas yatim di storage |

**Awas:** `php artisan migrate:fresh --seed` menghapus SEMUA data lalu mengisi ulang data demo.
Pakai hanya kalau ingin mengulang dari nol.

## 6. Data sekolah di halaman pembuka (opsional)

Tambahkan di bagian paling bawah file `.env`:

```env
MAIVILE_SCHOOL_NAME="Nama Sekolah Anda"
MAIVILE_SCHOOL_ADDRESS="Alamat lengkap sekolah"
MAIVILE_SERVICE_HOURS="Senin-Jumat, 08.00-15.00"
MAIVILE_CONTACT="(0271) 000000"
```

Lalu jalankan `php artisan config:clear` dan muat ulang browser (Ctrl + F5).

## 7. Kalau ada masalah

| Gejala | Penyebab | Solusi |
|---|---|---|
| `SQLSTATE ... Connection refused` | MySQL belum menyala | Start MySQL di XAMPP/Laragon |
| `Unknown database 'maivile'` | Database belum dibuat | Buat database `maivile` (utf8mb4), lalu `php artisan migrate --seed` |
| `Base table or view not found` | Migration belum dijalankan | `php artisan migrate` |
| Login ditolak padahal sandi benar | Seeder belum jalan | `php artisan db:seed` |
| Halaman tidak berubah setelah ekstrak ZIP | Cache lama | `php artisan optimize:clear` lalu `php artisan view:clear`, lalu Ctrl + F5 di browser |
| `Port 8000 already in use` | Server sebelumnya masih jalan | Tutup terminal lama, atau `php artisan serve --port=8001` |
| `php` tidak dikenali | PHP belum masuk PATH | Jalankan dari terminal XAMPP/Laragon atau tambahkan PHP ke PATH |
