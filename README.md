# 📖 Sistem Penilaian Munaqosyah & Cetak Syahadah RTQ Utsmaniyyah
### Lajnah Muroqobah Yanbu'a Kabupaten Lampung Barat

Aplikasi web modern berbasis client-side untuk manajemen peserta ujian munaqosyah, penilaian evaluasi 9 mata pelajaran Al-Qur'an (Metode Yanbu'a), kalkulasi peringkat/rekapitulasi nilai otomatis, serta pencetakan **Syahadah (Ijazah)** dan **Transkrip Nilai** resmi berstandar A4 siap cetak.

Aplikasi ini dikembangkan dari format lembar kerja Excel (`aplikasi.xlsx`) menjadi aplikasi website yang interaktif, responsif, dan mudah di-hosting secara gratis (misalnya melalui GitHub Pages) maupun dijalankan langsung melalui web server lokal XAMPP.

---

## 🌟 Fitur Utama

1. **Dashboard & Statistik Interaktif**:
   - Ringkasan total santri, jumlah kelulusan (berdasarkan KKM), santri belum lulus, dan rata-rata nilai.
   - Peringkat 3 Besar Santri Terbaik (*Podium Juara 1, 2, dan 3*).
   - Grafik distribusi capaian rata-rata 9 mata ujian.

2. **Manajemen Data Santri (CRUD)**:
   - Tambah, edit, dan hapus data santri munaqosyah.
   - Identitas lengkap: Nama, TTL, Jenis Kelamin (Bin/Binti otomatis), Nama Orang Tua, Nomor Induk, Nomor Ujian, Nomor Syahadah, dan Alamat.
   - Pencarian cerdas dan pemfilteran instan.

3. **Penilaian Munaqosyah (9 Mata Ujian Standar Yanbu'a)**:
   - 1. *Tartil & Fashohah*
   - 2. *Ghoroibul Qur'an*
   - 3. *Ilmu Tajwid*
   - 4. *Tauhid Dasar*
   - 5. *Praktik Wudlu*
   - 6. *Praktik Sholat (Fasolatan)*
   - 7. *Hafalan Surat Pendek*
   - 8. *Hafalan Do'a Harian*
   - 9. *Kitabah (Menulis Kaligrafi/Huruf Al-Qur'an)*
   - Perhitungan total nilai, nilai rata-rata, dan status kelulusan secara *real-time*.

4. **Rekapitulasi Nilai & Rangking Otomatis**:
   - Peringkat/juara otomatis dihitung berdasarkan perolehan total nilai tertinggi.
   - Cetak lembar rekapitulasi nilai (*A4 Landscape*) lengkap dengan tanda tangan ketua panitia.
   - Ekspor data ke format **Excel (.xlsx)** dan **CSV**.

5. **Pencetakan Dokumen Resmi (Siap Cetak / Print to PDF A4)**:
   - **Syahadah Depan**: Format ijazah berkop kaligrafi Yanbu'a resmi, ornamen islami elegan, nomor syahadah dinamis, dan titimangsa masehi & hijriyah.
   - **Syahadah Belakang (Transkrip Nilai)**: Tabel nilai angka dan **ejaan huruf terbilang otomatis** (contoh: 85 -> *"Delapan Puluh Lima"*), tanda tangan Kepala RTQ dan Tim Penguji.
   - **Formulir Pendaftaran Ujian**: Biodata lengkap santri untuk arsip administrasi.
   - Fitur **Cetak Masal (*Batch Print*)** untuk mencetak seluruh syahadah santri sekaligus.

6. **Penyimpanan & Cadangan Data (Backup & Restore)**:
   - Menggunakan penyimpanan browser lokal (*LocalStorage*), data aman tidak hilang saat browser ditutup.
   - Fitur *Backup Data (Unduh JSON)* dan *Restore Data (Unggah JSON)*.

---

## 🚀 Cara Menjalankan di Lokal (XAMPP)

1. Pastikan folder proyek ini berada di dalam direktori htdocs XAMPP:
   ```
   C:\xampp\htdocs\utsmaniyyah
   ```
2. Buka aplikasi **XAMPP Control Panel**, lalu klik **Start** pada modul **Apache**.
3. Buka browser (Chrome, Edge, Firefox), lalu ketikkan alamat:
   ```
   http://localhost/utsmaniyyah
   ```
4. Atau Anda juga bisa langsung membuka file `index.html` dengan klik dua kali (*double click*).

---

## 📤 Cara Push ke GitHub

Ikuti langkah-langkah berikut di terminal (PowerShell / Command Prompt / Git Bash) di folder proyek:

### 1. Inisialisasi Git & Tambahkan File
```bash
git init
git add .
git commit -m "feat: inisialisasi aplikasi web munaqosyah utsmaniyyah"
```

### 2. Hubungkan ke Repositori GitHub Anda
1. Buat repositori baru di akun GitHub Anda (misal namanya: `utsmaniyyah` atau `munaqosyah-utsmaniyyah`).
2. Jalankan perintah berikut (ganti URL dengan URL repositori Anda):
```bash
git branch -M main
git remote add origin https://github.com/USERNAME_ANDA/NAMA_REPO.git
git push -u origin main
```

---

## 🌐 Cara Mengaktifkan Hosting Gratis di GitHub Pages

Setelah repo berhasil di-push ke GitHub:
1. Masuk ke halaman repositori Anda di GitHub.
2. Klik tab **Settings** (Pengaturan).
3. Di menu sebelah kiri, pilih **Pages**.
4. Pada bagian **Build and deployment** -> **Branch**:
   - Pilih branch `main`
   - Pilih folder `/ (root)`
   - Klik **Save**.
5. Tunggu 1–2 menit, website Anda akan langsung aktif dan bisa diakses online secara gratis di:
   ```
   https://USERNAME_ANDA.github.io/NAMA_REPO/
   ```

---

## 🛠️ Teknologi yang Digunakan
- **HTML5 & Semantic Elements**
- **Modern Vanilla CSS3** (Islamic Emerald Theme, Glassmorphism, CSS Grid & Flexbox, Print Media Queries)
- **Vanilla JavaScript (ES6+)**
- **SheetJS (xlsx.full.min.js)** untuk ekspor data Excel
- **Google Fonts** (Plus Jakarta Sans, Amiri, Cinzel)

---

*Lembaga Pendidikan Al-Qur'an Thoriqoh Yanbu'a - RTQ Utsmaniyyah III Lampung Barat*
