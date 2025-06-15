
<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dbfont");
if ($conn->connect_error) die("Connection failed");

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$id = $_GET['id'] ?? ($_POST['id'] ?? '');

if (!isset($_SESSION['user']) || !in_array($_SESSION['role'], ['admin', 'user'])) {
  header("Location: ../View/login.php");
  exit();
}

// Capitalize helper
function capitalizeCategory($name)
{
  return ucwords(strtolower(trim($name)));
}

// Hapus font
if ($action === 'delete' && $id) {
  $stmt = $conn->prepare("DELETE FROM fonts WHERE id = ?");
  $stmt->bind_param("i", $id);
  if ($stmt->execute()) {
    header("Location: ../View/admin/dashboardAdmin.php?msg=deleted");
  } else {
    header("Location: ../View/admin/dashboardAdmin.php?msg=error");
  }
  exit();
}

// Ambil kategori
$categoryInput = '';
if (!empty($_POST['category_combined_select']) && $_POST['category_combined_select'] !== '__add__') {
  $categoryInput = $_POST['category_combined_select'];
} elseif (!empty($_POST['category_combined'])) {
  $categoryInput = capitalizeCategory($_POST['category_combined']);
}

// Dapetin ID kategori
$categoryId = null;
if ($categoryInput !== '') {
  $stmt = $conn->prepare("SELECT id FROM categories WHERE category_name = ?");
  $stmt->bind_param("s", $categoryInput);
  $stmt->execute();
  $stmt->bind_result($existingId);
  if ($stmt->fetch()) {
    $categoryId = $existingId;
    $stmt->close();
  } else {
    $stmt->close();
    $insert = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
    $insert->bind_param("s", $categoryInput);
    $insert->execute();
    $categoryId = $insert->insert_id;
    $insert->close();
  }
}

// Jika tidak berhasil ambil kategori
if ($action !== 'delete' && !$categoryId) {
  die("Gagal mendapatkan kategori");
}

// Edit font
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $fontName    = $_POST['fontName'];
  $style       = $_POST['style'];
  $license     = $_POST['license'];
  $description = $_POST['description'];

  $stmt = $conn->prepare("UPDATE fonts SET font_name = ?, category_id = ?, style = ?, license = ?, description = ? WHERE id = ?");
  $stmt->bind_param("sisssi", $fontName, $categoryId, $style, $license, $description, $id);
  $stmt->execute();
  $stmt->close();

  header("Location: ../View/admin/dashboardAdmin.php?msg=updated");
  exit();
}

// Tambah font baru
if ($action === 'add') {
  $fontName    = $_POST['fontName'];
  $style       = $_POST['style'] ?? 'Regular';
  $license     = $_POST['license'];
  $description = $_POST['description'];
  $createdAt   = date('Y-m-d H:i:s');
  $uploaded_by = $_SESSION['user_id'] ?? null;

  // Handle upload
  $uploadDir = '../Uploads/Fonts/';
  $previewFile  = $_FILES['previewFontFile'] ?? null;
  $downloadFile = $_FILES['fontFile'] ?? null;

  $previewPath = '';
  $downloadPath = '';

  if ($previewFile && $previewFile['tmp_name']) {
    $previewName = time() . '_preview_' . basename($previewFile['name']);
    $previewPath = $uploadDir . $previewName;
    if (!move_uploaded_file($previewFile['tmp_name'], $previewPath)) {
      die("Gagal upload preview font.");
    }
  }

  if ($downloadFile && $downloadFile['tmp_name']) {
    $downloadName = time() . '_download_' . basename($downloadFile['name']);
    $downloadPath = $uploadDir . $downloadName;
    if (!move_uploaded_file($downloadFile['tmp_name'], $downloadPath)) {
      die("Gagal upload font file.");
    }
  }

  if (!$previewPath || !$downloadPath) {
    die("Upload gagal. File tidak ditemukan.");
  }

  $gapResult = $conn->query("SELECT MIN(t1.id + 1) AS next_id FROM fonts t1 LEFT JOIN fonts t2 ON t1.id + 1 = t2.id WHERE t2.id IS NULL");
  $nextId = $gapResult->fetch_assoc()['next_id'] ?? null;
  if (!$nextId) {
    $maxIdRes = $conn->query("SELECT MAX(id) AS max_id FROM fonts");
    $nextId = ($maxIdRes->fetch_assoc()['max_id'] ?? 0) + 1;
  }

  $stmt = $conn->prepare("INSERT INTO fonts (id, font_name, category_id, style, license, description, file_path, download_path, created_at, uploaded_by)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("isissssssi", $nextId, $fontName, $categoryId, $style, $license, $description, $previewPath, $downloadPath, $createdAt, $uploaded_by);
  $stmt->execute();
  $stmt->close();

  header("Location: ../View/landingPage.php");
  exit();
}

$conn->close();
?>
