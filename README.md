# Sistem Pengaduan Fasilitas Sekolah

Aplikasi web untuk mengelola pengaduan kerusakan fasilitas sekolah berbasis PHP, MySQL, dan Bootstrap 5.

## Fitur Aplikasi

### Admin
1. ✅ Mendaftarkan user/siswa baru
2. ✅ Melihat list aspirasi keseluruhan (per tanggal, per bulan, per siswa, per kategori)
3. ✅ Menentukan status penyelesaian laporan (Pending, Proses, Selesai, Ditolak)
4. ✅ Melihat histori pengaduan
5. ✅ Memberikan umpan balik aspirasi

### Siswa
1. ✅ Melaporkan pengaduan kerusakan fasilitas/sarana sekolah
2. ✅ Melihat histori pelaporan
3. ✅ Melihat umpan balik dari admin
4. ✅ Melihat progres perbaikan dengan timeline visual

## Teknologi yang Digunakan
- **Backend:** PHP 7.4+
- **Database:** MySQL
- **Frontend:** Bootstrap 5.3, HTML5, CSS3
- **Icons:** Bootstrap Icons

## Struktur Database

### Tabel `users`
- Menyimpan data admin dan siswa
- Fields: id, username, password, nama_lengkap, role, nis, kelas, email, created_at

### Tabel `kategori`
- Menyimpan kategori pengaduan
- Fields: id, nama_kategori, deskripsi

### Tabel `pengaduan`
- Menyimpan laporan pengaduan
- Fields: id, user_id, kategori_id, judul, deskripsi, lokasi, foto, status, tanggal_lapor, tanggal_selesai

### Tabel `feedback`
- Menyimpan feedback admin untuk pengaduan
- Fields: id, pengaduan_id, pesan, tanggal_feedback

## Cara Instalasi

### 1. Persiapan
- Pastikan XAMPP sudah terinstall
- Aktifkan Apache dan MySQL dari XAMPP Control Panel

### 2. Setup Database
```sql
1. Buka phpMyAdmin (http://localhost/phpmyadmin)
2. Import file database.sql
   - Klik tab "Import"
   - Pilih file database.sql
   - Klik "Go"
```

### 3. Konfigurasi
File `config.php` sudah dikonfigurasi dengan default:
```php
DB_HOST: localhost
DB_USER: root
DB_PASS: (kosong)
DB_NAME: pengaduan_sekolah
```

Jika setting database Anda berbeda, edit file `config.php`

### 4. Folder Uploads
Aplikasi akan otomatis membuat folder `uploads/` saat siswa pertama kali upload foto

### 5. Akses Aplikasi
Buka browser dan akses: `http://localhost/ukk_rpl2026/`

## Akun Default

### Admin
- **Username:** aoel123
- **Password:** aoel

### Siswa (untuk testing)
- **Username:** siswa01
- **Password:** siswa123

## Fitur Detail

### Admin Panel
- **Dashboard:** Statistik pengaduan, tabel pengaduan terbaru
- **Kelola User:** Tambah, lihat, dan hapus user/siswa
- **Kelola Pengaduan:** 
  - Filter by status, kategori, tanggal, bulan, siswa
  - Update status pengaduan
  - Kirim feedback ke siswa
  - Lihat detail lengkap pengaduan
- **Histori:** Lihat semua histori pengaduan

### Siswa Panel
- **Dashboard:** Statistik laporan pribadi, quick action
- **Buat Laporan:** 
  - Form lengkap dengan kategori, judul, lokasi, deskripsi
  - Upload foto (optional)
  - Panduan pelaporan
- **Histori:** 
  - Timeline visual progres perbaikan
  - Progress bar status
  - Lihat semua feedback dari admin
  - Detail lengkap setiap laporan

## Status Pengaduan
1. **Pending** (Kuning): Laporan baru, menunggu ditindaklanjuti
2. **Proses** (Biru): Sedang dalam perbaikan
3. **Selesai** (Hijau): Perbaikan selesai
4. **Ditolak** (Merah): Laporan ditolak

## Kategori Default
1. Kerusakan Meja/Kursi
2. Kerusakan Toilet
3. Kerusakan Lampu
4. Kerusakan AC/Kipas
5. Kerusakan Papan Tulis
6. Lainnya

## Keamanan
- Password di-hash menggunakan `password_hash()` PHP
- SQL Injection prevention dengan `mysqli_real_escape_string()`
- XSS prevention dengan `htmlspecialchars()`
- Session management untuk autentikasi
- Role-based access control (Admin/Siswa)

## Troubleshooting

### Error: Cannot connect to database
- Pastikan MySQL di XAMPP sudah running
- Cek konfigurasi di `config.php`

### Error: Folder uploads tidak bisa diakses
- Pastikan folder `uploads/` memiliki permission write (chmod 777)

### Foto tidak muncul
- Cek apakah folder `uploads/` ada
- Cek permission folder uploads

## Support
Untuk pertanyaan atau bantuan, silakan hubungi administrator sistem.

## License
© 2026 Sistem Pengaduan Fasilitas Sekolah - UKK RPL 2026
