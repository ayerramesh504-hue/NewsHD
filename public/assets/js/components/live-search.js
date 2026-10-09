(function () {
  function mount(root) {
    var api = root.dataset.api;
    var timer;
    var controller;

    root.innerHTML =
      '<div class="search-wrapper-inner">' +
        '<div class="live-search-box">' +
          '<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>' +
          '<input type="search" placeholder="Search news..." aria-label="Search news" autocomplete="off">' +
          '<button type="button" class="live-search-clear" aria-label="Clear search" hidden><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i></button>' +
        '</div>' +
        '<div class="live-search-results" role="listbox" hidden></div>' +
      '</div>';

    var input = root.querySelector('input');
    var clear = root.querySelector('.live-search-clear');
    var results = root.querySelector('.live-search-results');

    function closeResults() {
      results.innerHTML = '';
      results.hidden = true;
      results.classList.remove('open');
    }

    function render(items) {
      results.innerHTML = '';
      if (!items.length) {
        results.innerHTML = '<div class="live-search-empty">No results found</div>';
      } else {
        items.forEach(function (item) {
          var link = document.createElement('a');
          link.href = item.url;
          link.className = 'live-search-item';
          link.setAttribute('role', 'option');

          var title = document.createElement('strong');
          title.textContent = item.title;
          var detail = document.createElement('small');
          detail.textContent = item.category_name + ' — ' + item.excerpt;
          link.append(title, detail);
          link.addEventListener('click', closeResults);
          results.appendChild(link);
        });
      }
      results.hidden = false;
      results.classList.add('open');
    }

    input.addEventListener('input', function () {
      var query = input.value.trim();
      clear.hidden = !query;
      window.clearTimeout(timer);
      if (controller) controller.abort();
      if (query.length < 2) {
        closeResults();
        return;
      }

      timer = window.setTimeout(function () {
        controller = new AbortController();
        fetch(api + '?q=' + encodeURIComponent(query), { signal: controller.signal })
          .then(function (response) { return response.ok ? response.json() : Promise.reject(); })
          .then(function (data) { render(data.results || []); })
          .catch(function (error) {
            if (error.name !== 'AbortError') closeResults();
          });
      }, 250);
    });

    input.addEventListener('keydown', function (event) {
      if (event.key !== 'Enter' || !input.value.trim()) return;
      event.preventDefault();
      var baseUrl = window.APP_URL || window.location.origin + '/';
      window.location.assign(baseUrl.replace(/\/?$/, '/') + 'search?q=' + encodeURIComponent(input.value.trim()));
    });

    clear.addEventListener('click', function () {
      input.value = '';
      clear.hidden = true;
      closeResults();
      input.focus();
    });

    document.addEventListener('click', function (event) {
      if (!root.contains(event.target)) closeResults();
    });
  }

  document.querySelectorAll('#liveSearchRoot, #liveSearchOverlayRoot').forEach(mount);
})();
