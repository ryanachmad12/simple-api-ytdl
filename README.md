# Simple Api YTDL

Sebuah aplikasi web modern yang berfungsi sebagai antarmuka untuk `yt-dlp`. Proyek ini memungkinkan pengguna untuk mengunduh video atau audio dari YouTube dengan cepat dan efisien melalui antarmuka yang bersih tanpa perlu me-refresh halaman.

Aplikasi ini dirancang dengan memisahkan logika antara frontend dan backend untuk memberikan pengalaman pengguna yang responsif. Prosesnya dibagi menjadi dua tahap: menampilkan informasi video secara instan, kemudian memproses unduhan di latar belakang.

-----

## Fitur Utama

  - **Antarmuka Modern**: Dibangun dengan HTML, CSS, dan JavaScript murni untuk performa yang ringan dan cepat.
  - **Pengalaman Asinkron**: Seluruh proses, mulai dari pengambilan info hingga pengunduhan, berjalan tanpa me-refresh halaman (menggunakan AJAX/Fetch API).
  - **Respons Cepat**: Pengguna akan langsung melihat thumbnail dan judul video sebagai konfirmasi sebelum proses unduhan yang berat dimulai.
  - **Backend Efisien**: Menggunakan PHP sebagai backend untuk berinteraksi dengan *command-line tool* `yt-dlp`.
  - **Pembersihan Otomatis**: Dilengkapi dengan skrip untuk membersihkan file-file lama secara otomatis guna menghemat ruang server.
  - **Keamanan**: Dilengkapi proteksi dasar terhadap *Cross-Site Scripting* (XSS) dan melarang akses langsung ke direktori unduhan.

-----

## Cara Kerja

Arsitektur aplikasi ini sengaja dirancang untuk memaksimalkan responsivitas. Berikut adalah alur kerjanya:

1.  **Permintaan Info**: Ketika pengguna memasukkan URL YouTube dan menekan tombol "Proses", frontend (`app.js`) akan mengirimkan permintaan pertama ke `getInfo.php`.
2.  **Respons Cepat**: `getInfo.php` dengan cepat memanggil `yt-dlp` hanya untuk mengambil metadata (judul & thumbnail) dan mengirimkannya kembali ke frontend dalam format JSON. Proses ini biasanya hanya memakan waktu 1-3 detik.
3.  **Tampilan Konfirmasi**: Frontend menerima data dan langsung menampilkan thumbnail beserta judulnya. Pada saat yang sama, sebuah *progress bar* mulai berjalan, dan permintaan kedua secara otomatis dikirim.
4.  **Proses Unduhan**: Permintaan kedua dikirim ke `prepareDownload.php`. Endpoint ini melakukan tugas berat: memanggil `yt-dlp` untuk men-download dan mengonversi video ke format yang dipilih (MP3/MP4) dan menyimpannya di direktori `downloader/` di server.
5.  **Pemicu Unduhan**: Setelah file berhasil disimpan di server, `prepareDownload.php` akan mengirimkan kembali respons berisi tautan unduhan file tersebut.
6.  **Selesai**: Frontend menerima tautan tersebut dan secara otomatis memicu proses unduhan di browser pengguna.

-----

## Instalasi dan Penggunaan

Ikuti langkah-langkah berikut untuk menginstal dan menjalankan aplikasi di server Anda.

### Langkah 1: Instalasi Dependensi

Pastikan semua perangkat lunak yang dibutuhkan sudah terinstal di server.

#### A. yt-dlp (Versi Terbaru)

Metode ini memastikan Anda mendapatkan versi `yt-dlp` paling baru langsung dari pengembangnya, yang penting untuk kompatibilitas dengan YouTube.

```bash
# Unduh file executable yt-dlp ke direktori bin global
sudo curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp

# Berikan izin eksekusi
sudo chmod a+rx /usr/local/bin/yt-dlp
```

#### B. FFmpeg

FFmpeg diperlukan untuk proses konversi, terutama untuk format MP3.

**Untuk Debian / Ubuntu (apt):**

```bash
sudo apt update
sudo apt install ffmpeg -y
```

**Untuk Fedora / CentOS Stream / RHEL (dnf):**

```bash
sudo dnf install ffmpeg -y
```

*(Catatan: Mungkin Anda perlu mengaktifkan repositori **RPM Fusion** terlebih dahulu jika FFmpeg tidak ditemukan.)*

### Langkah 2: Clone Repositori

```bash
git clone https://github.com/ryanachmad12/simple-api-ytdl.git
cd simple-api-ytdl
```

### Langkah 3: Konfigurasi Path

Buka file `getInfo.php` dan `prepareDownload.php`. Pastikan konstanta `YT_DLP_PATH` sesuai dengan lokasi instalasi `yt-dlp` (seharusnya sudah benar jika mengikuti Langkah 1).

```php
define('YT_DLP_PATH', '/usr/local/bin/yt-dlp');
```

### Langkah 4: Atur Izin Direktori

Pastikan direktori `downloader` dapat ditulisi oleh user web server (misal: `www-data` atau `apache`).

```bash
# Ganti www-data:www-data sesuai user server Anda jika perlu
sudo chown -R www-data:www-data downloader
sudo chmod -R 775 downloader
```

### Langkah 5: Konfigurasi .htaccess

Untuk keamanan, tambahkan file `.htaccess` di dalam direktori `downloader` dengan isi berikut untuk mencegah *directory listing*:

```htaccess
Options -Indexes
```

### Langkah 6: Atur Cron Job (Pembersihan Otomatis)

Untuk menghapus file-file lama secara berkala, atur *cron job* untuk menjalankan `cleanup.php`. Buka crontab dengan `crontab -e` dan tambahkan baris berikut (jalankan setiap jam):

```bash
0 * * * * /usr/bin/php /path/to/path/cleanup.php >> /path/to/path/cleanup.log 2>&1
```

*(Pastikan path PHP dan path proyek sudah benar)*

### Langkah 7: Akses Aplikasi

Arahkan web server Anda ke direktori proyek dan buka `index.html` melalui browser.
