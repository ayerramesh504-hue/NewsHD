(function () {
  const { useState, useEffect, useRef, createElement: h } = React;
  const rootEl = document.getElementById('liveSearchRoot');
  if (!rootEl) return;

  function LiveSearch() {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState([]);
    const [open, setOpen] = useState(false);
    const api = rootEl.dataset.api;
    const timer = useRef(null);

    useEffect(function () {
      if (query.length < 2) {
        setResults([]);
        setOpen(false);
        return;
      }
      clearTimeout(timer.current);
      timer.current = setTimeout(async function () {
        try {
          const res = await fetch(api + '?q=' + encodeURIComponent(query));
          const data = await res.json();
          setResults(data.results || []);
          setOpen(true);
        } catch (e) { setResults([]); }
      }, 300);
      return function () { clearTimeout(timer.current); };
    }, [query, api]);

    return h('div', { className: 'search-wrapper-inner' },
      h('div', { className: 'live-search-box' },
        h('input', {
          type: 'search',
          placeholder: 'Search news...',
          value: query,
          onChange: function (e) { setQuery(e.target.value); },
          'aria-label': 'Search news',
        }),
        h('i', { className: 'fa-solid fa-magnifying-glass' })
      ),
      open && results.length > 0 && h('div', { className: 'live-search-results open', role: 'listbox' },
        results.map(function (item) {
          return h('a', {
            key: item.id,
            href: item.url,
            className: 'live-search-item',
            role: 'option',
            onClick: function () { setOpen(false); },
          },
            h('strong', null, item.title),
            h('small', null, item.category_name + ' — ' + item.excerpt)
          );
        })
      )
    );
  }

  ReactDOM.createRoot(rootEl).render(h(LiveSearch));
})();
