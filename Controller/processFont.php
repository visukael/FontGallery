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

function capitalizeCategory($name) {
  return ucwords(strtolower(trim($name)));
}

// ---------------- DELETE FONT ----------------
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

// ---------------- CATEGORY HANDLING ----------------
$categoryInput = '';
if (!empty($_POST['category_combined_select']) && $_POST['category_combined_select'] !== '__add__') {
  $categoryInput = $_POST['category_combined_select'];
} elseif (!empty($_POST['category_combined'])) {
  $categoryInput = capitalizeCategory($_POST['category_combined']);
}

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

if ($action !== 'delete' && !$categoryId) {
  die("Gagal mendapatkan kategori");
}

// ---------------- EDIT FONT ----------------
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $fontName    = $_POST['fontName'];
  $style       = $_POST['style'];
  $license     = $_POST['license'];
  $description = $_POST['description'];

  $check = $conn->prepare("SELECT status FROM fonts WHERE id = ?");
  $check->bind_param("i", $id);
  $check->execute();
  $result = $check->get_result();
  $oldStatus = $result->fetch_assoc()['status'] ?? null;
  $check->close();

  $uploadDir = '../Uploads/Fonts/';
  $newPreview = $_FILES['previewFontFile'] ?? null;
  $newDownload = $_FILES['fontFile'] ?? null;

  $updateFields = "font_name = ?, category_id = ?, style = ?, license = ?, description = ?";
  $params = [$fontName, $categoryId, $style, $license, $description];
  $types = "sisss";

  // Handle new preview file
  if ($newPreview && $newPreview['error'] === UPLOAD_ERR_OK) {
    $previewName = time() . '_preview_' . basename($newPreview['name']);
    $previewPath = $uploadDir . $previewName;
    if (move_uploaded_file($newPreview['tmp_name'], $previewPath)) {
      $updateFields .= ", file_path = ?";
      $params[] = $previewPath;
      $types .= "s";
    }
  }

  // Handle new downloadable zip
  if ($newDownload && $newDownload['error'] === UPLOAD_ERR_OK) {
    $downloadName = time() . '_download_' . basename($newDownload['name']);
    $downloadPath = $uploadDir . $downloadName;
    if (move_uploaded_file($newDownload['tmp_name'], $downloadPath)) {
      $updateFields .= ", download_path = ?";
      $params[] = $downloadPath;
      $types .= "s";
    }
  }

  // Ubah status ke pending jika approved atau denied
  if (in_array($oldStatus, ['approved', 'denied'])) {
    $updateFields .= ", status = 'pending'";
  }

  $updateFields .= " WHERE id = ?";
  $params[] = $id;
  $types .= "i";

  $stmt = $conn->prepare("UPDATE fonts SET $updateFields");
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $stmt->close();

  header("Location: ../View/admin/dashboardAdmin.php?msg=updated");
  exit();
}

// ---------------- ADD FONT ----------------
if ($action === 'add') {
  $fontName    = $_POST['fontName'];
  $style       = $_POST['style'] ?? 'Regular';
  $license     = $_POST['license'];
  $description = $_POST['description'];
  $createdAt   = date('Y-m-d H:i:s');
  $uploaded_by = $_SESSION['user_id'] ?? null;

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

  $stmt = $conn->prepare("INSERT INTO fonts (id, font_name, category_id, style, license, description, file_path, download_path, uploaded_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
  $stmt->bind_param("isisssssi", $nextId, $fontName, $categoryId, $style, $license, $description, $previewPath, $downloadPath, $uploaded_by);

  $stmt->execute();
  $stmt->close();

  header("Location: ../View/landingPage.php");
  exit();
}

$conn->close();
