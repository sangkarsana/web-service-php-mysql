<?php
// Mengaktifkan pelaporan error untuk pengembangan (non-produksi)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mengatur header untuk JSON dan CORS
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Menangani preflight request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Mengimpor file koneksi database
require_once 'db.php';

// Membuat instance dan koneksi database
$database = new Database();
$db = $database->getConnection();

// Memeriksa koneksi database
if (!$db) {
    sendResponse(['message' => 'Koneksi database gagal'], 500);
}

// Mendapatkan metode permintaan HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Mendapatkan path dari URL dan menghilangkan direktori dasar
$request = $_SERVER['REQUEST_URI'];
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$path = substr(parse_url($request, PHP_URL_PATH), strlen($script_name));
$pathFragments = explode('/', trim($path, '/'));
$resource = isset($pathFragments[0]) ? $pathFragments[0] : null;
$resourceType = isset($pathFragments[1]) ? $pathFragments[1] : null;
$id = isset($pathFragments[2]) ? (int)$pathFragments[2] : null;

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
if ($resource === 'api' && $resourceType === 'books') {
    switch ($method) {
        case 'GET':
            if ($id) {
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
            if ($id) {
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
            if ($id) {
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
