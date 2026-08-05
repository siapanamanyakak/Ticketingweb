# IT Helpdesk Ticketing System

Sistem pelaporan dan manajemen kendala IT berbasis web yang dirancang untuk merampingkan proses operasional dan administratif tim IT Support. Sistem ini mendigitalisasi pelaporan masalah yang sebelumnya berjalan konvensional dan manual menjadi lebih terpusat, transparan, serta terukur (diimplementasikan untuk studi kasus lingkungan operasional di KTU Shipyard Sagulung).

## Fitur Utama

- **🤖 Auto-Categorization & Priority:** Fitur otomatisasi berbasis logika *keyword-matching* yang mampu mendeteksi kata kunci dari laporan pengguna untuk menentukan kategori dan prioritas masalah secara otomatis, sehingga memangkas beban administratif teknisi.
- **⏱️ Modul SLA (Service Level Agreement):** Parameter pengukuran kinerja yang memantau *response time* dan *resolved time* berdasarkan tingkat prioritas. Pelapor dapat melihat estimasi waktu penyelesaian secara langsung.
- **📝 Manajemen Tiket Terstruktur:** Dokumentasi rapi untuk setiap tiket pelaporan, lengkap dengan log perubahan riwayat penanganan, serta fitur komunikasi interaktif antara pengguna dan teknisi IT.
- **📊 Dashboard & Visualisasi Data:** Antarmuka yang menyajikan rekapitulasi data layanan IT dalam bentuk grafik visual untuk kemudahan pemantauan (monitoring).
- **📥 Ekspor Laporan:** Dukungan *export* data pelaporan dan penyelesaian masalah untuk memfasilitasi evaluasi kinerja manajemen, analisis kinerja teknisi, dan pengarsipan solusi teknis.

## 🛠️ Teknologi yang Digunakan

- **Frontend:** HTML, CSS, JavaScript, Framework CSS (Node/NPM)
- **Backend:** PHP (Laravel Framework)
- **Database:** MySQL
- **Pengujian:** Diuji secara lokal dan jarak jauh menggunakan akses *Cloudflared Tunnel*.

## Panduan Instalasi (Local Development)

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di *local machine* Anda:

1. **Clone Repository**
   ```bash
   git clone [https://github.com/siapanamanyakak/Ticketingweb.git](https://github.com/siapanamanyakak/Ticketingweb.git)
   cd Ticketingweb
   ```

2. **Install Dependencies**
   Pastikan Anda sudah menginstal Composer dan Node.js, kemudian jalankan:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment & Database**
   - Buat database baru di MySQL (contoh: `db_ticketing`).
   - Salin file `.env.example` menjadi `.env`:
     ```bash
     cp .env.example .env
     ```
   - Buka file `.env` dan sesuaikan kredensial database Anda (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
   - Generate Application Key:
     ```bash
     php artisan key:generate
     ```

4. **Migrasi Database**
   Jalankan perintah ini untuk membangun tabel database:
   ```bash
   php artisan migrate
   ```
   *(Opsional: Tambahkan `--seed` jika Anda memiliki data dumi/awal)*

5. **Jalankan Aplikasi**
   Anda perlu menjalankan beberapa *terminal* terpisah untuk menjalankan aplikasi, *frontend assets*, dan *scheduler* (terutama untuk fungsi SLA):

   - Terminal 1 (Menjalankan Web Server):
     ```bash
     php artisan serve
     ```
   - Terminal 2 (Kompilasi Frontend Assets):
     ```bash
     npm run dev
     ```
   - Terminal 3 (Menjalankan Task Scheduler/Background Jobs):
     ```bash
     php artisan schedule:work
     ```

   Akses aplikasi melalui browser pada alamat `http://localhost:8000`.

## Pengguna Sistem (Role)

Sistem ini dirancang untuk mengakomodasi tiga peran utama:
1. **User/Pelapor:** Karyawan dari berbagai departemen yang melaporkan kendala IT, memantau status tiket, dan melihat estimasi SLA.
2. **IT Support (Teknisi):** Staf yang menerima tugas, menangani masalah, dan mendokumentasikan penyelesaian.
3. **IT Supervisor/Admin:** Pihak manajemen yang mengelola seluruh tiket, memantau metrik SLA, mengevaluasi kinerja teknisi, dan menarik laporan layanan IT.

## Penulis

**Bryan Aditya Dachi**  
*Politeknik Negeri Batam*  

Proyek ini dikembangkan dan dirancang sebagai bagian dari pemenuhan syarat kelulusan Tugas Akhir.

## Lisensi

[MIT License](LICENSE)
