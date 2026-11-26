# Kantinku UBBG API

Backend API pemesanan kantin kampus (Laravel 13 + Sanctum) untuk tugas proyek pertemuan 9.

## Anggota Kelompok

| Nama | NIM |
|---|---|
| Miftahul Jannah | 23210047 |
| Susilawati | 23210126 |
| Fazil Ikhram | 23210077 |
| Arzan Amin | 23210108 |
| Mirza Firdaus | 23210013 |

## Fitur

- Autentikasi token (register, login, profil, ubah password, logout)
- Katalog kategori dan menu (publik) + CRUD admin
- Pemesanan: buat pesanan, tambah/ubah/hapus item, ubah status, batalkan, struk, ringkasan
- Response seragam `{status, message, data}` + `meta` pagination
- Validasi lewat Form Request, output lewat API Resource (tanpa data sensitif)

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

API berjalan di `http://127.0.0.1:8000/api`.

## Akun Seeder

| Role | Email | Password |
|---|---|---|
| Admin | `admin@kantinku.test` | `password123` |
| Customer | `susilawati@kantinku.test` | `password123` |

## Dokumentasi

- `docs/postman/` — Postman Collection v2.1 + environment lokal
- `docs/testing_results.md` — 10 test wajib + screenshot bukti
- `php artisan route:list --path=api` — daftar endpoint aktif (29 route)

## Menjalankan Test API

```bash
npm install
npm run test:api          # via Newman (CLI)
npm run test:api:report   # hasil + report HTML di docs/screenshots/
```

Atau import kedua file di `docs/postman/` ke Postman, pilih environment
`Kantinku UBBG - Local`, lalu Run Collection.
