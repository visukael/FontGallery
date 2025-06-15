<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dbfont");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? (int) $_GET['category'] : 0;

$perPage = 9;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$conditions = [];
if ($search) {
  $conditions[] = "fonts.font_name LIKE '%$search%'";
}
if ($categoryFilter > 0) {
  $conditions[] = "fonts.category_id = $categoryFilter";
}
$searchCondition = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

$categoryResult = $conn->query("SELECT * FROM categories");

$totalResult = $conn->query("SELECT COUNT(*) as total FROM fonts $searchCondition");
$totalFonts = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalFonts / $perPage);

$sql = "SELECT fonts.*, categories.category_name FROM fonts 
        JOIN categories ON fonts.category_id = categories.id 
        $searchCondition
        ORDER BY fonts.created_at DESC 
        LIMIT $perPage OFFSET $offset";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Font Library</title>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html {
      scroll-behavior: smooth;
    }

    .glass-button {
      backdrop-filter: blur(8px);
      background-color: rgba(255, 255, 255, 0.1);
    }

    @keyframes float {
      0% {
        transform: translateY(0);
        opacity: 0.4;
      }

      50% {
        transform: translateY(-20px);
        opacity: 0.7;
      }

      100% {
        transform: translateY(0);
        opacity: 0.4;
      }
    }
  </style>
</head>

<body class="bg-black text-white font-sans">
  <div id="floatingContainer">
    <div class="floating-shape" style="top:10%; left:15%;"></div>
    <div class="floating-shape" style="top:30%; left:75%;"></div>
    <div class="floating-shape" style="top:60%; left:40%;"></div>
    <div class="floating-shape" style="top:80%; left:20%;"></div>
  </div>

  <!-- Hero -->
  <?php if (!isset($_SESSION['user'])): ?>
    <section class="relative bg-cover bg-center min-h-screen flex items-center justify-center text-center text-white overflow-hidden" style="background-image: url('../Assets/moon.png');">
      <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/90 z-0"></div>
      <div class="absolute inset-0 z-0 pointer-events-none">
        <canvas id="stars" class="w-full h-full"></canvas>
      </div>
      <div class="relative z-10 px-6 animate-fade-in-slow">
        <h1 class="text-5xl md:text-6xl font-semibold leading-tight mb-6 drop-shadow-md">
          We exist for a <span class="text-white">reason.</span>
        </h1>
        <p class="text-neutral-300 max-w-xl mx-auto text-base md:text-lg">
          Typography, Tailored for Creators.
        </p>
      </div>
      <div class="absolute bottom-20 left-1/2 transform -translate-x-1/2 z-10">
        <a href="#fontSection" class="glass-button px-6 py-3 rounded-full text-white border border-white/20 transition hover:border-white hover:bg-white/10 shadow-lg shadow-white/10 hover:shadow-white/20">
          Get Started
        </a>
      </div>
    </section>

    <div class="h-[30vh] bg-black"></div>
  <?php endif; ?>




  <div id="fontSection">
    <nav class="flex justify-between items-center px-8 py-6 border-b border-neutral-800">
      <div class="flex items-center gap-8">
        <h1 class="text-3xl font-semibold">FG</h1>
      </div>
      <div class="flex items-center gap-3">
        <?php if (isset($_SESSION['user'])): ?>
          <a href="addFont.php" class="text-sm px-4 py-2 border border-white/20 rounded-full text-white hover:bg-white/10 transition">Add Font</a>
        <?php else: ?>
          <a href="login.php" onclick="return confirm('You must login first to add a font.')" class="text-sm px-4 py-2 border border-white/20 rounded-full text-white hover:bg-white/10 transition">Add Font</a>
        <?php endif; ?>
        <?php if (isset($_SESSION['user'])): ?>
          <form action="../Controller/logout.php" method="POST">
            <button type="submit" class="text-sm px-4 py-2 border border-white/20 rounded-full text-white hover:bg-white/10 transition">Logout</button>
          </form>
        <?php else: ?>
          <a href="login.php" class="text-sm px-4 py-2 border border-white/20 rounded-full text-white hover:bg-white/10 transition">Login</a>
        <?php endif; ?>
        <?php if (isset($_SESSION['user'])): ?>
          <a href="<?= $_SESSION['role'] === 'admin' ? 'admin/dashboardAdmin.php' : 'user/dashboardUser.php' ?>"
            class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center hover:bg-white/10 transition"
            title="Profile">
            <i data-lucide="user" class="lucide w-5 h-5 text-white"></i>
          </a>
        <?php endif; ?>

      </div>
    </nav>

    <!-- Search & Filter -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-8 py-4">
      <!-- Search Input -->
      <form action="" method="GET" class="relative flex-1">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search fonts..."
          class="w-full p-3 bg-[#1a1a1a] border border-neutral-700 text-white rounded-xl pl-10 focus:outline-none focus:ring-2 focus:ring-white placeholder:text-neutral-500 transition" />
        <i data-lucide="search" class="lucide absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-500 pointer-events-none"></i>

      </form>

      <!-- Filter Dropdown -->
      <form method="GET" class="w-full md:w-auto">
        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
        <select name="category"
          onchange="this.form.submit()"
          class="block w-full md:w-[180px] bg-[#1a1a1a] border border-neutral-700 text-white p-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-white transition">

          <option value="0" <?= $categoryFilter == 0 ? 'selected' : '' ?>>All Categories</option>

          <?php while ($cat = $categoryResult->fetch_assoc()): ?>
            <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['category_name']) ?>
            </option>
          <?php endwhile; ?>

        </select>
      </form>

    </div>

    <!-- Font List -->
    <div id="fontContainer" class="px-8 py-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()):
          $fontId = "font" . $row['id'];
          $fontUrl = '../Uploads/Fonts/' . basename($row['file_path']);
          echo "<style>@font-face { font-family: '$fontId'; src: url('$fontUrl'); }</style>";
        ?>
          <a href="detailFont.php?id=<?= $row['id'] ?>" class="reveal group block bg-[#121212] rounded-2xl overflow-hidden hover:scale-105 transition-transform">
            <div class="p-8 pb-6 border-b border-neutral-800">
              <div class="text-[72px] md:text-[90px] lg:text-[100px] font-bold leading-none text-white" style="font-family: '<?= $fontId ?>';">
                <?= strtoupper(substr($row['font_name'], 0, 1)) ?>
              </div>
            </div>
            <div class="p-6">
              <h2 class="text-xl font-semibold text-white mb-1"><?= htmlspecialchars($row['font_name']) ?></h2>
              <p class="text-sm text-neutral-400"><?= $row['category_name'] ?> — 1 style</p>
              <p class="text-xs text-neutral-500 mt-2"><?= htmlspecialchars($row['license']) ?></p>
            </div>
          </a>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-center text-neutral-500 col-span-full">No fonts found.</p>
      <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="flex justify-center gap-1 mt-5 mb-10">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?search=<?= urlencode($search) ?>&category=<?= $categoryFilter ?>&page=<?= $i ?>"
            class="px-4 py-2 border border-white/20 rounded <?= $i == $page ? 'bg-white text-black' : 'text-white hover:bg-white/10' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Footer -->
  <footer class="bg-black text-white border-t border-neutral-800 px-8 py-12 grid grid-cols-1 md:grid-cols-3 gap-8 text-sm">
    <div>
      <h1 class="text-6xl font-semibold mb-4">FG</h1>
      <p class="text-neutral-400">This website was developed as part of the learning process and final assessment for the Web Programming course.</p>
    </div>
    <div>
      <h2 class="text-neutral-300 mb-2 font-medium">All typefaces</h2>
      <ul class="space-y-1 text-neutral-400">
        <li>Cursive</li>
        <li>Display</li>
        <li>Monospace</li>
        <li>Sans Serif</li>
        <li>Serif</li>
        <li>Slab</li>
      </ul>
    </div>
    <div>
      <p class="text-neutral-400">Website by <span class="text-white">Kelompok 5</span></p>
      <p class="text-neutral-400">Based in <span class="text-white">Yogyakarta</span></p>
      <p class="text-white mt-2">Contact</p>
    </div>
  </footer>

  <script>
    lucide.createIcons();

    const canvas = document.getElementById('stars');
    const ctx = canvas.getContext('2d');
    let stars = [];

    function resizeCanvas() {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
      stars = Array.from({
        length: 80
      }, () => ({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        r: Math.random() * 1.2,
        o: Math.random(),
        d: Math.random() * 0.02
      }));
    }

    function animateStars() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      for (let star of stars) {
        ctx.beginPath();
        ctx.arc(star.x, star.y, star.r, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(255, 255, 255, ${star.o})`;
        ctx.fill();
        star.o += star.d;
        if (star.o > 1 || star.o < 0) star.d = -star.d;
      }
      requestAnimationFrame(animateStars);
    }

    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();
    animateStars();
  </script>
</body>

</html>