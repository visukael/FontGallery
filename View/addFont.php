<?php
// Koneksi ke database
$conn = new mysqli("localhost:8111", "root", "", "dbfont");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil data dari form
$fontName   = $_POST['fontName'];
$category   = $_POST['category'];
$newCategory = $_POST['newCategory'] ?? '';
$style      = $_POST['style'] ?? 'Regular';
$license    = $_POST['license'];
$description= $_POST['description'];
$designer   = $_POST['designer'];
$createdAt  = date('Y-m-d H:i:s');

// Handle upload file
$uploadDir = '../Uploads/Fonts/';
$fontFile = $_FILES['fontFile'];
$filename = basename($fontFile['name']);
$targetPath = $uploadDir . $filename;

if (move_uploaded_file($fontFile['tmp_name'], $targetPath)) {
    // Insert kategori baru kalau pilih custom
    if ($category === 'custom' && !empty($newCategory)) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name, created_at) VALUES (?, ?)");
        $stmt->bind_param("ss", $newCategory, $createdAt);
        $stmt->execute();
        $category_id = $stmt->insert_id;
        $stmt->close();
    } else {
        // Ambil category_id dari kategori yang dipilih
        $stmt = $conn->prepare("SELECT id FROM categories WHERE category_name = ?");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $stmt->bind_result($category_id);
        $stmt->fetch();
        $stmt->close();
    }

    // Simpan font ke database
    $stmt = $conn->prepare("INSERT INTO fonts (font_name, category_id, style, license, description, file_path, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisssss", $fontName, $category_id, $style, $license, $description, $targetPath, $createdAt);
    $stmt->execute();
    $stmt->close();

    echo "Font berhasil ditambahkan!";
} else {
    echo "Upload gagal.";
}

$conn->close();
?>
