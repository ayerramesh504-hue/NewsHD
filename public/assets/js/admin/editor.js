(function () {
  const coverDropzone = document.getElementById('coverDropzone');
  const coverFile = document.getElementById('cover_file');
  const coverBrowseBtn = document.getElementById('coverBrowseBtn');
  const coverPreview = document.getElementById('coverPreview');
  const coverImageUrl = document.getElementById('cover_image');

  if (coverDropzone && coverFile) {
    function showCover(file) {
      if (!file || !file.type.startsWith('image/')) return;
      const transfer = new DataTransfer();
      transfer.items.add(file);
      coverFile.files = transfer.files;
      coverPreview.src = URL.createObjectURL(file);
      coverPreview.hidden = false;
      coverDropzone.classList.add('has-file');
      const hint = document.querySelector('.cover-hint');
      if (hint) hint.textContent = 'Selected: ' + file.name;
    }

    function openPicker(event) {
      if (event) event.stopPropagation();
      coverFile.click();
    }

    coverDropzone.addEventListener('click', function (event) {
      if (event.target !== coverImageUrl && event.target !== coverBrowseBtn) coverFile.click();
    });
    coverDropzone.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        coverFile.click();
      }
    });
    coverDropzone.addEventListener('dragover', function (event) {
      event.preventDefault();
      coverDropzone.classList.add('is-dragging');
    });
    coverDropzone.addEventListener('dragleave', function () {
      coverDropzone.classList.remove('is-dragging');
    });
    coverDropzone.addEventListener('drop', function (event) {
      event.preventDefault();
      event.stopPropagation();
      coverDropzone.classList.remove('is-dragging');
      showCover(event.dataTransfer.files[0]);
    });
    coverFile.addEventListener('change', function () {
      showCover(coverFile.files[0]);
    });
    if (coverBrowseBtn) {
      coverBrowseBtn.addEventListener('click', openPicker);
      coverBrowseBtn.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') openPicker(event);
      });
    }
  }

  const contentEl = document.getElementById('content');
  const wordCount = document.getElementById('contentWordCount');
  if (contentEl && wordCount) {
    function updateWordCount() {
      const text = contentEl.value.trim();
      wordCount.textContent = text ? text.split(/\s+/).length : 0;
    }
    contentEl.addEventListener('input', updateWordCount);
    updateWordCount();
  }
})();
