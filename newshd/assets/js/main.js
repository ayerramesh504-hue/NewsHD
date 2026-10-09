(function () {
  const navToggle = document.getElementById('navToggle');
  const menu = document.getElementById('mainMenu');

  if (navToggle && menu) {
    navToggle.addEventListener('click', function () {
      const open = menu.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  const slides = document.querySelectorAll('.hero-slide');
  const dots = document.querySelectorAll('.hero-dot');
  let current = 0;
  let timer;

  function showSlide(index) {
    if (!slides.length) return;
    current = index;
    slides.forEach(function (s, i) { s.classList.toggle('active', i === index); });
    dots.forEach(function (d, i) { d.classList.toggle('active', i === index); });
  }

  function nextSlide() {
    showSlide((current + 1) % slides.length);
  }

  if (slides.length > 1) {
    timer = setInterval(nextSlide, 6000);
    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        clearInterval(timer);
        showSlide(parseInt(dot.dataset.index, 10));
        timer = setInterval(nextSlide, 6000);
      });
    });
  }

  const bookmarkBtn = document.getElementById('bookmarkBtn');
  if (bookmarkBtn) {
    bookmarkBtn.addEventListener('click', async function () {
      const articleId = bookmarkBtn.dataset.articleId;
      try {
        const res = await fetch(window.APP_URL + '/api/bookmarks.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
          body: JSON.stringify({ article_id: parseInt(articleId, 10) }),
        });
        const data = await res.json();
        if (data.success) {
          bookmarkBtn.dataset.bookmarked = data.bookmarked ? '1' : '0';
          bookmarkBtn.innerHTML = data.bookmarked
            ? '<i class="fa-solid fa-bookmark"></i> Saved'
            : '<i class="fa-regular fa-bookmark"></i> Save Article';
        }
      } catch (e) { console.error(e); }
    });
  }

  const newsletterForm = document.getElementById('newsletterForm');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      const fd = new FormData(newsletterForm);
      try {
        const res = await fetch(newsletterForm.action, { method: 'POST', body: fd });
        const data = await res.json();
        alert(data.message || (data.success ? 'Subscribed!' : 'Failed'));
        if (data.success) newsletterForm.reset();
      } catch (err) { alert('Subscription failed'); }
    });
  }
})();
