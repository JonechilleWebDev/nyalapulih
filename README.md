# NyalaPulih — Backend Server

Repository backend NyalaPulih menggunakan **PHP Native** dan **MySQL**. Server ini berfungsi sebagai pusat pengelolaan data dan autentikasi yang nantinya digunakan oleh aplikasi mobile Flutter maupun website NyalaPulih.

## 1. Teknologi yang Digunakan

- PHP Native
- MySQL / MariaDB
- PDO (PHP Data Objects)
- REST API
- JSON
- Git & GitHub

## 2. Struktur Folder

```text
NyalaPulih/
├── config/
│   └── database.php
├── api/
│   ├── auth/
│   │   ├── register.php
│   │   ├── login.php
│   │   └── logout.php
│   └── user/
│       └── profile.php
├── middleware/
│   └── auth_middleware.php
├── .gitignore
└── README.md
```

## 3. Persiapan Sebelum Menjalankan Server

Pastikan perangkat sudah memiliki:

- Git
- PHP 8.1 atau versi lebih baru
- Ekstensi PDO dan PDO MySQL
- MySQL atau MariaDB
- Postman, Thunder Client, atau aplikasi sejenis untuk menguji API

Periksa instalasi PHP:

```bash
php -v
```

Periksa ekstensi database:

```bash
php -m | grep -iE 'PDO|pdo_mysql'
```

Pastikan hasilnya menampilkan:

```text
PDO
pdo_mysql
```

Periksa Git:

```bash
git --version
```

## 4. Mengunduh Project

Clone repository NyalaPulih:

```bash
git clone <URL_REPOSITORY>
```

Masuk ke direktori project:

```bash
cd NyalaPulih
```

Pindah ke branch server:

```bash
git switch server
```

Ambil pembaruan terbaru:

```bash
git pull origin server
```

## 5. Konfigurasi Database

Backend NyalaPulih menggunakan MySQL atau MariaDB.

Buat database dengan nama:

```sql
CREATE DATABASE nyalapulih_db;
```

Gunakan database tersebut:

```sql
USE nyalapulih_db;
```

Buat tabel pengguna:

```sql
CREATE TABLE users (
  id_user INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('relawan', 'admin') NOT NULL DEFAULT 'relawan',
  organization VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Buat tabel token autentikasi:

```sql
CREATE TABLE auth_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)
        REFERENCES users(id_user)
        ON DELETE CASCADE
);
```

### Konfigurasi koneksi database

Sesuaikan konfigurasi `config/database.php` dengan MySQL di perangkat masing-masing.

Contoh konfigurasi untuk XAMPP di Linux:

```php
private $host = "127.0.0.1";
private $port = 3306;
private $dbname = "nyalapulih_db";
private $username = "root";
private $password = "";
```

**Penting:**

- Gunakan `127.0.0.1`, bukan `localhost`, untuk koneksi TCP ke MySQL XAMPP dari PHP Development Server.
- Pastikan port MySQL sesuai dengan konfigurasi XAMPP. Port default biasanya `3306`.
- Sesuaikan username dan password dengan konfigurasi MySQL masing-masing.
- Jangan mengunggah password database pribadi ke GitHub.

## 6. Menjalankan PHP Development Server

Backend dapat dijalankan langsung dari folder repository tanpa perlu memindahkan project ke `htdocs`.

Pastikan terminal berada di root project NyalaPulih, kemudian jalankan:

```bash
php -S 127.0.0.1:8000 -t api
```

Keterangan:

- `127.0.0.1` digunakan agar server hanya dapat diakses dari komputer sendiri.
- `8000` merupakan port server PHP.
- `-t api` menjadikan folder `api` sebagai document root sehingga folder internal repository seperti `.git` tidak terekspos melalui HTTP.

Jika berhasil, terminal akan menampilkan pesan bahwa PHP Development Server sudah berjalan.

Biarkan terminal tetap terbuka selama server digunakan.

### Menghentikan server

Tekan:

```text
CTRL + C
```

### Mengakses server

Gunakan alamat:

```text
http://127.0.0.1:8000
```

Karena folder `api` menjadi document root, endpoint tidak perlu lagi menggunakan awalan `/api`.

## 7. Endpoint API

| Method | Endpoint | Keterangan |
|---|---|---|
| POST | `/auth/register.php` | Registrasi pengguna |
| POST | `/auth/login.php` | Login pengguna |
| POST | `/auth/logout.php` | Logout pengguna |
| GET | `/user/profile.php` | Mengambil profil pengguna |

Semua endpoint menggunakan format komunikasi JSON.

### A. Register

**URL:**

```text
POST http://127.0.0.1:8000/auth/register.php
```

Request Body:

```json
{
    "name": "Jona",
    "email": "jona@example.com",
    "password": "password123"
}
```

### B. Login

**URL:**

```text
POST http://127.0.0.1:8000/auth/login.php
```

Request Body:

```json
{
    "email": "jona@example.com",
    "password": "password123"
}
```

Jika berhasil, server akan mengembalikan informasi pengguna dan token autentikasi.

Contoh format respons:

```json
{
    "success": true,
    "message": "Login berhasil",
    "token": "TOKEN_AUTENTIKASI",
    "user": {
        "id": 1,
        "name": "Jona",
        "email": "jona@example.com"
    }
}
```

### C. Logout

**URL:**

```text
POST http://127.0.0.1:8000/auth/logout.php
```

Gunakan token autentikasi pada header:

```text
Authorization: Bearer TOKEN_AUTENTIKASI
```

### D. Profile

**URL:**

```text
GET http://127.0.0.1:8000/user/profile.php
```

Gunakan header autentikasi:

```text
Authorization: Bearer TOKEN_AUTENTIKASI
```

**Catatan:** Endpoint dan format respons di atas merupakan acuan integrasi. Implementasi aktual dapat disesuaikan apabila terdapat perubahan selama pengembangan.

## 8. Cara Menguji API Menggunakan Postman

1. Jalankan MySQL melalui XAMPP.
2. Pastikan database `nyalapulih_db` sudah dibuat.
3. Pastikan konfigurasi `config/database.php` sudah sesuai.
4. Jalankan PHP Development Server.
5. Buka Postman.
6. Pilih method sesuai endpoint, misalnya `POST`.
7. Masukkan URL endpoint.
8. Pilih tab **Body**.
9. Pilih **raw** dan format **JSON**.
10. Masukkan data request.
11. Klik **Send**.
12. Periksa status HTTP dan respons JSON dari server.

Contoh pengujian register:

```text
POST http://127.0.0.1:8000/auth/register.php
```

Body:

```json
{
    "name": "Jona",
    "email": "jona@example.com",
    "password": "password123"
}
```

Jika berhasil, server akan mengembalikan status `201 Created`.

## 9. Mengakses Server dari Perangkat Lain

Jika anggota tim ingin mengakses server dari perangkat lain dalam jaringan Wi-Fi yang sama, jalankan server menggunakan:

```bash
php -S 0.0.0.0:8000 -t api
```

Cari IP lokal komputer yang menjalankan server:

```bash
hostname -I
```

Misalnya IP komputer server adalah `192.168.1.10`.

Anggota tim dapat menggunakan URL:

```text
http://192.168.1.10:8000/auth/register.php
```

Pastikan:

- Kedua perangkat terhubung ke jaringan yang sama.
- Firewall mengizinkan koneksi ke port `8000`.
- Server PHP tetap berjalan.
- Perangkat lain dapat menjangkau komputer server.

Gunakan cara ini hanya untuk pengujian di jaringan tepercaya. PHP Development Server ditujukan untuk pengembangan, bukan deployment produksi.

## 10. Integrasi dengan Aplikasi

Backend NyalaPulih dirancang agar dapat digunakan oleh:

- **Flutter Mobile:** Mengirim request HTTP ke backend untuk autentikasi dan pengelolaan data.
- **Website:** Mengakses backend yang sama melalui endpoint API.

Keduanya menggunakan database dan sistem autentikasi yang sama.

```text
       Flutter Mobile
             |
             |
             v
       NyalaPulih API
             ^
             |
             |
          Website
             |
             v
          MySQL
```

Untuk pengembangan lokal:

- Gunakan `127.0.0.1` jika aplikasi berjalan pada komputer yang sama.
- Gunakan IP lokal komputer server jika aplikasi dijalankan dari perangkat lain.
- Untuk Android Emulator, biasanya gunakan alamat host emulator yang mengarah ke komputer pengembang, misalnya `10.0.2.2` pada Android Studio Emulator.

## 11. Aturan Pengembangan Tim

Agar pengembangan backend tetap teratur:

1. Selalu lakukan `git pull origin server` sebelum mulai bekerja.
2. Jangan mengubah konfigurasi database pribadi anggota lain.
3. Jangan mengunggah password, token, atau informasi rahasia ke GitHub.
4. Gunakan prepared statements melalui PDO untuk query database.
5. Gunakan `password_hash()` dan `password_verify()` untuk pengelolaan password.
6. Pastikan respons API menggunakan format JSON yang konsisten.
7. Lakukan pengujian endpoint sebelum mengirim perubahan.
8. Gunakan branch kerja masing-masing jika mengembangkan fitur baru.
9. Buat Pull Request ke branch `server` sebelum menggabungkan perubahan.

## 12. Status Pengembangan

- [ ] Konfigurasi koneksi database
- [ ] API Register
- [ ] API Login
- [ ] API Logout
- [ ] Middleware autentikasi
- [ ] API Profile
- [ ] Pengujian menggunakan Postman
- [ ] Integrasi Flutter
- [ ] Integrasi Website

## 13. Sistem Role Pengguna

NyalaPulih menggunakan sistem Role-Based Access Control (RBAC) untuk membedakan hak akses pengguna.

| Role | Keterangan |
|---|---|
| `volunteer` | Pengguna yang terdaftar sebagai sukarelawan |
| `admin` | Pengguna dengan hak akses administrator |

### Ketentuan Role

- Setiap pengguna yang mendaftar melalui endpoint Register otomatis mendapatkan role `volunteer`.
- Role `admin` tidak dapat dipilih melalui registrasi publik.
- Penetapan administrator dilakukan oleh administrator yang berwenang atau melalui pengaturan database selama tahap pengembangan.
- Setiap endpoint yang membutuhkan hak administrator wajib melakukan validasi role di backend.

### Mengubah Role Pengguna

Untuk kebutuhan pengembangan, role pengguna dapat diubah melalui query SQL:

```sql
UPDATE users
SET role = 'admin'
WHERE email = 'admin@nyalapulih.com';
```

Mengembalikan akun menjadi sukarelawan:

```sql
UPDATE users
SET role = 'volunteer'
WHERE email = 'admin@nyalapulih.com';
```

Role pengguna juga dikirimkan dalam respons Login agar aplikasi Flutter dan website dapat menyesuaikan akses berdasarkan peran pengguna.

---

**NyalaPulih Backend**

PHP Native · MySQL · REST API

*Repository khusus pengembangan backend server NyalaPulih.*