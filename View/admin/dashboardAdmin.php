<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

$conn = new mysqli("localhost", "root", "", "dbfont");
if ($conn->connect_error) die("Connection failed");

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$page = $_GET['page'] ?? 'dashboard';
$totalFonts = $conn->query("SELECT COUNT(*) FROM fonts")->fetch_row()[0];
$totalCategories = $conn->query("SELECT COUNT(*) FROM categories")->fetch_row()[0];
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$adminFonts = $conn->query("SELECT COUNT(*) FROM fonts f JOIN users u ON f.uploaded_by = u.id WHERE u.role = 'admin'")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-20px) translateX(-50%);
      }

      to {
        opacity: 1;
        transform: translateY(0) translateX(-50%);
      }
    }

    @keyframes fadeOut {
      to {
        opacity: 0;
        transform: translateY(-20px) translateX(-50%);
      }
    }

    .animate-slideIn {
      animation: slideIn 0.4s ease-out forwards;
    }

    .fadeOut {
      animation: fadeOut 0.4s ease-in forwards;
    }
  </style>
</head>

<body class="bg-black text-white font-sans min-h-screen flex flex-col">

  <nav class="flex items-center px-8 py-6 border-b border-neutral-800">
    <a class="text-3xl font-semibold mr-6">FG</a>
    <div class="ml-auto flex gap-3">
      <a href="../landingPage.php" class="text-sm px-4 py-2 border border-white/20 rounded-full hover:bg-white/10 transition">← Back to Gallery</a>
      <form action="../../Controller/logout.php" method="POST">
        <button class="text-sm px-4 py-2 border border-white/20 rounded-full hover:bg-white/10 transition">Logout</button>
      </form>
    </div>
  </nav>

  <div class="flex flex-1 min-h-screen overflow-hidden">
    <aside id="sidebar" class="w-64 transition-all duration-300 bg-[#111] border-r border-neutral-800 p-4">
      <button onclick="toggleSidebar()" class="mb-6 text-white hover:text-neutral-400 transition">
        <i data-lucide="chevron-left" id="toggle-icon" class="w-6 h-6"></i>
      </button>
      <div id="sidebar-links" class="flex flex-col gap-2">
        <a href="?page=dashboard" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $page == 'dashboard' ? 'bg-neutral-800' : '' ?> hover:bg-neutral-700 transition">
          <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
          <span class="sidebar-text">Dashboard</span>
        </a>
        <a href="?page=fonts" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $page == 'fonts' ? 'bg-neutral-800' : '' ?> hover:bg-neutral-700 transition">
          <i data-lucide="settings" class="w-4 h-4"></i>
          <span class="sidebar-text">Manage Fonts</span>
        </a>
        <a href="?page=users" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $page == 'users' ? 'bg-neutral-800' : '' ?> hover:bg-neutral-700 transition">
          <i data-lucide="users" class="w-4 h-4"></i>
          <span class="sidebar-text">Users</span>
        </a>
        <a href="?page=categories" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $page == 'categories' ? 'bg-neutral-800' : '' ?> hover:bg-neutral-700 transition">
          <i data-lucide="list" class="w-4 h-4"></i>
          <span class="sidebar-text">Categories</span>
        </a>
        <a href="?page=settings" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $page == 'fonts' ? 'bg-neutral-800' : '' ?> hover:bg-neutral-700 transition">
          <i data-lucide="settings" class="w-4 h-4"></i>
          <span class="sidebar-text">Settings</span>
        </a>
      </div>
    </aside>

    <main class="flex-1 p-8 overflow-y-auto">



      <?php if ($page === 'dashboard'): ?>
        <!-- Profile Card -->
        <div class="bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 shadow-xl p-6 flex flex-col sm:flex-row items-center gap-6 mb-9">

          <!-- Clickable Avatar -->
          <a href="?page=settings" class="relative group block w-28 h-28 shrink-0">
            <img src="<?= $user['avatar'] && file_exists($user['avatar'])
                        ? htmlspecialchars($user['avatar'])
                        : 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name']) . '&background=111&color=fff&size=128' ?>"
              alt="Avatar"
              class="w-28 h-28 rounded-full object-cover border-4 border-gray-800 shadow-lg transition duration-300 group-hover:scale-105" />

            <!-- Overlay -->
            <div class="absolute inset-0 rounded-full bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-xs text-white font-medium transition">
              Edit Profile
            </div>
          </a>

          <!-- User Info -->
          <div class="text-center sm:text-left">
            <h2 class="text-2xl font-semibold text-white"><?= htmlspecialchars($user['full_name']) ?></h2>
            <p class="text-sm text-neutral-400">@<?= htmlspecialchars($user['username']) ?></p>
            <p class="text-sm text-neutral-400"><?= htmlspecialchars($user['email']) ?></p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
          <a href="?page=fonts" class="bg-gradient-to-br from-slate-800/40 to-slate-900/10 border border-slate-600/30 rounded-xl p-6 shadow-md hover:scale-105 transition cursor-pointer">
            <p class="text-slate-400 text-sm mb-2">Total Fonts</p>
            <h2 class="text-3xl font-bold text-slate-100"><?= $totalFonts ?></h2>
          </a>

          <a href="?page=categories" class="bg-gradient-to-br from-slate-800/40 to-slate-900/10 border border-slate-600/30 rounded-xl p-6 shadow-md hover:scale-105 transition cursor-pointer">
            <p class="text-slate-400 text-sm mb-2">Categories</p>
            <h2 class="text-3xl font-bold text-slate-100"><?= $totalCategories ?></h2>
          </a>

          <a href="../addFont.php?filter=admin" class="bg-gradient-to-br from-slate-800/40 to-slate-900/10 border border-slate-600/30 rounded-xl p-6 shadow-md hover:scale-105 transition cursor-pointer">
            <p class="text-slate-400 text-sm mb-2">Admin Uploads</p>
            <h2 class="text-3xl font-bold text-slate-100"><?= $adminFonts ?></h2>
          </a>

          <a href="?page=users" class="bg-gradient-to-br from-slate-800/40 to-slate-900/10 border border-slate-600/30 rounded-xl p-6 shadow-md hover:scale-105 transition cursor-pointer">

            <p class="text-slate-400 text-sm mb-2">Users</p>
            <h2 class="text-3xl font-bold text-slate-100"><?= $totalUsers ?></h2>
          </a>
        </div>


        <div class="bg-white/5 backdrop-blur-md border-white/10 p-6 rounded-xl shadow">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">Recent Uploads</h2>
            <a href="?page=fonts" class="text-sm text-blue-400 hover:underline">View all</a>
          </div>
          <table class="w-full text-left text-sm">
            <thead class="text-neutral-400 border-b border-neutral-700">
              <tr>
                <th class="pb-2">Font Name</th>
                <th class="pb-2">Uploader</th>
                <th class="pb-2">Date</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $latestFonts = $conn->query("SELECT f.font_name, u.username, f.created_at FROM fonts f JOIN users u ON f.uploaded_by = u.id ORDER BY f.created_at DESC LIMIT 5");
              while ($row = $latestFonts->fetch_assoc()): ?>
                <tr class="border-b border-neutral-800 hover:bg-[#222222]">
                  <td class="py-2 font-medium text-white"><?= htmlspecialchars($row['font_name']) ?></td>
                  <td class="py-2 text-neutral-400"><?= htmlspecialchars($row['username']) ?></td>
                  <td class="py-2 text-neutral-500">
                    <span class="inline-block bg-white/10 px-2 py-0.5 rounded text-xs">
                      <?= date("d M Y", strtotime($row['created_at'])) ?>
                    </span>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

      <!-- Fonts Management -->
      <?php if ($page === 'fonts'): ?>
        <?php
        $fonts = $conn->query("
      SELECT f.*, c.category_name, u.username 
      FROM fonts f
      JOIN categories c ON f.category_id = c.id
      LEFT JOIN users u ON f.uploaded_by = u.id
      ORDER BY f.created_at DESC
    ");

        if (!empty($_GET['search'])) {
          $search = $conn->real_escape_string($_GET['search']);
          $fonts_search = $conn->query("
        SELECT f.*, c.category_name, u.username 
        FROM fonts f
        JOIN categories c ON f.category_id = c.id
        LEFT JOIN users u ON f.uploaded_by = u.id
        WHERE f.font_name LIKE '%$search%' OR c.category_name LIKE '%$search%'
        ORDER BY f.created_at DESC
      ");
          if ($fonts_search) $fonts = $fonts_search;
        }

        $allowed_sort = [
          'font_name' => 'f.font_name',
          'created_at' => 'f.created_at',
          'category_name' => 'c.category_name'
        ];
        $sort_key = $_GET['sort'] ?? '';
        $order = (strtolower($_GET['order'] ?? '') === 'asc') ? 'ASC' : 'DESC';
        if (array_key_exists($sort_key, $allowed_sort)) {
          $sort_column = $allowed_sort[$sort_key];
          $fonts_sorted = $conn->query("
        SELECT f.*, c.category_name, u.username 
        FROM fonts f
        JOIN categories c ON f.category_id = c.id
        LEFT JOIN users u ON f.uploaded_by = u.id
        ORDER BY $sort_column $order
      ");
          if ($fonts_sorted) $fonts = $fonts_sorted;
        }
        ?>

        <?php if (isset($_GET['msg'])): ?>
          <div class="mb-4 text-sm px-4 py-2 rounded bg-green-700/20 border border-green-400/30 w-fit">
            <?php
            $msg = $_GET['msg'];
            if ($msg === 'deleted') echo "Font deleted successfully.";
            elseif ($msg === 'updated') echo "Font updated successfully.";
            elseif ($msg === 'error')   echo "Something went wrong.";
            ?>
          </div>
        <?php endif; ?>

        <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
          <div class="flex flex-wrap gap-2">
            <?php
            function sortLink($label, $key)
            {
              $currentSort = $_GET['sort'] ?? '';
              $currentOrder = $_GET['order'] ?? 'desc';
              $newOrder = ($currentSort === $key && $currentOrder === 'asc') ? 'desc' : 'asc';
              $arrow = $currentSort === $key ? ($currentOrder === 'asc' ? '↑' : '↓') : '';
              $query = http_build_query(array_merge($_GET, ['sort' => $key, 'order' => $newOrder]));
              return "<a href='?{$query}'
          class='px-3 py-1.5 rounded-full text-xs font-medium border border-white/10 text-white/60 hover:text-white hover:bg-white/10 transition-all'>
          $label $arrow
        </a>";
            }

            echo sortLink('Font Name', 'font_name');
            echo sortLink('Category', 'category_name');
            echo sortLink('Uploaded At', 'created_at');
            ?>
          </div>

          <form method="GET" class="flex gap-2">
            <input type="hidden" name="page" value="fonts">
            <input type="text" name="search" placeholder="Search font..."
              value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
              class="bg-white/10 text-white text-sm rounded px-3 py-1.5 placeholder-white/40 focus:outline-none focus:ring focus:ring-white/30" />
            <button type="submit"
              class="bg-white/10 hover:bg-white/20 text-sm text-white px-4 py-1.5 rounded transition">Search</button>
          </form>
        </div>

        <div class="bg-[#1a1a1a] rounded-xl shadow overflow-y-auto max-h-[600px] border border-white/10">
          <table class="min-w-full text-sm text-white">
            <thead class="sticky top-0 z-10 bg-[#121212] text-xs uppercase tracking-wide text-white/60 border-b border-white/10">
              <tr>
                <th class="px-4 py-3 text-left">#</th>
                <th class="px-4 py-3 text-left">Font Name</th>
                <th class="px-4 py-3 text-left">Category</th>
                <th class="px-4 py-3 text-left">Uploader</th>
                <th class="px-4 py-3 text-left">Uploaded At</th>
                <th class="px-4 py-3 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1;
              while ($row = $fonts->fetch_assoc()): ?>
                <tr class="border-b border-white/5 hover:bg-white/5 transition-all">
                  <td class="px-4 py-3 text-white/50"><?= $i++ ?></td>
                  <td class="px-4 py-3 font-semibold"><?= htmlspecialchars($row['font_name']) ?></td>
                  <td class="px-4 py-3 text-white/80"><?= htmlspecialchars($row['category_name']) ?></td>
                  <td class="px-4 py-3 text-white/50"><?= htmlspecialchars($row['username'] ?? '—') ?></td>
                  <td class="px-4 py-3 text-white/40"><?= date("d M Y", strtotime($row['created_at'])) ?></td>
                  <td class="px-4 py-3">
                    <div class="flex gap-2">
                      <a href="../addFont.php?edit=<?= $row['id'] ?>" class="text-blue-400 hover:underline text-sm">Edit</a>
                      <a href="../../Controller/processFont.php?action=delete&id=<?= $row['id'] ?>"
                        onclick="return confirm('Are you sure you want to delete this font?')"
                        class="text-red-400 hover:underline text-sm">Delete</a>
                    </div>
                  </td>
                </tr>
              <?php endwhile ?>
              <?php if ($fonts->num_rows === 0): ?>
                <tr>
                  <td colspan="6" class="text-center py-6 text-white/40">No fonts found</td>
                </tr>
              <?php endif ?>
            </tbody>
          </table>
        </div>




      <?php endif; ?>

      <!-- Users Management -->
      <?php if ($page === 'users'): ?>
        <?php
        $search = $_GET['search'] ?? '';
        $sortOptions = [
          'username' => 'username',
          'full_name' => 'full_name',
          'email' => 'email',
          'created_at' => 'created_at'
        ];
        $sortKey = $_GET['sort'] ?? 'created_at';
        $order = (strtolower($_GET['order'] ?? '') === 'asc') ? 'ASC' : 'DESC';
        $sortColumn = $sortOptions[$sortKey] ?? 'created_at';

        $searchSql = '';
        if (!empty($search)) {
          $safeSearch = $conn->real_escape_string($search);
          $searchSql = "WHERE username LIKE '%$safeSearch%' OR full_name LIKE '%$safeSearch%' OR email LIKE '%$safeSearch%'";
        }

        $users = $conn->query("
    SELECT id, username, full_name, email, role, created_at 
    FROM users
    $searchSql
    ORDER BY $sortColumn $order
  ");
        ?>

        <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
          <div class="flex flex-wrap gap-2">
            <?php
            function sortUserLink($label, $key)
            {
              $currentSort = $_GET['sort'] ?? '';
              $currentOrder = $_GET['order'] ?? 'desc';
              $newOrder = ($currentSort === $key && $currentOrder === 'asc') ? 'desc' : 'asc';
              $arrow = $currentSort === $key ? ($currentOrder === 'asc' ? '↑' : '↓') : '';
              $query = http_build_query(array_merge($_GET, ['sort' => $key, 'order' => $newOrder]));
              return "<a href='?$query'
          class='px-3 py-1.5 rounded-full text-xs font-medium border border-white/10 text-white/60 hover:text-white hover:bg-white/10 transition-all'>
          $label $arrow
        </a>";
            }

            echo sortUserLink('Username', 'username');
            echo sortUserLink('Full Name', 'full_name');
            echo sortUserLink('Email', 'email');
            echo sortUserLink('Joined', 'created_at');
            ?>
          </div>

          <form method="GET" class="flex gap-2">
            <input type="hidden" name="page" value="users">
            <input type="text" name="search" placeholder="Search user..."
              value="<?= htmlspecialchars($search) ?>"
              class="bg-white/10 text-white text-sm rounded px-3 py-1.5 placeholder-white/40 focus:outline-none focus:ring focus:ring-white/30" />
            <button type="submit"
              class="bg-white/10 hover:bg-white/20 text-sm text-white px-4 py-1.5 rounded transition">Search</button>
          </form>
        </div>

        <div class="bg-[#1a1a1a] rounded-xl shadow overflow-y-auto max-h-[600px] border border-white/10">
          <table class="min-w-full text-sm text-white">
            <thead class="sticky top-0 z-10 bg-[#121212] text-xs uppercase tracking-wide text-white/60 border-b border-white/10">
              <tr>
                <th class="px-4 py-3 text-left">#</th>
                <th class="px-4 py-3 text-left">Username</th>
                <th class="px-4 py-3 text-left">Full Name</th>
                <th class="px-4 py-3 text-left">Email</th>
                <th class="px-4 py-3 text-left">Role</th>
                <th class="px-4 py-3 text-left">Joined</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1;
              while ($user = $users->fetch_assoc()): ?>
                <tr class="border-b border-white/5 hover:bg-white/5 transition-all">
                  <td class="px-4 py-3 text-white/70"><?= $i++ ?></td>
                  <td class="px-4 py-3 font-semibold"><?= htmlspecialchars($user['username']) ?></td>
                  <td class="px-4 py-3"><?= htmlspecialchars($user['full_name']) ?></td>
                  <td class="px-4 py-3 text-white/50"><?= htmlspecialchars($user['email']) ?></td>
                  <td class="px-4 py-3">
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium 
            <?= $user['role'] === 'admin'
                  ? 'bg-red-500/10 text-red-400 border border-red-400/30'
                  : 'bg-green-500/10 text-green-400 border border-green-400/30' ?>">
                      <?= htmlspecialchars($user['role']) ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-white/40"><?= date("d M Y", strtotime($user['created_at'])) ?></td>
                </tr>
              <?php endwhile ?>
              <?php if ($users->num_rows === 0): ?>
                <tr>
                  <td colspan="6" class="text-center py-6 text-white/40">No users found</td>
                </tr>
              <?php endif ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>



      <?php if ($page === 'categories'): ?>
        <?php
        $categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
        $selectedCategoryId = $_GET['id'] ?? null;
        $fonts = [];

        if ($selectedCategoryId) {
          $stmt = $conn->prepare("SELECT fonts.*, users.username 
                              FROM fonts 
                              JOIN users ON fonts.uploaded_by = users.id
                              WHERE category_id = ?
                              ORDER BY fonts.created_at DESC");
          $stmt->bind_param("i", $selectedCategoryId);
          $stmt->execute();
          $fonts = $stmt->get_result();
          $stmt->close();
        }
        ?>

        <div class="grid md:grid-cols-2 gap-6">
          <!-- Tabel kategori -->
          <div class="bg-[#1a1a1a] rounded-xl shadow overflow-y-auto max-h-[600px] border border-white/10">
            <table class="min-w-full text-sm text-white">
              <thead class="sticky top-0 z-10 bg-[#121212] text-xs uppercase tracking-wide text-white/60 border-b border-white/10">
                <tr>
                  <th class="px-4 py-3 text-left">#</th>
                  <th class="px-4 py-3 text-left">Category</th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 1;
                while ($cat = $categories->fetch_assoc()): ?>
                  <tr class="border-b border-white/5 hover:bg-white/5 transition-all">
                    <td class="px-4 py-3 text-white/70"><?= $i++ ?></td>
                    <td class="px-4 py-3">
                      <a href="?page=categories&id=<?= $cat['id'] ?>" class="hover:underline <?= $selectedCategoryId == $cat['id'] ? 'text-white font-semibold' : 'text-white/80' ?>">
                        <?= htmlspecialchars($cat['category_name']) ?>
                      </a>
                    </td>
                  </tr>
                <?php endwhile ?>
              </tbody>
            </table>
          </div>

          <!-- Tabel font per kategori -->
          <div class="bg-[#1a1a1a] rounded-xl shadow overflow-y-auto max-h-[600px] border border-white/10">
            <table class="min-w-full text-sm text-white">
              <thead class="sticky top-0 z-10 bg-[#121212] text-xs uppercase tracking-wide text-white/60 border-b border-white/10">
                <tr>
                  <th class="px-4 py-3 text-left">Font</th>
                  <th class="px-4 py-3 text-left">Uploader</th>
                  <th class="px-4 py-3 text-left">Created At</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($selectedCategoryId && $fonts && $fonts->num_rows > 0): ?>
                  <?php while ($font = $fonts->fetch_assoc()): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-all">
                      <td class="px-4 py-3 font-medium"><?= htmlspecialchars($font['font_name']) ?></td>
                      <td class="px-4 py-3 text-white/60"><?= htmlspecialchars($font['username']) ?></td>
                      <td class="px-4 py-3 text-white/40"><?= date("d M Y", strtotime($font['created_at'])) ?></td>
                    </tr>
                  <?php endwhile ?>
                <?php elseif ($selectedCategoryId): ?>
                  <tr>
                    <td colspan="3" class="px-4 py-4 text-white/40 italic">No fonts found in this category.</td>
                  </tr>
                <?php else: ?>
                  <tr>
                    <td colspan="3" class="px-4 py-4 text-white/40 italic">Select a category to view fonts.</td>
                  </tr>
                <?php endif ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($page === 'settings'): ?>
        <?php
        // Ambil ulang data user jika belum tersedia
        if (!isset($user)) {
          $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
          $stmt->bind_param("i", $userId);
          $stmt->execute();
          $result = $stmt->get_result();
          $user = $result->fetch_assoc();
        }

        // Proses form edit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $fullName = $_POST['full_name'] ?? '';
          $email = $_POST['email'] ?? '';
          $avatar = $user['avatar'];

          if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed)) {
              $newName = uniqid('avatar_') . '.' . $ext;
              $targetDir = '../uploads/avatar/';
              if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
              $uploadPath = $targetDir . $newName;
              if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                $avatar = $uploadPath;
              }
            }
          }

          if ($fullName && $email) {
            $update = $conn->prepare("UPDATE users SET full_name=?, email=?, avatar=? WHERE id=?");
            $update->bind_param("sssi", $fullName, $email, $avatar, $userId);
            $update->execute();
            header("Location: ?page=settings&success=updated");
            exit;
          } else {
            header("Location: ?page=settings&error=empty");
          }
        }
        ?>
        <?php if (isset($_GET['success']) && $_GET['success'] === 'updated'): ?>
          <div class="fixed top-6 left-1/2 transform -translate-x-1/2 bg-white/5 text-white text-sm px-5 py-3 rounded-lg border border-white/10 shadow-lg backdrop-blur-lg z-50 animate-slideIn">
            Profile updated successfully!
          </div>
          <script>
            setTimeout(() => {
              const notif = document.querySelector('.animate-slideIn');
              if (notif) {
                notif.classList.remove('animate-slideIn');
                notif.classList.add('fadeOut');
                setTimeout(() => notif.remove(), 400);
              }
            }, 4000);
          </script>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'empty'): ?>
          <div class="fixed top-6 left-1/2 transform -translate-x-1/2 bg-white/5 text-white text-sm px-5 py-3 rounded-lg border border-white/10 shadow-lg backdrop-blur-lg z-50 animate-slideIn">
            Name and email are required.
          </div>
        <?php endif; ?>

        <section class="flex-1 p-8 overflow-y-auto">
          <h1 class="text-4xl font-bold text-white mb-10 tracking-tight">Account Settings</h1>

          <!-- Avatar Card -->
          <div class="bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 shadow-xl p-6 flex flex-col sm:flex-row items-center gap-6 mb-12">
            <!-- Avatar yang bisa diklik -->
            <a href="?page=settings" class="relative group block">
              <img src="<?= $user['avatar'] && file_exists($user['avatar'])
                          ? htmlspecialchars($user['avatar'])
                          : 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name']) . '&background=111&color=fff&size=128' ?>"
                alt="Avatar"
                class="w-28 h-28 rounded-full object-cover border-4 border-gray-800 shadow-lg transition duration-300 group-hover:scale-105" />
            </a>

            <!-- Info Text -->
            <div class="text-center sm:text-left">
              <h2 class="text-2xl font-semibold text-white"><?= htmlspecialchars($user['full_name']) ?></h2>
              <p class="text-sm text-neutral-400">@<?= htmlspecialchars($user['username']) ?></p>
              <p class="text-sm text-neutral-400"><?= htmlspecialchars($user['email']) ?></p>
            </div>
          </div>


          <!-- Edit Profile Form -->
          <div class="bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 shadow-xl px-8 py-10 mb-12">
            <h3 class="text-xl font-semibold text-white mb-6">Edit Profile</h3>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">

              <!-- Full Name -->
              <div class="flex flex-col">
                <label class="text-sm font-medium text-white mb-1">Full Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>"
                  class="w-full px-4 py-2 bg-white/10 border border-white/20 text-white rounded-md placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" />
              </div>

              <!-- Email -->
              <div class="flex flex-col">
                <label class="text-sm font-medium text-white mb-1">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                  class="w-full px-4 py-2 bg-white/10 border border-white/20 text-white rounded-md placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" />
              </div>

              <!-- Avatar Upload -->
              <div class="flex flex-col">
                <label class="text-sm font-medium text-white mb-1">Upload New Avatar</label>
                <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp"
                  class="w-full px-4 py-2 bg-white/10 border border-white/20 text-white rounded-md file:bg-white/10 file:text-white file:border-0 file:px-4 file:py-2 file:rounded-md hover:file:bg-white/20 transition" />
                <p class="text-xs text-neutral-400 mt-2">Max 2MB. Format: JPG, PNG, WebP.</p>
              </div>

              <!-- Save Button -->
              <div class="pt-4 text-right">
                <button type="submit"
                  class="bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-2 rounded-md font-semibold shadow-sm transition">
                  Save Changes
                </button>
              </div>

            </form>
          </div>

        </section>

      <?php endif; ?>

    </main>
  </div>

  <footer class="py-6 text-center text-neutral-500 text-xs border-t border-neutral-800">
    &copy; <?= date('Y') ?> FontGallery Admin Panel. All rights reserved.
  </footer>

  <script>
    lucide.createIcons();

    function toggleSidebar() {
      const sidebar = document.getElementById("sidebar");
      const sidebarText = document.querySelectorAll(".sidebar-text");
      const toggleIcon = document.getElementById("toggle-icon");
      const sidebarLinks = document.getElementById("sidebar-links");
      sidebar.classList.toggle("w-64");
      sidebar.classList.toggle("w-16");
      sidebar.classList.toggle("collapsed");
      sidebarText.forEach(el => el.classList.toggle("hidden"));
      sidebarLinks.classList.toggle("hidden", sidebar.classList.contains("collapsed"));
      toggleIcon.setAttribute("data-lucide", toggleIcon.getAttribute("data-lucide") === "chevron-left" ? "chevron-right" : "chevron-left");
      lucide.createIcons();
    }
  </script>

  </div>


</body>

</html>