<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dbfont");
if ($conn->connect_error) die("Connection failed");

$isEdit = isset($_GET['edit']);
$fontData = null;
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");

if ($isEdit) {
  $id = $_GET['edit'];
  $stmt = $conn->prepare("SELECT * FROM fonts WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $fontData = $result->fetch_assoc();
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $isEdit ? 'Edit Font' : 'Upload Font' ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function toggleCategoryInput(select) {
      const newInput = document.getElementById('newCategoryInput');
      if (select.value === '__add__') {
        newInput.classList.remove('hidden');
        newInput.setAttribute('required', 'required');
      } else {
        newInput.classList.add('hidden');
        newInput.removeAttribute('required');
      }
    }
  </script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>

<body class="bg-[#090909] text-white min-h-screen flex flex-col">

  <!-- Navigation -->
  <nav class="flex items-center px-8 py-6 border-b border-neutral-800">
    <a class="text-3xl font-semibold mr-6">FG</a>
    <div class="ml-auto flex gap-3">
      <a href="landingPage.php" class="text-sm px-4 py-2 border border-white/20 rounded-full hover:bg-white/10 transition">← Back to Gallery</a>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="flex-grow flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-2xl bg-white/5 backdrop-blur-md rounded-2xl p-10 border border-white/10 shadow-xl">

      <h2 class="text-center text-3xl font-semibold mb-10">
        <?= $isEdit ? 'Edit Font' : 'Upload New Font' ?>
      </h2>

      <form action="../Controller/processFont.php" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="action" value="<?= $isEdit ? 'edit' : 'add' ?>">
        <?php if ($isEdit): ?>
          <input type="hidden" name="id" value="<?= $fontData['id'] ?>">
        <?php endif; ?>

        <!-- Font Name -->
        <div>
          <label class="text-sm block mb-1">Font Name</label>
          <input type="text" name="fontName" required value="<?= $fontData['font_name'] ?? '' ?>"
            class="w-full bg-neutral-900 border border-neutral-700 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <!-- Category -->
        <div>
          <label class="text-sm block mb-1">Category</label>
          <select name="category_combined_select" onchange="toggleCategoryInput(this)" required
            class="w-full bg-neutral-900 border border-neutral-700 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">– Select Category –</option>
            <?php while ($cat = $categories->fetch_assoc()): ?>
              <option value="<?= htmlspecialchars($cat['category_name']) ?>"
                <?= ($fontData && $cat['id'] == $fontData['category_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars(ucwords($cat['category_name'])) ?>
              </option>
            <?php endwhile; ?>
            <option value="__add__">+ Add New Category</option>
          </select>
          <input type="text" id="newCategoryInput" name="category_combined"
            class="mt-3 hidden w-full bg-neutral-900 border border-neutral-700 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="New category name" />
        </div>

        <!-- Style -->
        <div>
          <label class="text-sm block mb-1">Style</label>
          <input type="text" name="style" value="<?= $fontData['style'] ?? 'Regular' ?>"
            class="w-full bg-neutral-900 border border-neutral-700 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <!-- License -->
        <div>
          <label class="text-sm block mb-1">License</label>
          <input type="text" name="license" value="<?= $fontData['license'] ?? '' ?>"
            class="w-full bg-neutral-900 border border-neutral-700 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <!-- Description -->
        <div>
          <label class="text-sm block mb-1">Description</label>
          <textarea name="description" rows="4"
            class="w-full bg-neutral-900 border border-neutral-700 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= $fontData['description'] ?? '' ?></textarea>
        </div>

        <!-- Upload Fields (Add Only) -->
        <?php if (!$isEdit): ?>
          <div>
            <label class="text-sm block mb-1">Preview Font File <span class="text-neutral-400">(TTF, OTF, etc)</span></label>
            <input type="file" name="previewFontFile" accept=".ttf,.otf,.woff,.woff2" required
              class="w-full bg-neutral-900 border border-neutral-700 px-4 py-2 rounded-lg file:bg-neutral-800 file:border-0 file:px-4 file:py-2 file:text-white file:rounded-md hover:file:bg-neutral-700 transition" />
          </div>

          <div>
            <label class="text-sm block mb-1">Downloadable ZIP File</label>
            <input type="file" name="fontFile" accept=".zip" required
              class="w-full bg-neutral-900 border border-neutral-700 px-4 py-2 rounded-lg file:bg-neutral-800 file:border-0 file:px-4 file:py-2 file:text-white file:rounded-md hover:file:bg-neutral-700 transition" />
          </div>
        <?php endif; ?>

        <!-- Submit -->
        <div class="pt-6 flex flex-col text-center">
          <button type="submit"
            class="mt-4 inline-block text-center text-sm px-4 py-2 bg-white text-black rounded hover:bg-neutral-200 transition">
            <?= $isEdit ? 'Update Font' : 'Upload Font' ?>
          </button>
        </div>
      </form>
    </div>
  </main>

  <footer class="py-6 text-center text-neutral-500 text-xs border-t border-neutral-800">
    &copy; <?= date('Y') ?> FontGallery. All rights reserved.
  </footer>
</body>

</html>