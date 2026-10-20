# Dokumentasi Skenario Uji Manual: Autentikasi, Otorisasi, dan RBAC

Dokumen ini berisi rangkaian skenario pengujian manual untuk memvalidasi mekanisme login, hak akses (*role*), serta pembatasan halaman pada aplikasi Simple POS.

## 1. Peran Admin (`admin@pos.test`)
* **Langkah Pengujian:**
  1. Jalankan server lokal (`php artisan serve` dan `npm run dev`).
  2. Buka halaman `/login` dan masuk menggunakan email `admin@pos.test` dengan kata sandi `password`[cite: 8, 11].
  3. Akses halaman `/products` atau `/categories` melalui navigasi maupun langsung dari *address bar*[cite: 12, 13].
* **Hasil yang Diharapkan:** 
  * Pengguna berhasil masuk, menu navigasi admin lengkap, dan halaman Produk serta Kategori dapat diakses serta dikelola secara normal[cite: 11, 12, 13, 15].

## 2. Peran Kasir (`kasir@pos.test`)
* **Langkah Pengujian:**
  1. Keluar (*logout*) dari sesi sebelumnya, lalu login menggunakan email `kasir@pos.test` dengan kata sandi `password`[cite: 8, 11, 15].
  2. Buka halaman `/pos` untuk melakukan transaksi[cite: 12, 15].
  3. Coba akses paksa URL `/products` atau `/categories` langsung dari *address bar* browser[cite: 13, 15].
* **Hasil yang Diharapkan:** 
  * Halaman kasir (`/pos`) tampil dan berfungsi normal[cite: 15]. 
  * Saat mencoba membuka halaman `/products`, sistem menolak akses dan menampilkan halaman error `403 Forbidden` ("Anda tidak memiliki akses untuk halaman ini.")[cite: 12, 13, 15].

## 3. Peran Manager (`manager@pos.test`)
* **Langkah Pengujian:**
  1. Keluar (*logout*), lalu login menggunakan email `manager@pos.test` dengan kata sandi `password`.
  2. Buka halaman riwayat transaksi (`/transactions`).
  3. Coba akses halaman produk (`/products`) melalui *address bar*.
  4. Coba akses halaman kasir (`/pos`) melalui *address bar*.
* **Hasil yang Diharapkan:** 
  * Manager diizinkan membuka halaman riwayat transaksi (`/transactions`) dan dapat melihat seluruh transaksi (semua kasir) di dashboard.
  * Akses ke halaman manajemen produk (`/products`) ditolak dengan error `403 Forbidden`.
  * Akses ke halaman kasir POS (`/pos`) juga ditolak dengan error `403 Forbidden` (manager tidak menjual).

## 4. Pengguna Belum Login (Tamu / Guest)
* **Langkah Pengujian:**
  1. Pastikan kondisi browser bersih/tidak memiliki sesi aktif.
  2. Coba akses langsung URL `/products` atau `/pos` dari *address bar*[cite: 13, 15].
* **Hasil yang Diharapkan:** 
  * Sistem secara otomatis mengarahkan (*redirect*) pengguna ke halaman `/login`[cite: 13, 15].