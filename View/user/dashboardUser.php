<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dbfont");
if ($conn->connect_error) die("Connection failed");


$userId = $_SESSION['user_id'];
$page = $_GET['page'] ?? 'dashboard';
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$userFonts = $conn->query("SELECT COUNT(*) FROM fonts WHERE uploaded_by = $userId")->fetch_row()[0];

// Chart data
$chartResult = $conn->query("
  SELECT DATE_FORMAT(created_at, '%b %Y') AS month, COUNT(*) as total
  FROM fonts
  WHERE uploaded_by = $userId
  GROUP BY month
  ORDER BY MIN(created_at)
");
$labels = [];
$values = [];
while ($row = $chartResult->fetch_assoc()) {
    $labels[] = $row['month'];
    $values[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <i data-lucide="folder-edit" class="w-4 h-4"></i>
                    <span class="sidebar-text">My Fonts</span>
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
                <div class="bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 shadow-xl p-6 flex flex-col sm:flex-row items-center gap-6 mb-12">

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




                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">

                    <!-- Total Uploaded Fonts -->
                    <div class="bg-gradient-to-br from-slate-800/40 to-slate-900/10 border border-slate-600/30 rounded-xl p-6 shadow-md hover:scale-105 transition cursor-pointer">
                        <div class="text-sm text-slate-300 mb-2">Total Uploaded Fonts</div>
                        <div class="text-3xl font-bold text-white/90"><?= $userFonts ?></div>
                    </div>

                    <!-- Account Info -->
                    <div class="bg-gradient-to-br from-slate-800/40 to-slate-900/10 border border-slate-600/30 rounded-xl p-6 shadow-md hover:scale-105 transition cursor-pointer">
                        <div class="text-sm text-white/70 mb-2">Account</div>
                        <div class="text-white font-semibold text-lg">@<?= htmlspecialchars($_SESSION['user']) ?></div>
                        <div class="text-neutral-400 text-sm">Standard User</div>
                    </div>

                    <!-- Upload Font -->
                    <div class="bg-gradient-to-br from-slate-800/40 to-slate-900/10 border border-slate-600/30 rounded-xl p-6 shadow-md hover:scale-105 transition cursor-pointer flex flex-col justify-between">
                        <div>
                            <div class="text-sm text-white/70 mb-2">Upload Font</div>
                            <p class="text-neutral-400 text-sm leading-relaxed">Share your unique fonts with the world.</p>
                        </div>
                        <a href="../addFont.php" class="mt-4 inline-block text-center text-sm px-4 py-2 bg-white text-black rounded hover:bg-neutral-200 transition">Upload New Font</a>
                    </div>

                </div>

                <div class="bg-white/5 backdrop-blur mt-10 p-6 rounded-xl shadow border border-white/10">
                    <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <i data-lucide="bar-chart" class="w-5 h-5 text-sky-400"></i>
                        Upload Activity
                    </h2>
                    <canvas id="uploadChart" height="100"></canvas>
                </div>
            <?php endif; ?>

            <?php if ($page === 'fonts'): ?>
                <?php
                $baseQuery = "
      SELECT f.*, c.category_name 
      FROM fonts f 
      JOIN categories c ON f.category_id = c.id 
      WHERE f.uploaded_by = $userId
    ";

                if (!empty($_GET['search'])) {
                    $search = $conn->real_escape_string($_GET['search']);
                    $baseQuery .= " AND f.font_name LIKE '%$search%'";
                }

                $allowedSort = [
                    'font_name' => 'f.font_name',
                    'category_name' => 'c.category_name',
                    'created_at' => 'f.created_at'
                ];
                $sortKey = $_GET['sort'] ?? '';
                $order = (strtolower($_GET['order'] ?? '') === 'asc') ? 'ASC' : 'DESC';
                if (array_key_exists($sortKey, $allowedSort)) {
                    $sortColumn = $allowedSort[$sortKey];
                    $baseQuery .= " ORDER BY $sortColumn $order";
                } else {
                    $baseQuery .= " ORDER BY f.created_at DESC";
                }

                $fonts = $conn->query($baseQuery);

                function sortLinkUser($label, $key)
                {
                    $currentSort = $_GET['sort'] ?? '';
                    $currentOrder = $_GET['order'] ?? 'desc';
                    $newOrder = ($currentSort === $key && $currentOrder === 'asc') ? 'desc' : 'asc';
                    $arrow = $currentSort === $key ? ($currentOrder === 'asc' ? '↑' : '↓') : '';
                    $query = http_build_query(array_merge($_GET, ['sort' => $key, 'order' => $newOrder]));
                    return "<a href='?{$query}' class='px-3 py-1.5 rounded-full text-xs font-medium border border-white/10 text-white/60 hover:text-white hover:bg-white/10 transition'>$label $arrow</a>";
                }
                ?>

                <h2 class="text-2xl font-semibold mb-6">My Uploaded Fonts</h2>

                <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap gap-2">
                        <?= sortLinkUser('Font Name', 'font_name') ?>
                        <?= sortLinkUser('Category', 'category_name') ?>
                        <?= sortLinkUser('Created At', 'created_at') ?>
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
                                <th class="px-4 py-3 text-left">Created At</th>
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
                                    <td colspan="5" class="text-center py-6 text-white/40">No fonts found</td>
                                </tr>
                            <?php endif ?>
                        </tbody>
                    </table>
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
                            $targetDir = '../../uploads/avatar/';
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
        &copy; <?= date('Y') ?> FontGallery User Panel. All rights reserved.
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

        const ctx = document.getElementById('uploadChart')?.getContext('2d');
        if (ctx) {
            const uploadChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($labels) ?>,
                    datasets: [{
                        label: 'Uploaded Fonts',
                        data: <?= json_encode($values) ?>,
                        backgroundColor: '#38bdf8'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>
</body>

</html>