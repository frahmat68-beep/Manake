# PANDUAN OPERASIONAL MANAKE (UNTUK SIDANG TA)

Dokumen ini menjelaskan cara menggunakan, menguji, mengupload ke GitHub, dan mendeploy aplikasi Manake secara manual.

---

## 1. CARA MENJALANKAN DI LAPTOP (LOKAL)

1. **Jalankan Backend Laravel**:
   ```bash
   php artisan serve
   ```
   Akses website di: `http://127.0.0.1:8000`

2. **Jalankan Frontend Vite (Tailwind)**:
   Buka terminal baru, jalankan:
   ```bash
   npm run dev
   ```

3. **Jalankan Uji Kelayakan (Testing)**:
   ```bash
   php artisan test
   ```

---

## 2. CARA UPLOAD KODE KE GITHUB

Jalankan perintah ini secara berurutan di terminal:

```bash
# 1. Cek berkas yang diubah
git status

# 2. Tandai semua berkas untuk di-upload
git add .

# 3. Berikan catatan komit
git commit -m "Revisi sidang: deskripsi perubahan"

# 4. Kirim ke GitHub
git push origin main
```

---

## 3. CARA MENERBITKAN KE WEB LIVE (DEPLOYMENT)

Aplikasi Manake menggunakan alur **CI/CD Otomatis** dengan **Vercel**:

1. Setiap kali Anda melakukan `git push origin main`, GitHub akan memberi tahu Vercel.
2. Vercel akan otomatis menarik kode terbaru dan mendeploy ulang dalam 1-2 menit.
3. Anda bisa memantau status deploy di dashboard [Vercel](https://vercel.com) proyek Anda.

---

## 4. CARA EDIT DATA TANPA CODING (DATABASE)

1. **Melalui Admin Panel**:
   Akses `http://namadomain.com/admin/login` untuk mengelola peralatan, kategori, dan transaksi secara visual.
2. **Melalui Supabase**:
   Masuk ke [Supabase](https://supabase.com), pilih proyek **Manake.ID**, gunakan **Table Editor** untuk mengedit baris data secara langsung seperti Excel.
