## 2. Color Palette Tokens

AI Agent **WAJIB** menggunakan token warna di bawah ini dan **DILARANG** menggunakan warna hitam pekat (`#000000`) atau putih menyilaukan (`#FFFFFF`) sebagai warna utama aplikasi.

| Token / Peran | Nama Warna | HEX | RGB | Kegunaan |
| :--- | :--- | :--- | :--- | :--- |
| `--bg-main` | **Soft Warm White** | `#F8F7F4` | `rgb(248, 247, 244)` | Latar belakang utama halaman (*canvas*). |
| `--color-primary` | **Soft Clay (Tanah Muda)** | `#DFC7A7` | `rgb(223, 199, 167)` | Kartu (*card*), *banner*, *badge* kategori, dan *highlight* bagian. |
| `--color-accent` | **Earthy Terracotta** | `#BC6C25` | `rgb(188, 108, 37)` | Tombol CTA utama, status aktif, ikon penting, dan indikator aksi. |
| `--color-dark` | **Dark Wood (Cokelat Kayu)** | `#432818` | `rgb(67, 40, 24)` | Seluruh teks utama, judul, ikon navigasi, dan latar belakang *footer*. |

### Warna Turunan (State & Surface)
- **Accent Hover:** `#9E581C` (Gunakan untuk efek *hover* pada tombol terakota).
- **Card Surface (Opsional):** `#FFFFFF` atau `#FDFCFA` (Hanya boleh digunakan untuk elemen *card* yang membutuhkan elevasi tipis di atas `--bg-main`).
- **Muted Text:** `rgba(67, 40, 24, 0.7)` (Untuk teks sekunder, *subtitle*, atau *placeholder*).
- **Border / Divider:** `rgba(67, 40, 24, 0.15)` atau `#DFC7A7`.

---

## 3. Strict UI & Layout Rules (Aturan 60-30-10)

Setiap kali membuat atau memodifikasi komponen UI, patuhi distribusi visual berikut:

1. **60% Soft Warm White (`#F8F7F4`):**
   - Harus mendominasi latar belakang halaman untuk menjaga ruang kosong (*whitespace*) tetap lega dan nyaman dilihat.
2. **30% Tanah Muda (`#DFC7A7`) & Cokelat Kayu (`#432818`):**
   - Gunakan `#DFC7A7` sebagai latar belakang kotak informasi, *highlight* fitur lingkungan, atau pembatas *section*.
   - Gunakan `#432818` untuk semua tipografi agar kontras tetap tinggi dan mudah dibaca, serta untuk bagian *footer*.
3. **10% Terakota (`#BC6C25`):**
   - Batasi penggunaannya hanya pada elemen interaktif berprioritas tinggi (seperti tombol *Submit*, *Beli*, *Mulai*, atau *badge* penting) agar fokus pengguna langsung tertuju ke sana.

---

# NyalaPulih – Folder Architecture

## Project Structure

nyalapulih/
├── assets/
│   ├── images/
│   ├── icons/
│   └── fonts/
│
├── lib/
│   ├── main.dart
│   │
│   ├── app/
│   │   ├── core/
│   │   │   ├── theme/
│   │   │   ├── values/
│   │   │   └── utils/
│   │   │
│   │   ├── routes/
│   │   └── bindings/
│   │
│   ├── data/
│   │   ├── models/
│   │   ├── providers/
│   │   └── repositories/
│   │
│   ├── modules/
│   │   ├── auth/
│   │   │   ├── bindings/
│   │   │   ├── controllers/
│   │   │   └── views/
│   │   │
│   │   ├── home/
│   │   │   ├── bindings/
│   │   │   ├── controllers/
│   │   │   └── views/
│   │   │
│   │   └── profile/
│   │       ├── bindings/
│   │       ├── controllers/
│   │       └── views/
│   │
│   └── shared/
│       ├── widgets/
│       └── services/
│
└── pubspec.yaml
└── agents.md

## Folder Description

| Folder | Function |
|---|---|
| `assets/` | Menyimpan aset aplikasi seperti gambar, ikon, dan font. |
| `main.dart` | Entry point aplikasi untuk inisialisasi Flutter dan GetMaterialApp. |
| `app/core/theme/` | Mengatur tema, warna, typography, dan tampilan global aplikasi. |
| `app/core/values/` | Menyimpan konstanta, teks, dan konfigurasi nilai aplikasi. |
| `app/core/utils/` | Menyimpan fungsi utilitas umum seperti validator dan helper. |
| `app/routes/` | Mengelola routing dan navigasi antarhalaman menggunakan GetX. |
| `app/bindings/` | Mengatur dependency injection global menggunakan GetX. |
| `data/models/` | Mendefinisikan struktur data dan model objek aplikasi. |
| `data/providers/` | Menangani komunikasi dengan layanan eksternal seperti Firebase Authentication dan API. |
| `data/repositories/` | Mengelola logika pengambilan, pengiriman, dan pengolahan data sebelum digunakan Controller. |
| `modules/` | Menyimpan seluruh fitur aplikasi berdasarkan modul atau fungsinya. |
| `modules/auth/` | Menangani fitur autentikasi seperti Login dan Register. |
| `modules/home/` | Menangani halaman utama dan fitur Dashboard. |
| `modules/profile/` | Menangani informasi dan pengaturan profil pengguna. |
| `bindings/` dalam modul | Mendaftarkan Controller dan dependency khusus untuk modul terkait. |
| `controllers/` | Mengelola state, interaksi pengguna, dan logika presentasi menggunakan GetX. |
| `views/` | Menyimpan tampilan UI setiap halaman aplikasi. |
| `shared/widgets/` | Menyimpan reusable UI components seperti Custom Button dan Custom Text Field. |
| `shared/services/` | Menyimpan layanan bersama seperti penyimpanan lokal dan pengelolaan session. |

## AI Agent Development Guidelines

1. Selalu mengikuti struktur folder yang telah ditentukan.
2. Jangan mencampurkan UI, Controller, Repository, dan Provider dalam satu file.
3. Gunakan GetX untuk State Management, Routing, dan Dependency Injection.
4. Gunakan kembali komponen dari `shared/widgets/` jika tersedia.
5. Letakkan fitur baru di dalam `modules/` sesuai tanggung jawabnya.
6. Jangan membuat folder atau file baru di luar arsitektur tanpa alasan teknis yang jelas.