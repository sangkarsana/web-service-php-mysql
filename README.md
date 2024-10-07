# **Modul Ajar: Integrasi Web Service PHP dengan MySQL**

## **1. Pendahuluan**

Pada sesi ini, Anda akan mempelajari bagaimana menghubungkan web service PHP sederhana dengan database MySQL untuk mengelola data buku. Dengan menggunakan MySQL, data buku akan disimpan secara persisten, memungkinkan operasi CRUD yang lebih efisien dan andal.

---

## **2. Tujuan Pembelajaran**

Setelah menyelesaikan modul ini, peserta diharapkan mampu:

1. Menyiapkan dan mengonfigurasi database MySQL untuk menyimpan data buku.
2. Menghubungkan PHP dengan MySQL menggunakan **PDO (PHP Data Objects)**.
3. Mengimplementasikan operasi CRUD yang berinteraksi langsung dengan database.
4. Mengamankan aplikasi dengan menggunakan prepared statements untuk mencegah SQL Injection.
5. Menguji web service yang telah diintegrasikan dengan database menggunakan cURL atau alat pengujian API seperti Postman.

---

## **3. Prasyarat**

Sebelum memulai, pastikan Anda memiliki:

- **XAMPP** atau paket serupa yang mencakup **Apache** dan **MySQL**.
- **Editor Teks** seperti Visual Studio Code, Sublime Text, atau Notepad++.
- **cURL** atau **Postman** untuk menguji API.
- **Pengetahuan Dasar PHP** dan **MySQL**.

---

## **4. Alat dan Bahan**

### **4.1. Perangkat Lunak yang Diperlukan**

1. **XAMPP**: Paket perangkat lunak yang mencakup Apache, PHP, dan MySQL.
   - **Unduh**: [XAMPP Download](https://www.apachefriends.org/download.html)
2. **cURL**: Alat baris perintah untuk mentransfer data dengan URL.
   - Biasanya sudah terpasang secara default di Linux dan macOS. Untuk Windows, bisa diunduh dari [cURL Download](https://curl.se/windows/).
3. **Postman** (Opsional): Alat GUI untuk menguji API.
   - **Unduh**: [Postman Download](https://www.postman.com/downloads/)
4. **PHP dan MySQL**: Termasuk dalam paket XAMPP.

### **4.2. Instalasi dan Persiapan**

1. **Instal XAMPP**:
   - Unduh dan instal XAMPP sesuai dengan sistem operasi Anda.
   - Buka **XAMPP Control Panel** dan **start** modul **Apache** dan **MySQL**.
2. **Akses phpMyAdmin**:
   - Buka browser dan akses: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - phpMyAdmin adalah antarmuka web untuk mengelola database MySQL.

---

## **5. Menyiapkan Database MySQL**

### **5.1. Membuat Database dan Tabel**

1. **Buka phpMyAdmin**:
   - Akses [http://localhost/phpmyadmin](http://localhost/phpmyadmin)

2. **Membuat Database Baru**:
   - Klik tab **"Databases"**.
   - Di bawah **"Create database"**, masukkan nama database, misalnya `webservice_db`.
   - Pilih **"utf8_general_ci"** sebagai collation untuk mendukung karakter Unicode.
   - Klik **"Create"**.

3. **Membuat Tabel `books`**:
   - Setelah database dibuat, klik nama database tersebut (`webservice_db`) di panel sebelah kiri.
   - Klik tab **"SQL"** dan masukkan perintah berikut untuk membuat tabel `books`:

     ```sql
     CREATE TABLE books (
         id INT AUTO_INCREMENT PRIMARY KEY,
         title VARCHAR(255) NOT NULL,
         author VARCHAR(255) NOT NULL
     );
     ```

   - Klik **"Go"** untuk menjalankan perintah.

4. **Memasukkan Data Awal (Opsional)**:
   - Anda dapat memasukkan data awal ke tabel `books` untuk menguji web service.
   - Klik tab **"Insert"** dan tambahkan beberapa entri buku.

   **Contoh Data:**

   | title                        | author       |
   |------------------------------|--------------|
   | Belajar PHP                  | John Doe     |
   | Web Development dengan PHP    | Jane Smith   |

---

## **6. Menghubungkan PHP dengan MySQL menggunakan PDO**

**PDO (PHP Data Objects)** adalah ekstensi PHP yang menyediakan antarmuka konsisten untuk mengakses berbagai database, termasuk MySQL. Menggunakan PDO memiliki keuntungan seperti kemudahan dalam penggunaan prepared statements untuk mencegah SQL Injection.

### **6.1. Membuat File Koneksi Database**

1. **Buat File `db.php`**:
   - Di dalam direktori `php-web-service`, buat file baru bernama `db.php`.
   - Tambahkan kode berikut untuk mengatur koneksi ke database MySQL:

     ```php
     <?php
     // db.php

     class Database {
         private $host = "localhost";
         private $db_name = "webservice_db";
         private $username = "root";
         private $password = "";
         public $conn;

         public function getConnection(){
             $this->conn = null;

             try{
                 $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                       $this->username, 
                                       $this->password);
                 $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             }
             catch(PDOException $exception){
                 echo "Connection error: " . $exception->getMessage();
             }

             return $this->conn;
         }
     }
     ?>
     ```

   **Penjelasan:**
   - **Class Database**: Mengatur parameter koneksi dan metode untuk mendapatkan koneksi.
   - **PDO**: Menggunakan PDO untuk menghubungkan PHP dengan MySQL.
   - **Error Handling**: Mengatur PDO untuk melempar exception jika terjadi kesalahan.

2. **Mengonfigurasi Kredensial Database**:
   - Pastikan kredensial (`username` dan `password`) sesuai dengan konfigurasi MySQL Anda.
   - Secara default, XAMPP menggunakan `username: root` dan `password: ""` (kosong).

### **6.2. Mengupdate `index.php` untuk Menggunakan Database**

1. **Mengimpor Koneksi Database**:
   - Di awal file `index.php`, tambahkan `require_once` untuk mengimpor `db.php`.

     ```php
     <?php
     // Mengaktifkan pelaporan error untuk pengembangan
     error_reporting(E_ALL);
     ini_set('display_errors', 1);

     // Mengatur header untuk JSON dan CORS
     header("Content-Type: application/json; charset=UTF-8");
     header("Access-Control-Allow-Origin: *");
     header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
     header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

     // Mengimpor file koneksi database
     require_once 'db.php';

     // Membuat instance dan koneksi database
     $database = new Database();
     $db = $database->getConnection();

     // Mendapatkan metode permintaan HTTP
     $method = $_SERVER['REQUEST_METHOD'];

     // Mendapatkan path dari URL dan menghilangkan direktori dasar
     $request = $_SERVER['REQUEST_URI'];
     $script_name = dirname($_SERVER['SCRIPT_NAME']);
     $path = substr(parse_url($request, PHP_URL_PATH), strlen($script_name));
     $pathFragments = explode('/', trim($path, '/'));
     $resource = isset($pathFragments[0]) ? $pathFragments[0] : null;
     $id = isset($pathFragments[1]) ? (int)$pathFragments[1] : null;

     // Fungsi untuk mengirim respons JSON
     function sendResponse($data, $status_code = 200) {
         http_response_code($status_code);
         echo json_encode($data);
         exit();
     }

     // Fungsi untuk mendapatkan data input JSON
     function getInput() {
         return json_decode(file_get_contents('php://input'), true);
     }

     // Routing
     if ($resource === 'api' && isset($pathFragments[1]) && $pathFragments[1] === 'books') {
         switch ($method) {
             case 'GET':
                 if (isset($pathFragments[2])) {
                     // Mendapatkan buku berdasarkan ID
                     $stmt = $db->prepare("SELECT * FROM books WHERE id = :id");
                     $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                     $stmt->execute();
                     $book = $stmt->fetch(PDO::FETCH_ASSOC);
                     if ($book) {
                         sendResponse($book);
                     } else {
                         sendResponse(['message' => 'Buku tidak ditemukan'], 404);
                     }
                 } else {
                     // Mendapatkan semua buku
                     $stmt = $db->prepare("SELECT * FROM books");
                     $stmt->execute();
                     $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                     sendResponse($books);
                 }
                 break;

             case 'POST':
                 // Menambahkan buku baru
                 $input = getInput();
                 if (isset($input['title']) && isset($input['author'])) {
                     $stmt = $db->prepare("INSERT INTO books (title, author) VALUES (:title, :author)");
                     $stmt->bindParam(':title', $input['title']);
                     $stmt->bindParam(':author', $input['author']);
                     if ($stmt->execute()) {
                         $new_id = $db->lastInsertId();
                         $new_book = [
                             'id' => (int)$new_id,
                             'title' => $input['title'],
                             'author' => $input['author']
                         ];
                         sendResponse($new_book, 201);
                     } else {
                         sendResponse(['message' => 'Gagal menambahkan buku'], 500);
                     }
                 } else {
                     sendResponse(['message' => 'Data tidak lengkap'], 400);
                 }
                 break;

             case 'PUT':
                 if (isset($pathFragments[2])) {
                     // Memperbarui buku berdasarkan ID
                     $input = getInput();
                     // Cek apakah buku ada
                     $stmt = $db->prepare("SELECT * FROM books WHERE id = :id");
                     $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                     $stmt->execute();
                     $book = $stmt->fetch(PDO::FETCH_ASSOC);
                     if ($book) {
                         // Update data
                         $title = isset($input['title']) ? $input['title'] : $book['title'];
                         $author = isset($input['author']) ? $input['author'] : $book['author'];
                         $stmt = $db->prepare("UPDATE books SET title = :title, author = :author WHERE id = :id");
                         $stmt->bindParam(':title', $title);
                         $stmt->bindParam(':author', $author);
                         $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                         if ($stmt->execute()) {
                             $updated_book = [
                                 'id' => (int)$id,
                                 'title' => $title,
                                 'author' => $author
                             ];
                             sendResponse($updated_book);
                         } else {
                             sendResponse(['message' => 'Gagal memperbarui buku'], 500);
                         }
                     } else {
                         sendResponse(['message' => 'Buku tidak ditemukan'], 404);
                     }
                 } else {
                     sendResponse(['message' => 'ID tidak disediakan'], 400);
                 }
                 break;

             case 'DELETE':
                 if (isset($pathFragments[2])) {
                     // Menghapus buku berdasarkan ID
                     $stmt = $db->prepare("DELETE FROM books WHERE id = :id");
                     $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                     if ($stmt->execute()) {
                         if ($stmt->rowCount() > 0) {
                             sendResponse(['message' => 'Buku dihapus']);
                         } else {
                             sendResponse(['message' => 'Buku tidak ditemukan'], 404);
                         }
                     } else {
                         sendResponse(['message' => 'Gagal menghapus buku'], 500);
                     }
                 } else {
                     sendResponse(['message' => 'ID tidak disediakan'], 400);
                 }
                 break;

             default:
                 sendResponse(['message' => 'Metode tidak diizinkan'], 405);
         }
     } else {
         sendResponse(['message' => 'Endpoint tidak ditemukan'], 404);
     }
     ?>
     ```

   **Penjelasan Perubahan:**
   - **Menggunakan PDO** untuk koneksi dan operasi database.
   - **Prepared Statements** digunakan untuk mencegah SQL Injection.
   - **CRUD Operations** kini berinteraksi langsung dengan database:
     - **GET**: Mengambil semua buku atau buku berdasarkan ID.
     - **POST**: Menambahkan buku baru ke database.
     - **PUT**: Memperbarui data buku berdasarkan ID.
     - **DELETE**: Menghapus buku berdasarkan ID.

---

## **7. Mengonfigurasi Apache untuk Mendukung Routing**

Pastikan konfigurasi Apache Anda mendukung URL rewriting sehingga permintaan dapat diarahkan ke `index.php` dengan benar.

### **7.1. Mengaktifkan Modul `mod_rewrite`**

1. **Buka File Konfigurasi Apache:**
   - Lokasi default di XAMPP (Windows): `C:\xampp\apache\conf\httpd.conf`
   - Lokasi default di macOS: `/Applications/XAMPP/etc/httpd.conf`

2. **Cari Baris yang Memuat `mod_rewrite`:**

   ```
   #LoadModule rewrite_module modules/mod_rewrite.so
   ```

3. **Hilangkan Tanda `#` untuk Mengaktifkan Modul:**

   ```
   LoadModule rewrite_module modules/mod_rewrite.so
   ```

4. **Izinkan `Override` di Direktori Proyek:**
   - Cari blok `<Directory "C:/xampp/htdocs">` (Windows) atau `<Directory "/Applications/XAMPP/htdocs">` (macOS).
   - Pastikan `AllowOverride` diset ke `All`:

     ```apache
     <Directory "C:/xampp/htdocs">
         Options Indexes FollowSymLinks Includes ExecCGI
         AllowOverride All
         Require all granted
     </Directory>
     ```

5. **Simpan Perubahan dan Restart Apache:**
   - Buka **XAMPP Control Panel**.
   - Klik **Stop** pada Apache, tunggu beberapa detik, lalu klik **Start** lagi.

### **7.2. Memastikan File `.htaccess` Benar**

Pastikan file `.htaccess` berada di dalam direktori `php-web-service` dan berisi aturan berikut:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Penjelasan:**
- **RewriteEngine On**: Mengaktifkan engine rewrite.
- **RewriteCond**: Kondisi untuk menghindari rewrite jika file atau direktori ada.
- **RewriteRule**: Mengarahkan semua permintaan ke `index.php` sambil mempertahankan query string.

---

## **8. Menguji Web Service yang Terintegrasi dengan MySQL**

Setelah semua konfigurasi selesai, lakukan pengujian untuk memastikan web service Anda berfungsi dengan baik.

### **8.1. Menguji Operasi CRUD dengan cURL**

#### **a. Mendapatkan Semua Buku (GET)**

**Perintah cURL:**

```bash
curl -X GET http://localhost/php-web-service/api/books
```

**Expected Response:**

```json
[
    {
        "id": 1,
        "title": "Belajar PHP",
        "author": "John Doe"
    },
    {
        "id": 2,
        "title": "Web Development dengan PHP",
        "author": "Jane Smith"
    }
]
```

#### **b. Mendapatkan Buku Berdasarkan ID (GET)**

Misalnya, untuk mendapatkan buku dengan ID 1.

**Perintah cURL:**

```bash
curl -X GET http://localhost/php-web-service/api/books/1
```

**Expected Response:**

```json
{
    "id": 1,
    "title": "Belajar PHP",
    "author": "John Doe"
}
```

#### **c. Menambahkan Buku Baru (POST)**

**Perintah cURL (dalam Satu Baris):**

```bash
curl -X POST http://localhost/php-web-service/api/books -H "Content-Type: application/json" -d "{\"title\": \"Framework PHP Terbaik\", \"author\": \"Alice Johnson\"}"
```

**Atau dalam Beberapa Baris dengan Caret (`^`):**

```bash
curl -X POST http://localhost/php-web-service/api/books ^
     -H "Content-Type: application/json" ^
     -d "{\"title\": \"Framework PHP Terbaik\", \"author\": \"Alice Johnson\"}"
```

**Expected Response:**

```json
{
    "id": 3,
    "title": "Framework PHP Terbaik",
    "author": "Alice Johnson"
}
```

#### **d. Memperbarui Buku (PUT)**

Misalnya, memperbarui judul buku dengan ID 1.

**Perintah cURL (dalam Satu Baris):**

```bash
curl -X PUT http://localhost/php-web-service/api/books/1 -H "Content-Type: application/json" -d "{\"title\": \"Belajar PHP Dasar\"}"
```

**Expected Response:**

```json
{
    "id": 1,
    "title": "Belajar PHP Dasar",
    "author": "John Doe"
}
```

#### **e. Menghapus Buku (DELETE)**

Misalnya, menghapus buku dengan ID 2.

**Perintah cURL:**

```bash
curl -X DELETE http://localhost/php-web-service/api/books/2
```

**Expected Response:**

```json
{
    "message": "Buku dihapus"
}
```

### **8.2. Menguji Operasi CRUD dengan Postman (Opsional)**

Jika Anda lebih suka menggunakan **Postman**, berikut adalah langkah-langkah untuk menguji API:

1. **Buka Postman** dan buat request baru.
2. **Pilih Metode HTTP** sesuai operasi yang ingin diuji (GET, POST, PUT, DELETE).
3. **Masukkan URL Endpoint**:
   - Contoh: `http://localhost/php-web-service/api/books`
4. **Atur Headers** (untuk POST dan PUT):
   - Key: `Content-Type`
   - Value: `application/json`
5. **Masukkan Body** (untuk POST dan PUT):
   - Pilih **Raw** dan **JSON**.
   - Contoh untuk POST:

     ```json
     {
         "title": "Framework PHP Terbaik",
         "author": "Alice Johnson"
     }
     ```

6. **Klik Send** dan periksa respons yang diterima.

---

## **9. Troubleshooting**

Jika Anda mengalami masalah saat mengintegrasikan web service dengan MySQL, berikut adalah beberapa langkah untuk mendiagnosis dan memperbaikinya.

### **9.1. Memeriksa Koneksi Database**

1. **Pastikan MySQL Berjalan:**
   - Buka **XAMPP Control Panel** dan pastikan modul **MySQL** sedang berjalan.
2. **Cek Kredensial Database:**
   - Pastikan `username`, `password`, dan `db_name` di file `db.php` sesuai dengan konfigurasi database Anda.
3. **Verifikasi Koneksi:**
   - Tambahkan kode berikut di awal `index.php` untuk memastikan koneksi berhasil:

     ```php
     <?php
     // Setelah require_once 'db.php';
     if (!$db) {
         sendResponse(['message' => 'Koneksi database gagal'], 500);
     }
     ?>
     ```

### **9.2. Memeriksa File `.htaccess` dan Routing**

1. **Pastikan File `.htaccess` Ada:**
   - File `.htaccess` harus berada di dalam direktori `php-web-service` dengan isi yang benar.
2. **Cek URL Endpoint:**
   - Pastikan URL yang diakses sesuai dengan struktur routing yang diatur di `index.php`.

### **9.3. Memeriksa Log Error PHP dan Apache**

1. **Aktifkan Error Reporting:**
   - Pastikan baris berikut ada di awal `index.php`:

     ```php
     error_reporting(E_ALL);
     ini_set('display_errors', 1);
     ```

2. **Periksa Log Error Apache:**
   - Lokasi log di XAMPP (Windows): `C:\xampp\apache\logs\error.log`
   - Cari pesan error yang relevan dengan waktu Anda menjalankan perintah `curl` atau mengakses API.

### **9.4. Memeriksa Sintaks SQL dan Prepared Statements**

1. **Pastikan Sintaks SQL Benar:**
   - Cek kembali query SQL di `index.php` untuk memastikan tidak ada kesalahan sintaks.
2. **Cek Penggunaan Parameter:**
   - Pastikan semua parameter di-bind dengan benar menggunakan `bindParam`.

### **9.5. Menggunakan Alat Debugging**

1. **Tambahkan `var_dump` atau `print_r`:**
   - Gunakan untuk memeriksa nilai variabel sebelum dan sesudah operasi database.
2. **Gunakan Postman:**
   - Lebih mudah untuk melihat dan mengelola permintaan dan respons API.

---

## **10. Pertimbangan Tambahan**

### **10.1. Penggunaan Prepared Statements**

Menggunakan **prepared statements** adalah praktik terbaik untuk mencegah **SQL Injection**. Pastikan setiap query yang menerima input dari pengguna menggunakan prepared statements.

**Contoh:**

```php
$stmt = $db->prepare("SELECT * FROM books WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
```

### **10.2. Validasi Input**

Sebelum melakukan operasi database, validasi input yang diterima untuk memastikan data yang dimasukkan sesuai dengan yang diharapkan.

**Contoh:**

```php
if (empty($input['title']) || empty($input['author'])) {
    sendResponse(['message' => 'Data tidak lengkap'], 400);
}
```

### **10.3. Menangani Kesalahan dengan Baik**

Pastikan untuk menangani kesalahan dengan memberikan respons yang informatif namun tidak mengungkapkan informasi sensitif.

**Contoh:**

```php
try {
    // Operasi database
} catch (PDOException $e) {
    sendResponse(['message' => 'Terjadi kesalahan pada server'], 500);
}
```

### **10.4. Struktur Proyek yang Lebih Baik**

Untuk proyek yang lebih besar, pertimbangkan untuk menggunakan **MVC (Model-View-Controller)** atau framework PHP seperti **Laravel**, **Symfony**, atau **Slim** yang menyediakan struktur dan fitur lebih lengkap.

---

## **11. Kesimpulan**

Mengintegrasikan web service PHP dengan MySQL memungkinkan pengelolaan data yang lebih efisien dan persisten. Dengan menggunakan **PDO** dan **prepared statements**, Anda dapat membangun API yang aman dan andal untuk aplikasi web modern. Pastikan untuk selalu mengamankan aplikasi dengan validasi input dan penanganan kesalahan yang baik.

---

## **12. Referensi dan Bacaan Tambahan**

1. **PHP Official Documentation - PDO:** [https://www.php.net/manual/en/book.pdo.php](https://www.php.net/manual/en/book.pdo.php)
2. **W3Schools - PHP MySQL CRUD:** [https://www.w3schools.com/php/php_mysql_crud.asp](https://www.w3schools.com/php/php_mysql_crud.asp)
3. **MDN Web Docs - Using Fetch:** [https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch)
4. **RFC 8259 - The JavaScript Object Notation (JSON) Data Interchange Format:** [https://tools.ietf.org/html/rfc8259](https://tools.ietf.org/html/rfc8259)
5. **Postman Learning Center - Introduction to JSON:** [https://learning.postman.com/docs/sending-requests/requests/#json-data](https://learning.postman.com/docs/sending-requests/requests/#json-data)

---

## **13. Latihan dan Penugasan**

### **13.1. Latihan**

1. **Membuat dan Menguji API untuk Mengelola Data Buku:**
   - Tambahkan fitur pencarian buku berdasarkan judul atau pengarang.
   - Implementasikan paginasi untuk daftar buku yang panjang.

2. **Integrasi Database Lebih Lanjut:**
   - Tambahkan kolom baru ke tabel `books`, seperti `tahun_terbit` atau `genre`.
   - Update operasi CRUD untuk mengelola kolom tambahan tersebut.

### **13.2. Penugasan**

1. **Buat Web Service untuk Manajemen User:**
   - Buat tabel `users` di database dengan kolom `id`, `username`, `password`, dan `email`.
   - Implementasikan operasi CRUD untuk user dengan autentikasi sederhana.

2. **Dokumentasi API:**
   - Buat dokumentasi lengkap untuk API yang telah dibuat, termasuk endpoint, metode HTTP, parameter, dan contoh respons.
   - Gunakan alat seperti **Swagger** atau **API Blueprint** untuk membuat dokumentasi yang interaktif.

---

**Selamat Belajar dan Berkreasi!**

