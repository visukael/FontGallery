<?php
session_start();
$conn = new mysqli("localhost", "root", "", "dbfont");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$fontId = $_GET['id'] ?? null;
if (!$fontId) {
  echo "Font ID not provided.";
  exit;
}

$sql = "SELECT fonts.*, categories.category_name, users.username FROM fonts 
        JOIN categories ON fonts.category_id = categories.id 
        JOIN users ON fonts.uploaded_by = users.id 
        WHERE fonts.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $fontId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
  echo "Font not found.";
  exit;
}
$font = $result->fetch_assoc();

$fontFamily = "font" . $font['id'];
$fontFilePath = '../Uploads/Fonts/' . basename($font['file_path']);
$fontDownloadPath = '../Uploads/Fonts/' . basename($font['download_path']);

echo "<style>@font-face { font-family: '$fontFamily'; src: url('$fontFilePath'); }</style>";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($font['font_name']) ?> - Font Details</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <style>
    .font-preview {
      font-size: 5rem;
      line-height: 1;
      font-family: '<?= $fontFamily ?>';
    }

    @media (max-width: 768px) {
      .font-preview {
        font-size: 3rem;
      }
    }
  </style>
</head>

<body class="bg-black text-white font-sans">
  <!-- Navbar -->
  <nav class="flex items-center px-8 py-6 border-b border-neutral-800">
    <a class="text-3xl font-semibold mr-6">FG</a>
    <div class="ml-auto flex gap-3">
      <a href="landingPage.php" class="text-sm px-4 py-2 border border-white/20 rounded-full hover:bg-white/10 transition">← Back to Gallery</a>
    </div>
  </nav>

  <main class="px-6 py-12 max-w-6xl mx-auto space-y-16">
    <!-- Header -->
    <header class="flex flex-col md:flex-row justify-between gap-4 items-start md:items-end">
      <div>
        <h1 class="text-4xl font-bold"><?= htmlspecialchars($font['font_name']) ?></h1>
        <div class="flex flex-wrap gap-2 text-sm text-neutral-400 mt-1">
          <span><?= htmlspecialchars($font['category_name']) ?></span>
          <span>•</span>
          <span><?= htmlspecialchars($font['style']) ?></span>
          <span>•</span>
          <span><?= htmlspecialchars($font['license']) ?></span>
        </div>
      </div>
      <a href="<?= $fontDownloadPath ?>" download class="inline-flex items-center gap-2 bg-white text-black text-sm font-medium px-5 py-2 rounded-md hover:bg-neutral-200 transition">
        Download
      </a>
    </header>

    <!-- Controls -->
    <section class="flex flex-col lg:flex-row justify-between gap-6 text-sm text-neutral-400">
      <div class="flex flex-wrap items-center gap-5">
        <!-- Style Dropdown -->
        <div class="relative">
          <button id="styleDropdownBtn" class="flex items-center gap-2 hover:text-white">
            <span id="currentStyle">Regular</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div id="styleDropdown" class="hidden absolute mt-2 z-10 w-40 bg-black rounded-md shadow-md ring-1 ring-white/10">
            <ul class="py-1">
              <li><label class="block px-4 py-2 hover:bg-white/10 cursor-pointer"><input type="radio" name="fontStyle" value="normal-400" class="mr-2" checked>Regular</label></li>
              <li><label class="block px-4 py-2 hover:bg-white/10 cursor-pointer"><input type="radio" name="fontStyle" value="italic-400" class="mr-2">Italic</label></li>
              <li><label class="block px-4 py-2 hover:bg-white/10 cursor-pointer"><input type="radio" name="fontStyle" value="normal-700" class="mr-2">Bold</label></li>
            </ul>
          </div>
        </div>

        <!-- Reset & Size -->
        <button id="resetPreview" class="hover:text-white">Reset</button>
        <div class="flex items-center gap-2">
          <span id="fontSizeLabel">100px</span>
          <input type="range" id="fontSize" min="24" max="200" value="100" class="w-40 accent-blue-500">
        </div>
      </div>

      <!-- Export Buttons -->
      <div class="flex gap-4">
        <button id="downloadPNG" class="hover:text-white">.png</button>
        <button id="downloadJPEG" class="hover:text-white">.jpeg</button>
      </div>
    </section>

    <!-- Font Preview -->
    <section class="bg-gradient-to-br from-[#161616] to-[#0f0f0f] border border-white/10 rounded-2xl px-6 py-12 text-center">
      <div id="mainPreview" class="font-preview max-w-5xl mx-auto text-white font-semibold tracking-tight leading-tight outline-none" contenteditable="true" spellcheck="false">
        Almost before we knew it, we had left the ground.
      </div>
      <p class="mt-4 text-sm text-neutral-500 italic">Click to edit preview</p>
    </section>

    <!-- Info Sections -->
    <section class="grid grid-cols-1 md:grid-cols-2 gap-12">
      <div>
        <h2 class="text-2xl font-bold mb-4">About this Font</h2>
        <p class="text-neutral-300 leading-relaxed text-justify"><?= nl2br(htmlspecialchars($font['description'])) ?></p>
      </div>
      <div>
        <h2 class="text-2xl font-bold mb-4">License</h2>
        <div class="bg-[#121212] p-6 rounded-2xl border border-white/10 min-h-[150px]">
          <p class="text-neutral-300 leading-relaxed text-justify">
            <?= htmlspecialchars($font['license']) ?>.
          </p>
        </div>

      </div>
    </section>

    <!-- Specs -->
    <section class="bg-[#121212] p-8 rounded-2xl border border-white/10">
      <h2 class="text-2xl font-bold mb-6">Font Specifications</h2>
      <dl class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-12 text-sm text-neutral-300">
        <div class="space-y-1">
          <dt class="font-medium">Kategori</dt>
          <dd class="text-white"><?= htmlspecialchars($font['category_name']) ?></dd>
        </div>
        <div class="space-y-1">
          <dt class="font-medium">Jumlah Style</dt>
          <dd class="text-white">1</dd>
        </div>
        <div class="space-y-1">
          <dt class="font-medium">Lisensi</dt>
          <dd class="text-white"><?= htmlspecialchars($font['license']) ?></dd>
        </div>
        <div class="space-y-1">
          <dt class="font-medium">Designer</dt>
          <dd class="text-white"><?= htmlspecialchars($font['username']) ?></dd>
        </div>
        <div class="space-y-1">
          <dt class="font-medium">Release Date</dt>
          <dd class="text-white"><?= date('Y', strtotime($font['created_at'])) ?></dd>
        </div>
        <div class="space-y-1">
          <dt class="font-medium">Version</dt>
          <dd class="text-white">1.0</dd>
        </div>
      </dl>
    </section>
  </main>

  <footer class="py-6 text-center text-neutral-500 text-xs border-t border-neutral-800">
    &copy; <?= date('Y') ?> FontGallery. All rights reserved.
  </footer>

  <script>
    const preview = document.getElementById('mainPreview');
    const fontSizeSlider = document.getElementById('fontSize');
    const fontSizeLabel = document.getElementById('fontSizeLabel');
    const previewContainer = preview.parentElement;

    // Simpan nilai default saat halaman dimuat
    const defaultFontSize = '80';
    const defaultFontStyle = 'normal';
    const defaultFontWeight = '400';
    const defaultText = 'Almost before we knew it, we had left the ground.';
    const defaultStyleLabel = 'Regular';

    fontSizeSlider.addEventListener('input', () => {
      const size = fontSizeSlider.value + 'px';
      fontSizeLabel.textContent = size;
      document.querySelectorAll('.font-preview').forEach(block => {
        block.style.fontSize = size;
      });
    });

    function downloadAsImage(type = 'png') {
      const clone = preview.cloneNode(true);
      const currentFontSize = window.getComputedStyle(preview).fontSize;
      const isDark = !previewContainer.classList.contains('bg-white');

      clone.style.margin = '0';
      clone.style.padding = '40px';
      clone.style.backgroundColor = isDark ? 'transparent' : '#ffffff';
      clone.style.color = isDark ? '#ffffff' : '#000000';
      clone.style.fontSize = currentFontSize;
      clone.style.fontFamily = window.getComputedStyle(preview).fontFamily;
      clone.style.display = 'inline-block';
      clone.style.borderRadius = '1rem';

      const container = document.createElement('div');
      container.style.position = 'absolute';
      container.style.top = '-9999px';
      container.appendChild(clone);
      document.body.appendChild(container);

      html2canvas(clone, {
        backgroundColor: null,
        scale: 3,
        useCORS: true
      }).then(canvas => {
        const link = document.createElement('a');
        link.download = `font-preview.${type}`;
        link.href = canvas.toDataURL(`image/${type}`);
        link.click();
        document.body.removeChild(container);
      });
    }

    document.getElementById('downloadPNG').addEventListener('click', () => downloadAsImage('png'));
    document.getElementById('downloadJPEG').addEventListener('click', () => downloadAsImage('jpeg'));

    const previewText = document.getElementById('mainPreview');
    const dropdownBtn = document.getElementById('styleDropdownBtn');
    const dropdownMenu = document.getElementById('styleDropdown');
    const currentStyle = document.getElementById('currentStyle');

    dropdownBtn.addEventListener('click', () => {
      dropdownMenu.classList.toggle('hidden');
    });

    document.querySelectorAll('input[name="fontStyle"]').forEach(radio => {
      radio.addEventListener('change', () => {
        const [style, weight] = radio.value.split('-');
        previewText.style.fontStyle = style;
        previewText.style.fontWeight = weight;
        currentStyle.textContent = radio.parentElement.textContent.trim();
        dropdownMenu.classList.add('hidden');
      });
    });

    document.getElementById('resetPreview').addEventListener('click', () => {
      preview.innerText = defaultText;
      preview.style.fontSize = defaultFontSize + 'px';
      fontSizeSlider.value = defaultFontSize;
      fontSizeLabel.textContent = defaultFontSize + 'px';
      preview.style.fontStyle = defaultFontStyle;
      preview.style.fontWeight = defaultFontWeight;
      currentStyle.textContent = defaultStyleLabel;

      const defaultRadio = document.querySelector(`input[value="${defaultFontStyle}-${defaultFontWeight}"]`);
      if (defaultRadio) defaultRadio.checked = true;
    });
  </script>

</body>

</html>