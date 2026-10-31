# Microservices

## Komponen

| Service | Tech stack | Database | Port |
|---|---|---|---:|
| Product | Node.js 18, Express | MySQL | 3000 |
| Cart | PHP 8.4, Laravel 12 | PostgreSQL | 3001 |
| Review | Python 3.12, Flask | MongoDB | 3002 |
| Order | PHP 8.4, Laravel 12 | MySQL | 3003 |

## Menjalankan semua service

Jalankan dari root repository:

```sh
docker compose up --build
```

Docker Compose menjalankan Product Service (port 3000), Cart Service
(port 3001), Review Service (port 3002), dan Order Service (port 3003),
beserta database masing-masing. Order Service menjalankan migrasi database
saat container dimulai. Database menggunakan named volumes agar datanya
tetap tersimpan setelah container dihentikan.

## Order Service

Order Service memakai PHP 8.4, Laravel 12, dan MySQL. Konfigurasi koneksi
Cart Service dan Product Service tersedia melalui `CART_SERVICE_URL` dan
`PRODUCT_SERVICE_URL`. Di Docker Compose keduanya diarahkan ke nama service
internal; untuk menjalankan Laravel langsung di komputer, gunakan URL
`localhost` di `order-service/.env`.

Alur order dipisah ke Form Request, Controller, `CheckoutService`, client
HTTP Cart/Product, dan model Eloquent. Pesanan dan item disimpan dalam satu
transaksi database; status pembayaran mengubah `PENDING` menjadi `PAID`.

| Method | Endpoint | Hasil utama |
|---|---|---|
| GET | `/health` | `200`, status service |
| POST | `/orders` | `201`, membuat order dari cart |
| GET | `/orders/{id}` | `200`, detail order; `404` jika tidak ditemukan |
| GET | `/orders/user/{userId}?status=PENDING` | `200`; `422` untuk filter tidak valid |
| PATCH | `/orders/{id}/pay` | `200`; `404` jika tidak ada; `409` jika status bukan `PENDING` |

Checkout menghasilkan `409` jika cart kosong, produk tidak ditemukan, data
cart tidak valid, atau stok tidak mencukupi; `503` jika Cart/Product Service
tidak tersedia; dan `500` untuk kesalahan internal. Validasi request memakai
`422`. Respons error API berisi `message`, `data`, dan `errors`; detail error
internal hanya dikirim saat debug aktif dan selalu dicatat di log.
