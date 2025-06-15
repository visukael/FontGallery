<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dbfont");
if ($conn->connect_error) die("DB connection failed");

$action = $_POST['action'] ?? '';

if ($action === 'signup') {
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
  $full_name = $_POST['full_name'];
  $role = 'user';

  $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
  $check->bind_param("ss", $username, $email);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    header("Location: ../View/login.php?error=exists");
    exit();
  }

  $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $username, $email, $password, $full_name, $role);
  if ($stmt->execute()) {
    header("Location: ../View/login.php?success=created");
  } else {
    header("Location: ../View/login.php?error=failed");
  }

  $stmt->close();
  $check->close();
}

if ($action === 'login') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
      $_SESSION['user'] = $user['username'];
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['role'] = $user['role'];
      header("Location: ../View/landingPage.php");
    } else {
      header("Location: ../View/login.php?error=invalidpass");
    }
  } else {
    header("Location: ../View/login.php?error=nouser");
  }

  $stmt->close();
}

$conn->close();
