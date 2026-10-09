(function () {
  const navToggle = document.getElementById("navToggle");
  const drawer = document.getElementById("mobileDrawer");
  const overlay = document.getElementById("mobileOverlay");
  const drawerClose = document.getElementById("mobileClose");

  function openDrawer() {
    if (!drawer || !overlay) return;
    drawer.classList.add("open");
    overlay.classList.add("open");
    drawer.setAttribute("aria-hidden", "false");
    navToggle && navToggle.setAttribute("aria-expanded", "true");
    document.body.classList.add("no-scroll");
  }

  function closeDrawer() {
    if (!drawer || !overlay) return;
    drawer.classList.remove("open");
    overlay.classList.remove("open");
    drawer.setAttribute("aria-hidden", "true");
    navToggle && navToggle.setAttribute("aria-expanded", "false");
    document.body.classList.remove("no-scroll");
  }

  if (navToggle) navToggle.addEventListener("click", openDrawer);
  if (drawerClose) drawerClose.addEventListener("click", closeDrawer);
  if (overlay) overlay.addEventListener("click", closeDrawer);
  if (drawer) {
    drawer.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", closeDrawer);
    });
  }
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && drawer && drawer.classList.contains("open"))
      closeDrawer();
  });

  // Hero slider
  const slides = document.querySelectorAll(".hero-slide");
  const dots = document.querySelectorAll(".hero-dot");
  let current = 0;
  let timer;

  function showSlide(index) {
    if (!slides.length) return;
    current = index;
    slides.forEach(function (s, i) {
      s.classList.toggle("active", i === index);
    });
    dots.forEach(function (d, i) {
      d.classList.toggle("active", i === index);
    });
  }

  function nextSlide() {
    showSlide((current + 1) % slides.length);
  }

  if (slides.length > 1) {
    timer = setInterval(nextSlide, 6000);
    dots.forEach(function (dot) {
      dot.addEventListener("click", function () {
        clearInterval(timer);
        showSlide(parseInt(dot.dataset.index, 10));
        timer = setInterval(nextSlide, 6000);
      });
    });
  }

  // Breaking ticker
  const tickerTrack = document.getElementById("breakingTrack");
  if (tickerTrack) {
    const items = tickerTrack.querySelectorAll("a");
    if (items.length > 1) {
      tickerTrack.addEventListener("mouseenter", function () {
        tickerTrack.style.animationPlayState = "paused";
      });
      tickerTrack.addEventListener("mouseleave", function () {
        tickerTrack.style.animationPlayState = "running";
      });
    } else {
      tickerTrack.style.animation = "none";
    }
  }

  // Bookmark toggle
  const bookmarkBtn = document.getElementById("bookmarkBtn");
  if (bookmarkBtn) {
    bookmarkBtn.addEventListener("click", async function () {
      const articleId = bookmarkBtn.dataset.articleId;
      try {
        const res = await fetch(window.APP_URL + "api/bookmarks", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-Token": window.CSRF_TOKEN,
          },
          body: JSON.stringify({ article_id: parseInt(articleId, 10) }),
        });
        const data = await res.json();
        if (data.success) {
          bookmarkBtn.dataset.bookmarked = data.bookmarked ? "1" : "0";
          bookmarkBtn.classList.toggle("active", !!data.bookmarked);
          bookmarkBtn.innerHTML = data.bookmarked
            ? '<i class="fa-solid fa-bookmark"></i> Saved'
            : '<i class="fa-regular fa-bookmark"></i> Save Article';
        }
      } catch (e) {
        console.error(e);
      }
    });
  }

  // Newsletter form
  const newsletterForm = document.getElementById("newsletterForm");
  if (newsletterForm) {
    newsletterForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const fd = new FormData(newsletterForm);
      try {
        const res = await fetch(newsletterForm.action, {
          method: "POST",
          body: fd,
        });
        const data = await res.json();
        alert(data.message || (data.success ? "Subscribed!" : "Failed"));
        if (data.success) newsletterForm.reset();
      } catch (err) {
        alert("Subscription failed");
      }
    });
  }

  // Infinite scroll for homepage feed
  const newsGrid = document.getElementById("newsGrid");
  const loadMoreObserver = document.getElementById("loadMoreObserver");
  if (newsGrid && loadMoreObserver) {
    let currentPage = parseInt(newsGrid.dataset.nextPage || "2", 10);
    let hasMore = newsGrid.dataset.hasMore === "1";
    let loading = false;

    const observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting || loading || !hasMore) return;
          loading = true;

          fetch(window.APP_URL + "api/feed?page=" + currentPage, {
            headers: {
              "X-Requested-With": "XMLHttpRequest",
              "X-CSRF-Token": window.CSRF_TOKEN,
            },
          })
            .then(function (res) {
              return res.json();
            })
            .then(function (data) {
              if (!data.success || !data.html) {
                hasMore = false;
                loading = false;
                return;
              }

              const container = document.createElement("div");
              container.innerHTML = data.html;
              const fragment = document.createDocumentFragment();
              container.querySelectorAll(".news-card").forEach(function (card) {
                fragment.appendChild(card);
              });
              newsGrid.appendChild(fragment);

              currentPage = data.nextPage;
              hasMore = data.hasMore;
              loading = false;
            })
            .catch(function () {
              hasMore = false;
              loading = false;
            });
        });
      },
      { rootMargin: "200px" },
    );

    observer.observe(loadMoreObserver);
  }

  // Search overlay (opened from the header nav search button)
  const searchOverlay = document.getElementById("searchOverlay");
  const navSearchBtn = document.getElementById("navSearchBtn");
  if (searchOverlay && navSearchBtn) {
    function openSearchOverlay() {
      searchOverlay.classList.add("open");
      searchOverlay.setAttribute("aria-hidden", "false");
      document.body.classList.add("no-scroll");
      setTimeout(function () {
        const overlayInput = searchOverlay.querySelector(".live-search-box input");
        if (overlayInput) overlayInput.focus();
      }, 120);
    }

    navSearchBtn.addEventListener("click", openSearchOverlay);

    searchOverlay
      .querySelectorAll("[data-search-close]")
      .forEach(function (el) {
        el.addEventListener("click", function () {
          searchOverlay.classList.remove("open");
          searchOverlay.setAttribute("aria-hidden", "true");
          document.body.classList.remove("no-scroll");
        });
      });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && searchOverlay.classList.contains("open")) {
        searchOverlay.classList.remove("open");
        searchOverlay.setAttribute("aria-hidden", "true");
        document.body.classList.remove("no-scroll");
      }
    });
  }
})();
