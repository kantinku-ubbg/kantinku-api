# Kantinku UBBG API

Backend API untuk aplikasi pemesanan kantin kampus.
Dibuat dengan **Laravel 13** dan **Laravel Sanctum** (untuk login pakai token) sebagai
tugas proyek pertemuan 9.

> Singkatnya: aplikasi ini adalah "pelayan" yang menerima pesanan dari aplikasi HP/web,
> menyimpannya ke database, lalu mengirim balik jawaban dalam bentuk JSON.

---

## 1. Anggota Kelompok

| No | Nama | NIM |
|---|---|---|
| 1 | Miftahul Jannah | 23210047 |
| 2 | Susilawati | 23210126 |
| 3 | Fazil Ikhram | 23210077 |
| 4 | Arzan Amin | 23210108 |
| 5 | Mirza Firdaus | 23210013 |

---

## 2. Aplikasi ini bisa apa saja?

| Fitur | Penjelasan sederhana |
|---|---|
| Daftar & login | Pengguna membuat akun, lalu login untuk mendapat **token** |
| Token | Seperti gelang tanda pengenal. Tanpa gelang, tidak boleh masuk |
| Lihat menu | Siapa saja (belum login) boleh melihat daftar kategori dan menu |
| Kelola menu | Hanya **admin** yang boleh menambah, mengubah, menghapus menu |
| Pesan makanan | Pelanggan memilih menu dan jumlahnya, sistem menghitung total otomatis |
| Ubah pesanan | Tambah item, ubah jumlah, hapus item — selama pesanan masih `pending` |
| Lacak status | `pending` → `paid` → `preparing` → `ready` → `completed`, atau `cancelled` |
| Struk | Ringkasan pesanan siap dicetak |
| Ringkasan | Jumlah pesanan dan total belanja per pengguna |

Jumlah endpoint aktif: **29 route API**.

---

## 3. Cara menjalankan di komputer sendiri

Butuh: **PHP 8.3+**, **Composer**, **Node.js** (untuk menjalankan test Postman).

```bash
# 1. Pasang semua bahan/library
composer install

# 2. Buat file pengaturan
cp .env.example .env
php artisan key:generate

# 3. Siapkan database + isi data contoh
php artisan migrate:fresh --seed

# 4. Nyalakan server
php artisan serve
```

Setelah muncul tulisan `Server running`, buka di browser:

```
http://127.0.0.1:8000/api
```

Kalau muncul JSON `{"status":"success", ...}` berarti API sudah hidup.

---

## 4. Akun contoh (sudah otomatis terisi oleh seeder)

| Peran | Email | Password | Boleh apa |
|---|---|---|---|
| Admin | `admin@kantinku.test` | `password123` | Semua, termasuk kelola menu |
| Pelanggan | `susilawati@kantinku.test` | `password123` | Pesan makanan, lihat pesanan sendiri |

Data contoh yang ikut terisi: 6 pengguna, 5 kategori, 14 menu, 8 pesanan, 16 item pesanan.

---

## 5. Struktur folder — dibaca apa maksudnya

Laravel punya aturan folder sendiri. Nama foldernya memang bahasa Inggris karena
itu standar framework-nya (kalau diganti, aplikasi rusak). Ini terjemahannya:

```
kantinku-api/
│
├── app/                          ← "otak" aplikasi (kode yang kita tulis)
│   ├── Http/
│   │   ├── Controllers/Api/      ← Penerima pesanan. Satu file = satu urusan
│   │   │   ├── AuthController.php      (daftar, login, profil, ganti password)
│   │   │   ├── CategoryController.php  (kategori menu)
│   │   │   ├── MenuController.php      (daftar menu)
│   │   │   └── OrderController.php     (pesanan)
│   │   ├── Middleware/
│   │   │   └── EnsureUserIsAdmin.php   ← Penjaga pintu: hanya admin boleh lewat
│   │   ├── Requests/             ← Pemeriksa formulir. Satu file = satu formulir
│   │   │   ├── RegisterRequest.php     (syarat daftar akun)
│   │   │   ├── StoreMenuRequest.php    (syarat tambah menu)
│   │   │   └── ... (total 9 file)
│   │   └── Resources/            ← Penyusun jawaban. Menyembunyikan data rahasia
│   │       ├── UserResource.php        (password tidak pernah ikut terkirim)
│   │       └── ... (total 5 file)
│   ├── Models/                   ← Bentuk data & hubungan antar tabel
│   │   ├── User.php              (pengguna)
│   │   ├── Category.php          (kategori: Makanan Berat, Minuman Dingin, ...)
│   │   ├── Menu.php              (menu: Nasi Goreng, Es Teh, ...)
│   │   ├── Order.php             (pesanan)
│   │   └── OrderItem.php         (rincian item di dalam pesanan)
│   └── Support/
│       └── ApiResponse.php       ← Pencetak jawaban seragam {status, message, data}
│
├── routes/
│   └── api.php                   ← DAFTAR ALAMAT. Semua URL API didaftarkan di sini
│
├── database/
│   ├── migrations/               ← Cetak biru tabel (9 file, urut dari atas ke bawah)
│   └── seeders/                  ← Pengisi data contoh
│       ├── UserSeeder.php
│       ├── CategorySeeder.php
│       ├── MenuSeeder.php
│       ├── OrderSeeder.php
│       └── DatabaseSeeder.php    ← Pemanggil semua seeder di atas
│
├── docs/
│   ├── postman/                  ← File Postman Collection + environment
│   ├── screenshots/              ← Bukti screenshot 11 test
│   └── testing_results.md        ← Laporan 10 test wajib
│
├── config/                       ← Pengaturan bawaan Laravel (jarang disentuh)
├── public/                       ← Pintu masuk web (index.php)
├── resources/                    ← Tampilan (tidak dipakai, ini API murni)
├── storage/                      ← Tempat catatan/log & file sementara
├── tests/                        ← Test otomatis bawaan Laravel
├── .env                          ← Pengaturan rahasia (password DB, dll) — TIDAK ikut Git
└── .env.example                  ← Contoh .env untuk orang lain
```

### Alur satu request, dari masuk sampai keluar

Contoh: pelanggan memesan makanan.

```
1. routes/api.php            → "POST /api/orders, tangani OrderController@store"
2. StoreOrderRequest.php     → cek dulu: order_type valid? items tidak kosong?
                               kalau salah → langsung balas 422, tidak lanjut
3. EnsureUserIsAdmin.php     → (khusus endpoint admin) punya hak? kalau tidak → 403
4. OrderController.php       → jalankan logika: hitung total, kurangi stok, simpan
5. Models/Order.php          → berhubungan dengan tabel orders di database
6. OrderResource.php         → susun jawaban, buang data yang tidak boleh tampil
7. ApiResponse.php           → bungkus jadi {status, message, data}
8. Keluar sebagai JSON      → 201 Created
```

Kalau ada yang salah di tengah jalan, **bootstrap/app.php** yang menerjemahkan error jadi
bentuk seragam: 422 (data salah), 401 (belum login), 403 (bukan admin), 404 (tidak ada).

---

## 6. Bentuk jawaban semua endpoint (seragam)

Berhasil:

```json
{
  "status": "success",
  "message": "Pesanan berhasil dibuat.",
  "data": { "id": 9, "order_code": "ORD-20261126-00009", "total_price": 43000 }
}
```

Berhasil dengan halaman (pagination) — ada tambahan `meta`:

```json
{
  "status": "success",
  "message": "OK",
  "data": [ ... ],
  "meta": { "current_page": 1, "per_page": 5, "total": 5, "last_page": 1 }
}
```

Gagal validasi (422) — ada `errors` per kolom:

```json
{
  "status": "error",
  "message": "Data yang dikirim tidak valid.",
  "data": null,
  "errors": { "email": ["Email sudah dipakai."] }
}
```

---

## 7. Daftar endpoint

Kolom **Akses**: `Publik` = tanpa login, `Token` = harus login, `Admin` = harus admin.

| # | Method | Endpoint | Akses | Gunanya |
|---|---|---|---|---|
| 1 | GET | `/api` | Publik | Cek API hidup |
| 2 | POST | `/api/auth/register` | Publik | Daftar akun baru |
| 3 | POST | `/api/auth/login` | Publik | Login, dapat token |
| 4 | GET | `/api/auth/me` | Token | Lihat profil sendiri |
| 5 | POST | `/api/auth/logout` | Token | Hapus token (keluar) |
| 6 | PUT | `/api/auth/profile` | Token | Ubah nama / nomor HP |
| 7 | PUT | `/api/auth/password` | Token | Ganti password |
| 8 | GET | `/api/categories` | Publik | Daftar kategori |
| 9 | GET | `/api/categories/{slug}` | Publik | Detail kategori |
| 10 | GET | `/api/categories/{slug}/menus` | Publik | Menu dalam satu kategori |
| 11 | POST | `/api/categories` | Admin | Tambah kategori |
| 12 | PUT | `/api/categories/{slug}` | Admin | Ubah kategori |
| 13 | DELETE | `/api/categories/{slug}` | Admin | Hapus kategori |
| 14 | GET | `/api/menus` | Publik | Daftar menu (bisa cari & filter harga) |
| 15 | GET | `/api/menus/{slug}` | Publik | Detail menu |
| 16 | POST | `/api/menus` | Admin | Tambah menu |
| 17 | PUT | `/api/menus/{slug}` | Admin | Ubah menu |
| 18 | DELETE | `/api/menus/{slug}` | Admin | Hapus menu |
| 19 | GET | `/api/orders` | Token | Daftar pesanan (admin: semua, pelanggan: miliknya) |
| 20 | POST | `/api/orders` | Token | Buat pesanan baru |
| 21 | GET | `/api/orders/summary` | Token | Ringkasan jumlah & total belanja |
| 22 | GET | `/api/orders/{id}` | Token | Detail pesanan |
| 23 | GET | `/api/orders/{id}/receipt` | Token | Struk pesanan |
| 24 | POST | `/api/orders/{id}/items` | Token | Tambah item ke pesanan |
| 25 | PUT | `/api/orders/{id}/items/{item}` | Token | Ubah jumlah item |
| 26 | DELETE | `/api/orders/{id}/items/{item}` | Token | Hapus item |
| 27 | PATCH | `/api/orders/{id}/cancel` | Token | Batalkan pesanan |
| 28 | DELETE | `/api/orders/{id}` | Token | Hapus pesanan (harus `cancelled`) |
| 29 | PATCH | `/api/orders/{id}/status` | Admin | Ubah status pesanan |

Daftar ini bisa dicek ulang kapan saja dengan:

```bash
php artisan route:list --path=api
```

---

## 8. Cara menguji dengan Postman

### Cara A — lewat tampilan Postman (yang biasa dipakai)

1. Buka **Postman** → **Import**
2. Masukkan dua file dari folder `docs/postman/`:
   - `Kantinku-UBBG-API.postman_collection.json`
   - `Kantinku-UBBG-Local.postman_environment.json`
3. Di kanan atas, pilih environment **Kantinku UBBG - Local**
4. Klik kanan nama collection → **Run collection** → **Run**
5. Semua request berjalan otomatis. Request **Login admin** akan menyimpan token,
   jadi request berikutnya tidak perlu tempel token manual.

### Cara B — lewat terminal (lebih cepat)

```bash
npm install
npm run test:api
```

Hasilnya berupa tabel: berapa request, berapa assertion, berapa yang gagal.

### Hasil terakhir

| Keterangan | Nilai |
|---|---|
| Request dijalankan | 38 |
| Assertion (pemeriksaan) | 120 |
| Gagal | **0** |
| Rata-rata waktu jawaban | 25 ms |

Laporan lengkap + screenshot: [`docs/testing_results.md`](docs/testing_results.md)

---

## 9. Tiga hal yang paling sering bikin bingung

| Gejala | Penyebab | Solusi |
|---|---|---|
| Semua request balas **401** | Token belum terisi | Jalankan folder `01 - Autentikasi` dulu, mulai dari **Login admin** |
| `php artisan migrate` gagal | File database belum ada | `touch database/database.sqlite` lalu ulangi |
| Error `could not find driver` | Ekstensi `pdo_sqlite` mati | Aktifkan di `php.ini`, atau pakai MySQL (isi `DB_*` di `.env`) |
| Halaman putih / 500 | `.env` belum ada atau `APP_KEY` kosong | `cp .env.example .env` lalu `php artisan key:generate` |

---

## 10. Teknologi yang dipakai

| Bagian | Alat | Kenapa dipakai |
|---|---|---|
| Framework | Laravel 13 | Standar industri PHP, banyak dokumentasi |
| Login token | Laravel Sanctum | Ringan, cocok untuk API & aplikasi mobile |
| Database | SQLite (dev) / MySQL (produksi) | SQLite tidak perlu instalasi |
| Validasi | Form Request | Aturan validasi terpisah dari controller, rapi |
| Bentuk jawaban | API Resource | Dijamin tidak membocorkan `password` |
| Pengujian | Postman Collection v2.1 + Newman | Sesuai permintaan tugas |
