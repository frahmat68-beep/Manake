# Project Architecture (Manake Rental)

Dokumen ini untuk konteks cepat developer/agent yang baru masuk repo.

## Stack

- Backend: Laravel 12, PHP 8.5
- Frontend: Blade + Vite (Tailwind + JS)
- DB: PostgreSQL (default `.env` sekarang mengarah ke Supabase)
- Payment: Midtrans Snap
- PDF: `barryvdh/laravel-dompdf`

## Core Domain

- `Category`: kategori alat.
- `Equipment`: item yang disewa, stok + status + foto.
- `Order`: transaksi sewa, status pembayaran + status operasional.
- `OrderItem`: item per order, qty, harga, tanggal sewa per item.
- `Payment`: log pembayaran utama dan tambahan (damage fee).
- `Profile`: data profil wajib user untuk checkout aman.
- `SiteSetting`/`SiteContent`/`SiteMedia`: CMS copywriting + media website.

## Public/User/Admin Flows

- Public:
  - Home `/` via `CategoryController@home`
  - Catalog, category, product detail, availability board
- User:
  - Cart -> Checkout -> Midtrans -> Booking history/detail -> Invoice
  - Route invoice:
    - HTML signed: `account.orders.receipt`
    - PDF signed: `account.orders.receipt.pdf`
- Admin:
  - Login admin, dashboard, CRUD kategori/alat, order ops, user tools, CMS.

## Invoice Architecture

- HTML invoice view: `resources/views/account/orders/receipt.blade.php`
- PDF invoice view: `resources/views/account/orders/receipt-pdf.blade.php`
- Controller:
  - `OrderController@receipt` untuk halaman invoice.
  - `OrderController@receiptPdf` untuk PDF download, support `?inline=1` untuk preview modal.
- Modal preview invoice (order detail + booking history) sekarang prioritas pakai URL PDF inline agar tampilan preview sama dengan file download.

## Internationalization

- Primary locale: `id`
- Secondary locale: `en`
- Key files:
  - `resources/lang/id/ui.php`
  - `resources/lang/en/ui.php`
  - `resources/lang/en.json` untuk translasi string bebas.

## Performance Notes

- `schema_table_exists_cached` / `schema_column_exists_cached` dipakai luas untuk guard schema tanpa query metadata berulang.
- Home page (`CategoryController@home`) sudah dioptimasi:
  - agregasi statistik user jadi 1 query summary.
  - cache singkat untuk ready products dan guest rental snapshot.
- Development disarankan tanpa Xdebug:
  - `composer dev` sudah menjalankan `env XDEBUG_MODE=off ...`.

## Media/Image Notes

- Upload media memakai disk `public`.
- Wajib ada symlink `public/storage -> storage/app/public`.
- `composer dev` sekarang otomatis menjalankan:
  - `php artisan storage:link --force`
- Jika foto tidak muncul, cek symlink dulu (`readlink public/storage`).

## Files to Read First

- Routes: `routes/web.php`
- Checkout/Payment:
  - `app/Http/Controllers/CheckoutController.php`
  - `app/Http/Controllers/PaymentController.php`
- Order + invoice:
  - `app/Http/Controllers/OrderController.php`
  - `resources/views/account/orders/show.blade.php`
  - `resources/views/account/orders/receipt.blade.php`
  - `resources/views/account/orders/receipt-pdf.blade.php`
- Homepage performance:
  - `app/Http/Controllers/CategoryController.php`
- Global helper/caching:
  - `app/helpers.php`
