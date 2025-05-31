// Ambil elemen preview sekali saja
const preview = document.getElementById('mainPreview');

// Daftar glyph: uppercase, lowercase, digits
const glyphs = [
    ..."ABCDEFGHIJKLMNOPQRSTUVWXYZ",
    ..."abcdefghijklmnopqrstuvwxyz",
    ..."0123456789"
  ];  

// Ambil fontId dari query string URL (jika ada), fallback ke default
const params = new URLSearchParams(window.location.search);
const fontId = params.get('id') || null;

// Letter-spacing
const ls = document.getElementById('letterSpacing');
const lsVal = document.getElementById('letterSpacingValue');
if (ls) {
  ls.addEventListener('input', () => {
    preview.style.letterSpacing = ls.value + 'px';
    lsVal.textContent = ls.value;
  });
}

// Line-height
const lh = document.getElementById('lineHeight');
const lhVal = document.getElementById('lineHeightValue');
if (lh) {
  lh.addEventListener('input', () => {
    preview.style.lineHeight = lh.value;
    lhVal.textContent = lh.value;
  });
}

// Preset Sample Text
document.getElementById('sampleText').addEventListener('change', function() {
    if (this.value) preview.textContent = this.value;
  });
  
// Mock font data - in a real app this would come from an API
const fontData = {
    id: 1,
    name: "Example Font",
    category: "Display",
    license: "SIL Open Font License, Version 1.1",
    styles: ["Regular"],
    description: "A modern display font with clean lines and geometric shapes. Perfect for headlines and branding.",
    designer: "Jane Designer",
    release: "2022",
    version: "1.0.3"
  };
  
  // Load font data
  document.addEventListener('DOMContentLoaded', function() {
    // Set font data
    document.getElementById('fontName').textContent = fontData.name;
    document.getElementById('fontCategory').textContent = fontData.category;
    document.getElementById('fontLicense').textContent = fontData.license;
    document.getElementById('fontDescription').textContent = fontData.description;
    document.getElementById('fontDesigner').textContent = fontData.designer;
    document.getElementById('fontRelease').textContent = fontData.release;
    document.getElementById('fontVersion').textContent = fontData.version;

    // Set font styles
    const stylesContainer = document.getElementById('stylesContainer');
    fontData.styles.forEach(style => {
      const styleCard = document.createElement('div');
      styleCard.className = 'bg-[#121212] rounded-2xl p-6';
      styleCard.innerHTML = `
        <div class="text-4xl mb-4">${style}</div>
        <div class="text-2xl" style="font-family: '${fontData.name}'">AaBbCc</div>
      `;
      stylesContainer.appendChild(styleCard);

      // Fungsi untuk meng-update section Spesifikasi
      function updateSpecs(font) {
        document.getElementById('specCategory').textContent = font.category;
        document.getElementById('specStyles').textContent   = font.styles.length + ' style(s)';
        document.getElementById('specLicense').textContent  = font.license;
        document.getElementById('specDesigner').textContent = font.designer;
        document.getElementById('specRelease').textContent  = font.release;
        document.getElementById('specVersion').textContent  = font.version;
      }

      // Copy CSS Snippet
        document.getElementById('copyCss').addEventListener('click', () => {
            const style = window.getComputedStyle(preview);
            const cssText = `
        font-family: '${fontData.name}';
        font-size: ${style.fontSize};
        color: ${style.color};
        letter-spacing: ${style.letterSpacing};
        line-height: ${style.lineHeight};
        text-align: ${style.textAlign};
        `;
            navigator.clipboard.writeText(cssText).then(() => {
            alert('CSS copied to clipboard!');
            });
        });

        // Preview Mode (Heading vs Body)
        document.getElementById('previewMode').addEventListener('change', ({ target }) => {
            if (target.value === 'heading') {
            preview.style.fontSize = '4rem';
            preview.style.lineHeight = '1.1';
            } else {
            preview.style.fontSize = '18px';
            preview.style.lineHeight = '1.6';
            }
        });
  
    });
  
    // Set edit form values
    document.getElementById('editFontName').value = fontData.name;
    document.getElementById('editFontCategory').value = fontData.category;
    document.getElementById('editFontLicense').value = fontData.license;
    document.getElementById('editFontDescription').value = fontData.description;
  });
  
  // Preview controls
  document.getElementById('sizeSlider').addEventListener('input', function() {
    document.getElementById('mainPreview').style.fontSize = this.value + 'px';
  });
  
  document.getElementById('colorPicker').addEventListener('input', function() {
    document.getElementById('mainPreview').style.color = this.value;
  });
  
  document.getElementById('textAlign').addEventListener('change', function() {
    document.getElementById('mainPreview').style.textAlign = this.value;
  });
  
  document.getElementById('customText').addEventListener('input', function() {
    if (this.value) {
      document.getElementById('mainPreview').textContent = this.value;
    } else {
      document.getElementById('mainPreview').textContent = 'Almost before we knew it, we had left the ground.';
    }
  });
  
  // Edit font functionality
  document.getElementById('editButton').addEventListener('click', function() {
    document.getElementById('editFontModal').classList.remove('hidden');
    document.getElementById('editFontModal').scrollIntoView({ behavior: 'smooth' });
  });
  
  document.getElementById('cancelEdit').addEventListener('click', function() {
    document.getElementById('editFontModal').classList.add('hidden');
  });
  
  document.getElementById('editFontForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // In a real app, this would send data to the server
    fontData.name = document.getElementById('editFontName').value;
    fontData.category = document.getElementById('editFontCategory').value;
    fontData.license = document.getElementById('editFontLicense').value;
    fontData.description = document.getElementById('editFontDescription').value;
    
    // Update the display
    document.getElementById('fontName').textContent = fontData.name;
    document.getElementById('fontCategory').textContent = fontData.category;
    document.getElementById('fontLicense').textContent = fontData.license;
    document.getElementById('fontDescription').textContent = fontData.description;
    
    document.getElementById('editFontModal').classList.add('hidden');
    alert('Font updated successfully!');
  });
  
  document.getElementById('deleteFont').addEventListener('click', function() {
    if (confirm('Are you sure you want to delete this font? This action cannot be undone.')) {
      alert('Font deleted!');
      window.location.href = 'index.html';
    }
  });
  
  // Admin button
  document.getElementById('adminButton').addEventListener('click', function() {
    window.location.href = 'admin.html';
  });
  
  // Glyph Explorer Functionality
    document.addEventListener('DOMContentLoaded', function() {
    const glyphPreview = document.getElementById('glyphPreview');
    const glyphName = document.getElementById('glyphName');
    const glyphCode = document.getElementById('glyphCode');
    const weightSelect = document.getElementById('weightSelect');
    const styleSelect = document.getElementById('styleSelect');
    
    // Handle glyph item clicks
    document.querySelectorAll('.glyph-item').forEach(item => {
      item.addEventListener('click', function() {
        // Remove previous selection
        document.querySelectorAll('.glyph-item.selected').forEach(el => {
          el.classList.remove('selected');
        });
        
        // Add selection to clicked item
        this.classList.add('selected');
        
        // Update preview
        const char = this.textContent;
        glyphPreview.innerHTML = char;
        glyphName.textContent = char;
        glyphCode.textContent = `U+${char.charCodeAt(0).toString(16).toUpperCase().padStart(4, '0')}`;
        
        // Apply current weight and style
        updateGlyphStyle();
      });
    });
    
    // Handle weight and style changes
    weightSelect.addEventListener('change', updateGlyphStyle);
    styleSelect.addEventListener('change', updateGlyphStyle);
    
    function updateGlyphStyle() {
      const weight = weightSelect.value;
      const style = styleSelect.value;
      
      glyphPreview.style.fontWeight = weight;
      glyphPreview.style.fontStyle = style;
    }
    
    // Handle glyph option clicks
    document.querySelectorAll('.glyph-option').forEach(option => {
        option.addEventListener('click', function() {
        // Remove previous selection in this group
        this.parentNode.querySelectorAll('.glyph-option.selected').forEach(el => {
            el.classList.remove('selected');
        });
        
        // Add selection to clicked option
        this.classList.add('selected');
        
        // Apply the selected style to the preview
        const style = this.dataset.style;
        applyGlyphStyle(style);
        });
    });
    
    // Function to apply glyph style
    function applyGlyphStyle(style) {
        const glyphPreview = document.getElementById('glyphPreview');
        
        // Reset all style classes
        glyphPreview.classList.remove('solid-style', 'outline-style', 'u1-style',
        'light-style', 'regular-style',
        'semibold-style', 'solid-build-style');
        
        // Add the selected style class
        switch(style) {
        case 'solid':
            glyphPreview.classList.add('solid-style');
            break;
        case 'outline':
            glyphPreview.classList.add('outline-style');
            break;
        case 'u1':
            glyphPreview.classList.add('u1-style');
            break;
        case 'light':
            glyphPreview.classList.add('light-style');
            break;
        case 'regular':
            glyphPreview.classList.add('regular-style');
            break;
        case 'semibold':
            glyphPreview.classList.add('semibold-style');
            break;
        case 'solid-build':
            glyphPreview.classList.add('solid-build-style');
            break;
        }
    }

    // Handle "Type your letters"
    document.getElementById('type-letters').addEventListener('click', function() {
        const userInput = prompt("Masukkan teks yang ingin ditampilkan:");
        if (userInput) {
            const glyphPreview = document.getElementById('glyphPreview');
            glyphPreview.textContent = userInput;
        }
    });

    // Handle all glyph option clicks
    document.querySelectorAll('.glyph-option').forEach(option => {
        option.addEventListener('click', function() {
            // Remove previous selection in this group
            this.parentNode.querySelectorAll('.glyph-option.selected').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Add selection to clicked option
            this.classList.add('selected');
            
            // Apply the selected style to the preview
            const style = this.dataset.style;
            applyGlyphStyle(style);
        });
    });
  });

  // Download button
  document.getElementById('downloadButton').addEventListener('click', function() {
    document.getElementById('mainPreview').scrollIntoView({ behavior: 'smooth', block: 'start' });
    window.open('https://fontshare.com/fonts/clash-display', '_blank');
  });
  
  