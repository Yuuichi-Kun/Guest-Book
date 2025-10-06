# Buku Tamu (Native PHP + MySQL)

Aplikasi buku tamu sederhana dengan fitur CRUD menggunakan PHP native dan MySQL. Aman dasar (prepared statements, CSRF token, escaping output).

## Struktur Project
```
config/
  config.php     # Konfigurasi environment (host DB, user, password, dsb)
  schema.sql     # SQL untuk membuat database & tabel
lib/
  db.php         # Koneksi PDO dan helper pembuatan tabel
public/
  index.php      # List + Create + Delete
  edit.php       # Edit/Update
  styles.css     # CSS sederhana
```

## Persyaratan
- PHP 8.0+ dengan ekstensi `pdo_mysql`
- MySQL/MariaDB yang bisa diakses dari hosting
- Web server mengarah ke folder `public/` sebagai document root

## Cara Menjalankan di Lokal
1. Buat database:
   - Import `config/schema.sql` ke MySQL Anda (via phpMyAdmin atau CLI), atau biarkan aplikasi membuat tabel otomatis lewat `ensure_guestbook_table()` saat pertama kali diakses.
2. Salin `config/config.php` dan atur kredensial DB:
   - `db_host`, `db_name`, `db_user`, `db_pass`, `db_port` jika perlu.
3. Jalankan server lokal (opsi):
   ```bash
   php -S localhost:8000 -t public
   ```
4. Buka `http://localhost:8000` lalu coba tambah, edit, dan hapus entri.

## Deploy ke Hosting (Shared Hosting / VPS)
1. Buat database dan user di hosting, simpan host, nama DB, user, password.
2. Unggah file project:
   - Pastikan `public/` dijadikan document root (pada shared hosting seringnya `public_html/`).
   - Jika tidak bisa ubah document root, pindahkan isi `public/` ke `public_html/` dan sesuaikan path CSS/link jika perlu.
3. Update `config/config.php` dengan kredensial DB hosting Anda.
4. Import `config/schema.sql` melalui phpMyAdmin hosting.
5. Akses domain Anda, aplikasi siap.

## Catatan Keamanan dan Praktik Baik
- Form menggunakan CSRF token via session.
- Query menggunakan prepared statements (PDO) untuk mencegah SQL Injection.
- Output di-escape menggunakan `htmlspecialchars`.

## CRUD di Aplikasi
- Create: Form di `index.php`
- Read: Tabel daftar entri di `index.php`
- Update: Halaman `edit.php`
- Delete: Tombol Hapus di `index.php` (dengan konfirmasi)

## Kustomisasi Cepat
- Ubah judul, logo, atau gaya di `public/styles.css`.
- Tambahkan field baru: update `schema.sql`, `lib/db.php` (ensure), form input di `index.php`/`edit.php`, dan query terkait.
