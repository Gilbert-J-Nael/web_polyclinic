# Sistem Optimasi Antrean Klinik dengan Algoritma Multi Level Feedback Queue (MLFQ)

Website manajemen antrean poliklinik yang mengimplementasikan algoritma **Multi Level Feedback Queue (MLFQ)** versi modifikasi (non-preemptive) untuk menentukan prioritas pelayanan pasien berdasarkan kondisi medis, bukan sekadar urutan kedatangan (FIFO). Dikembangkan sebagai Tugas Akhir Program Studi Informatika, Universitas Bhinneka Nusantara.

## Daftar Isi

- [Deskripsi Aplikasi](#deskripsi-aplikasi)
- [Fitur Utama](#fitur-utama)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Panduan Penggunaan](#panduan-penggunaan)
- [Menjalankan Aplikasi dengan Docker](#menjalankan-aplikasi-dengan-docker)
- [Struktur Project](#struktur-project)
- [Kontributor](#kontributor)

## Deskripsi Aplikasi

Antrean konvensional berbasis FIFO (First In First Out) di klinik rawat jalan sering mengabaikan urgensi klinis pasien, sehingga pasien dengan kondisi lebih berat harus menunggu sama lamanya dengan pasien lain. Aplikasi ini menjawab masalah tersebut dengan menerapkan algoritma **MLFQ yang dimodifikasi menjadi non-preemptive**, di mana urutan pelayanan dihitung dari skor prioritas berbasis 4 variabel medis berbobot:

| Variabel | Bobot |
|---|---|
| Tekanan darah | 40% |
| Skor keluhan utama | 35% |
| Waktu tunggu | 15% |
| Status kondisi medis khusus | 10% |

Sistem menggunakan struktur antrean dua lapis:

- **Fixed Queue** — kapasitas statis 3 slot, diurutkan secara FIFO.
- **Shadow Queue** — bersifat dinamis, diurutkan berdasarkan skor prioritas MLFQ, dilengkapi mekanisme **BubbleUp** (pergeseran posisi maksimal 2 langkah) dan perlindungan anti-starvation (`ADJUSTED_STATUS` dan `WAITING_TIME`) agar pasien lama tidak terus dilewati.

Saat slot pada Fixed Queue kosong (pasien selesai dilayani/tidak hadir), sistem otomatis mengisinya dengan pasien teratas dari Shadow Queue.

## Fitur Utama

**Tamu (pasien)**
- Dashboard pasien — menampilkan data antrean aktif secara real-time.

**Petugas Frontdesk**
- Login & logout dengan hak akses berbasis peran (Role-Based Access Control).
- Dashboard petugas.
- Pendaftaran pasien ke antrean poliklinik beserta input kondisi medis.
- Mekanisme bypass antrean otomatis berbasis skor prioritas MLFQ.
- Manajemen data pasien.
- Manajemen data dokter.
- Manajemen jadwal dokter.
- Riwayat kunjungan pasien (dengan filter tanggal, nama, dan poli tujuan).
- Cetak laporan operasional harian dalam format PDF.

## Teknologi yang Digunakan

- **Backend**: PHP 8.2, Laravel 10 (arsitektur MVC, Eloquent ORM, Blade)
- **Database**: MySQL 8.0
- **Frontend**: Blade templating engine
- **IDE Pengembangan**: Visual Studio Code
- **Browser Pengujian**: Google Chrome

## Panduan Penggunaan

### Kebutuhan Sistem

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Node.js & NPM (untuk build asset front-end, jika digunakan)

### Instalasi Manual (Tanpa Docker)

1. Clone repository:
   ```bash
   git clone https://github.com/<username>/<nama-repo>.git
   cd <nama-repo>
   ```
2. Install dependency PHP:
   ```bash
   composer install
   ```
3. Install dependency front-end (jika ada):
   ```bash
   npm install && npm run build
   ```
4. Salin file environment dan generate application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
5. Sesuaikan konfigurasi database pada file `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nama_database
   DB_USERNAME=root
   DB_PASSWORD=
   ```
6. Jalankan migrasi (dan seeder jika tersedia):
   ```bash
   php artisan migrate --seed
   ```
7. Jalankan server lokal:
   ```bash
   php artisan serve
   ```
8. Akses aplikasi melalui `http://127.0.0.1:8000`.

### Akun Default (sesuaikan dengan seeder di project)

| Peran | Username | Password |
|---|---|---|
| Petugas Frontdesk | *(isi sesuai seeder)* | *(isi sesuai seeder)* |

## Menjalankan Aplikasi dengan Docker

Repository ini sudah menyertakan `Dockerfile` dan `docker-compose.yml`, sehingga aplikasi dapat dijalankan tanpa perlu instalasi PHP/MySQL secara lokal.

### Prasyarat

- [Docker](https://docs.docker.com/get-docker/) dan [Docker Compose](https://docs.docker.com/compose/install/) sudah terpasang.

### Langkah Menjalankan

1. Clone repository:
   ```bash
   git clone https://github.com/<username>/<nama-repo>.git
   cd <nama-repo>
   ```
2. Salin file environment:
   ```bash
   cp .env.example .env
   ```
3. Sesuaikan variabel koneksi database pada `.env` agar mengarah ke service database di Docker (nama host mengikuti nama service pada `docker-compose.yml`, contoh `db`):
   ```env
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=nama_database
   DB_USERNAME=root
   DB_PASSWORD=secret
   ```
4. Build dan jalankan container:
   ```bash
   docker compose up -d --build
   ```
5. Install dependency PHP di dalam container aplikasi (sesuaikan nama service, contoh `app`):
   ```bash
   docker compose exec app composer install
   ```
6. Generate application key:
   ```bash
   docker compose exec app php artisan key:generate
   ```
7. Jalankan migrasi database:
   ```bash
   docker compose exec app php artisan migrate --seed
   ```
8. Akses aplikasi melalui browser di `http://localhost:8000` (atau port lain sesuai yang didefinisikan pada `docker-compose.yml`).

### Perintah Docker yang Berguna

```bash
# Melihat log container
docker compose logs -f

# Masuk ke shell container aplikasi
docker compose exec app bash

# Menghentikan seluruh container
docker compose down

# Menghentikan container sekaligus menghapus volume database
docker compose down -v
```

> **Catatan:** Nama service (`app`, `db`) dan port pada contoh di atas mengikuti konvensi umum Laravel + Docker. Sesuaikan dengan nama service dan port yang sebenarnya didefinisikan pada `docker-compose.yml` di repository ini.

## Struktur Project

```
├── app/                # Business logic (Models, Controllers, Services)
├── database/
│   ├── migrations/     # Skema tabel database
│   └── seeders/        # Data awal (akun, data dummy)
├── resources/views/    # Blade templates (UI)
├── routes/web.php      # Definisi routing & middleware RBAC
├── public/             # Entry point aplikasi
├── Dockerfile
├── docker-compose.yml
├── .env.example
└── README.md
```

## Kontributor

- **Gilbert Jeremy Nathanael** — Program Studi Informatika, Fakultas Sains dan Teknologi, Universitas Bhinneka Nusantara
- Pembimbing: Hilman Nuril Hadi, S.Kom., M.Kom.
