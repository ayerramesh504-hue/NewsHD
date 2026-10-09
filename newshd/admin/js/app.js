(function () {
  'use strict';
  var h = React.createElement;
  var useState = React.useState;
  var useEffect = React.useEffect;
  var useRef = React.useRef;
  var useCallback = React.useCallback;

  var rootEl = document.getElementById('adminRoot');
  if (!rootEl) return;

  var API = rootEl.dataset.api;
  var SITE_URL = rootEl.dataset.siteUrl;
  var LOGOUT_URL = rootEl.dataset.logoutUrl;
  var USER_NAME = rootEl.dataset.userName;
  var USER_ROLE = rootEl.dataset.userRole;
  var ROLE_SLUG = rootEl.dataset.roleSlug;
  var CSRF = window.CSRF_TOKEN;

  /* ---------------- API helpers ---------------- */
  function qs(params) {
    return Object.keys(params || {})
      .filter(function (k) { return params[k] !== undefined && params[k] !== null && params[k] !== ''; })
      .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); })
      .join('&');
  }

  function apiGet(resource, params) {
    var query = qs(Object.assign({ resource: resource }, params || {}));
    return fetch(API + '?' + query, { credentials: 'same-origin' }).then(function (r) { return r.json(); });
  }

  function apiSend(resource, body, method) {
    return fetch(API + '?resource=' + encodeURIComponent(resource), {
      method: method || 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify(body || {}),
    }).then(function (r) { return r.json(); });
  }

  /* ---------------- Small shared UI pieces ---------------- */
  function Alert(props) {
    if (!props.message) return null;
    return h('div', { className: 'alert alert-' + (props.type || 'success') }, props.message);
  }

  function Badge(props) {
    return h('span', { className: 'badge badge-' + props.status }, props.label || props.status);
  }

  function Modal(props) {
    return h('div', { className: 'modal-overlay', onClick: function (e) { if (e.target === e.currentTarget) props.onClose(); } },
      h('div', { className: 'modal' },
        h('h3', null, props.title),
        props.children
      )
    );
  }

  function Pagination(props) {
    var p = props.pagination;
    if (!p || p.total_pages <= 1) return null;
    var pages = [];
    for (var i = 1; i <= p.total_pages; i++) pages.push(i);
    return h('div', { className: 'pagination' },
      h('button', { disabled: !p.has_prev, onClick: function () { props.onChange(p.current - 1); } }, '\u00AB Prev'),
      pages.map(function (n) {
        return h('button', { key: n, className: n === p.current ? 'active' : '', onClick: function () { props.onChange(n); } }, n);
      }),
      h('button', { disabled: !p.has_next, onClick: function () { props.onChange(p.current + 1); } }, 'Next \u00BB')
    );
  }

  function ConfirmDelete(onConfirm) {
    if (window.confirm('Are you sure? This action cannot be undone.')) onConfirm();
  }

  function slugify(text) {
    return (text || '').toString().toLowerCase().trim()
      .replace(/[^a-z0-9\s-]/g, '').replace(/[\s-]+/g, '-').replace(/^-+|-+$/g, '');
  }

  /* ---------------- Simple HTML content editor (dependency-free) ---------------- */
  function MiniEditor(props) {
    var taRef = useRef(null);

    function wrap(before, after) {
      var ta = taRef.current;
      if (!ta) return;
      var start = ta.selectionStart, end = ta.selectionEnd;
      var val = ta.value;
      var selected = val.slice(start, end) || 'text';
      var next = val.slice(0, start) + before + selected + (after || before) + val.slice(end);
      props.onChange(next);
      requestAnimationFrame(function () {
        ta.focus();
        ta.selectionStart = start + before.length;
        ta.selectionEnd = start + before.length + selected.length;
      });
    }

    function insertLink() {
      var url = window.prompt('Enter URL:', 'https://');
      if (url) wrap('<a href="' + url + '">', '</a>');
    }

    return h('div', { className: 'form-group' },
      h('label', null, props.label || 'Content'),
      h('div', { style: { display: 'flex', gap: '6px', marginBottom: '8px', flexWrap: 'wrap' } },
        h('button', { type: 'button', className: 'btn btn-secondary btn-sm', onClick: function () { wrap('<h2>', '</h2>'); } }, 'H2'),
        h('button', { type: 'button', className: 'btn btn-secondary btn-sm', onClick: function () { wrap('<strong>', '</strong>'); } }, 'Bold'),
        h('button', { type: 'button', className: 'btn btn-secondary btn-sm', onClick: function () { wrap('<em>', '</em>'); } }, 'Italic'),
        h('button', { type: 'button', className: 'btn btn-secondary btn-sm', onClick: function () { wrap('<p>', '</p>'); } }, 'Paragraph'),
        h('button', { type: 'button', className: 'btn btn-secondary btn-sm', onClick: function () { wrap('<blockquote>', '</blockquote>'); } }, 'Quote'),
        h('button', { type: 'button', className: 'btn btn-secondary btn-sm', onClick: insertLink }, 'Link')
      ),
      h('textarea', {
        ref: taRef, rows: props.rows || 10, value: props.value,
        onChange: function (e) { props.onChange(e.target.value); },
        placeholder: 'Write the article body using HTML tags (basic formatting toolbar above).',
      }),
      h('small', { style: { color: '#64748b' } }, 'Lightweight HTML editor. A full WYSIWYG (TinyMCE/Quill) integration is listed as a future enhancement.')
    );
  }

  /* ---------------- Dashboard ---------------- */
  function Dashboard() {
    var stateArr = useState(null); var stats = stateArr[0]; var setStats = stateArr[1];
    var chartRef = useRef(null);
    var chartInstance = useRef(null);

    useEffect(function () {
      apiGet('dashboard').then(function (res) { if (res.success) setStats(res.stats); });
    }, []);

    useEffect(function () {
      if (!stats || !chartRef.current || typeof Chart === 'undefined') return;
      var labels = stats.chart.map(function (c) { return c.day; });
      var data = stats.chart.map(function (c) { return c.count; });
      if (chartInstance.current) chartInstance.current.destroy();
      chartInstance.current = new Chart(chartRef.current.getContext('2d'), {
        type: 'line',
        data: { labels: labels, datasets: [{ label: 'Site Activity (7 days)', data: data, borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,0.1)', tension: 0.3, fill: true }] },
        options: { responsive: true, plugins: { legend: { display: false } } },
      });
      return function () { if (chartInstance.current) chartInstance.current.destroy(); };
    }, [stats]);

    if (!stats) return h('p', null, 'Loading dashboard\u2026');

    var cards = [
      ['Articles', stats.articles, 'fa-newspaper'],
      ['Users', stats.users, 'fa-users'],
      ['Comments', stats.comments, 'fa-comments'],
      ['Pending Comments', stats.pending_comments, 'fa-hourglass-half'],
      ['Total Page Views', stats.page_views, 'fa-eye'],
      ['New Messages', stats.messages, 'fa-envelope'],
    ];

    return h('div', null,
      h('div', { className: 'stats-grid' },
        cards.map(function (c) {
          return h('div', { className: 'stat-card', key: c[0] },
            h('h3', null, Number(c[1]).toLocaleString()),
            h('p', null, h('i', { className: 'fa-solid ' + c[2] }), ' ' + c[0])
          );
        })
      ),
      h('div', { className: 'chart-card' },
        h('h3', { style: { marginBottom: '16px' } }, 'Weekly Activity'),
        h('canvas', { ref: chartRef, height: 90 })
      )
    );
  }

  /* ---------------- Articles ---------------- */
  function ArticlesManager() {
    var s1 = useState([]); var rows = s1[0]; var setRows = s1[1];
    var s2 = useState(null); var pagination = s2[0]; var setPagination = s2[1];
    var s3 = useState(1); var page = s3[0]; var setPage = s3[1];
    var s4 = useState(''); var search = s4[0]; var setSearch = s4[1];
    var s5 = useState([]); var categories = s5[0]; var setCategories = s5[1];
    var s6 = useState(null); var editing = s6[0]; var setEditing = s6[1];
    var s7 = useState(''); var msg = s7[0]; var setMsg = s7[1];

    var load = useCallback(function () {
      apiGet('articles', { page: page, q: search }).then(function (res) {
        if (res.success) { setRows(res.data); setPagination(res.pagination); }
      });
    }, [page, search]);

    useEffect(function () { load(); }, [load]);
    useEffect(function () { apiGet('categories').then(function (res) { if (res.success) setCategories(res.data); }); }, []);

    function openNew() {
      setEditing({ id: 0, title: '', slug: '', excerpt: '', content: '', category_id: categories[0] ? categories[0].id : '', status: 'draft', is_featured: false, is_breaking: false, cover_image: '', tags: '' });
    }

    function save(e) {
      e.preventDefault();
      apiSend('articles', editing).then(function (res) {
        if (res.success) { setEditing(null); setMsg('Article saved.'); load(); }
        else setMsg(res.message || 'Failed to save article.');
      });
    }

    function remove(id) {
      ConfirmDelete(function () {
        apiSend('articles', { id: id }, 'DELETE').then(function (res) { if (res.success) load(); });
      });
    }

    return h('div', null,
      h(Alert, { message: msg }),
      h('div', { className: 'data-table-wrap' },
        h('div', { className: 'table-toolbar' },
          h('input', { placeholder: 'Search articles\u2026', value: search, style: { maxWidth: '260px' }, onChange: function (e) { setPage(1); setSearch(e.target.value); } }),
          h('button', { className: 'btn btn-primary', onClick: openNew }, h('i', { className: 'fa-solid fa-plus' }), ' New Article')
        ),
        h('table', { className: 'data-table' },
          h('thead', null, h('tr', null, h('th', null, 'Title'), h('th', null, 'Category'), h('th', null, 'Author'), h('th', null, 'Status'), h('th', null, 'Views'), h('th', null, 'Published'), h('th', null, 'Actions'))),
          h('tbody', null, rows.map(function (a) {
            return h('tr', { key: a.id },
              h('td', null, a.title),
              h('td', null, a.category_name),
              h('td', null, a.author_name),
              h('td', null, h(Badge, { status: a.status })),
              h('td', null, Number(a.view_count).toLocaleString()),
              h('td', null, a.published_at || '\u2014'),
              h('td', null,
                h('button', { className: 'btn btn-secondary btn-sm', onClick: function () { setEditing(Object.assign({}, a, { tags: a.tag_names || '' })); } }, 'Edit'),
                ' ',
                h('button', { className: 'btn btn-danger btn-sm', onClick: function () { remove(a.id); } }, 'Delete')
              )
            );
          }))
        ),
        h(Pagination, { pagination: pagination, onChange: setPage })
      ),
      editing && h(Modal, { title: editing.id ? 'Edit Article' : 'New Article', onClose: function () { setEditing(null); } },
        h('form', { onSubmit: save },
          h('div', { className: 'form-group' },
            h('label', null, 'Title'),
            h('input', { required: true, value: editing.title, onChange: function (e) { setEditing(Object.assign({}, editing, { title: e.target.value, slug: editing.slug || slugify(e.target.value) })); } })
          ),
          h('div', { className: 'form-row' },
            h('div', { className: 'form-group' },
              h('label', null, 'Slug'),
              h('input', { value: editing.slug, onChange: function (e) { setEditing(Object.assign({}, editing, { slug: e.target.value })); } })
            ),
            h('div', { className: 'form-group' },
              h('label', null, 'Category'),
              h('select', { value: editing.category_id, onChange: function (e) { setEditing(Object.assign({}, editing, { category_id: e.target.value })); } },
                categories.map(function (c) { return h('option', { key: c.id, value: c.id }, c.name); })
              )
            )
          ),
          h('div', { className: 'form-group' },
            h('label', null, 'Cover Image URL'),
            h('input', { value: editing.cover_image || '', placeholder: 'https://\u2026', onChange: function (e) { setEditing(Object.assign({}, editing, { cover_image: e.target.value })); } })
          ),
          h('div', { className: 'form-group' },
            h('label', null, 'Excerpt'),
            h('textarea', { rows: 2, value: editing.excerpt || '', onChange: function (e) { setEditing(Object.assign({}, editing, { excerpt: e.target.value })); } })
          ),
          h(MiniEditor, { value: editing.content || '', onChange: function (v) { setEditing(Object.assign({}, editing, { content: v })); } }),
          h('div', { className: 'form-group' },
            h('label', null, 'Tags (comma separated)'),
            h('input', { value: editing.tags || '', onChange: function (e) { setEditing(Object.assign({}, editing, { tags: e.target.value })); } })
          ),
          h('div', { className: 'form-row' },
            h('div', { className: 'form-group' },
              h('label', null, 'Status'),
              h('select', { value: editing.status, onChange: function (e) { setEditing(Object.assign({}, editing, { status: e.target.value })); } },
                h('option', { value: 'draft' }, 'Draft'),
                h('option', { value: 'published' }, 'Published'),
                h('option', { value: 'scheduled' }, 'Scheduled')
              )
            ),
            h('div', { className: 'form-group' },
              h('label', null,
                h('input', { type: 'checkbox', checked: Number(editing.is_featured) === 1 || editing.is_featured === true, onChange: function (e) { setEditing(Object.assign({}, editing, { is_featured: e.target.checked })); } }),
                ' Featured'
              ),
              h('label', null,
                h('input', { type: 'checkbox', checked: Number(editing.is_breaking) === 1 || editing.is_breaking === true, onChange: function (e) { setEditing(Object.assign({}, editing, { is_breaking: e.target.checked })); } }),
                ' Breaking News'
              )
            )
          ),
          h('div', { style: { display: 'flex', gap: '10px', marginTop: '10px' } },
            h('button', { type: 'submit', className: 'btn btn-primary' }, 'Save Article'),
            h('button', { type: 'button', className: 'btn btn-secondary', onClick: function () { setEditing(null); } }, 'Cancel')
          )
        )
      )
    );
  }

  /* ---------------- Categories ---------------- */
  function CategoriesManager() {
    var s1 = useState([]); var rows = s1[0]; var setRows = s1[1];
    var s2 = useState(null); var editing = s2[0]; var setEditing = s2[1];

    function load() { apiGet('categories').then(function (res) { if (res.success) setRows(res.data); }); }
    useEffect(function () { load(); }, []);

    function save(e) {
      e.preventDefault();
      apiSend('categories', editing).then(function (res) { if (res.success) { setEditing(null); load(); } });
    }
    function remove(id) {
      ConfirmDelete(function () { apiSend('categories', { id: id }, 'DELETE').then(function (res) { if (res.success) load(); }); });
    }

    return h('div', null,
      h('div', { className: 'data-table-wrap' },
        h('div', { className: 'table-toolbar' },
          h('strong', null, 'Categories'),
          h('button', { className: 'btn btn-primary', onClick: function () { setEditing({ id: 0, name: '', slug: '', description: '' }); } }, h('i', { className: 'fa-solid fa-plus' }), ' New Category')
        ),
        h('table', { className: 'data-table' },
          h('thead', null, h('tr', null, h('th', null, 'Name'), h('th', null, 'Slug'), h('th', null, 'Description'), h('th', null, 'Actions'))),
          h('tbody', null, rows.map(function (c) {
            return h('tr', { key: c.id },
              h('td', null, c.name), h('td', null, c.slug), h('td', null, c.description || '\u2014'),
              h('td', null,
                h('button', { className: 'btn btn-secondary btn-sm', onClick: function () { setEditing(c); } }, 'Edit'), ' ',
                h('button', { className: 'btn btn-danger btn-sm', onClick: function () { remove(c.id); } }, 'Delete')
              )
            );
          }))
        )
      ),
      editing && h(Modal, { title: editing.id ? 'Edit Category' : 'New Category', onClose: function () { setEditing(null); } },
        h('form', { onSubmit: save },
          h('div', { className: 'form-group' }, h('label', null, 'Name'),
            h('input', { required: true, value: editing.name, onChange: function (e) { setEditing(Object.assign({}, editing, { name: e.target.value, slug: editing.slug || slugify(e.target.value) })); } })),
          h('div', { className: 'form-group' }, h('label', null, 'Slug'),
            h('input', { value: editing.slug, onChange: function (e) { setEditing(Object.assign({}, editing, { slug: e.target.value })); } })),
          h('div', { className: 'form-group' }, h('label', null, 'Description'),
            h('textarea', { rows: 3, value: editing.description || '', onChange: function (e) { setEditing(Object.assign({}, editing, { description: e.target.value })); } })),
          h('div', { style: { display: 'flex', gap: '10px' } },
            h('button', { type: 'submit', className: 'btn btn-primary' }, 'Save'),
            h('button', { type: 'button', className: 'btn btn-secondary', onClick: function () { setEditing(null); } }, 'Cancel')
          )
        )
      )
    );
  }

  /* ---------------- Users ---------------- */
  function UsersManager() {
    var s1 = useState([]); var rows = s1[0]; var setRows = s1[1];
    var s2 = useState([]); var roles = s2[0]; var setRoles = s2[1];

    function load() { apiGet('users').then(function (res) { if (res.success) setRows(res.data); }); }
    useEffect(function () { load(); apiGet('roles').then(function (res) { if (res.success) setRoles(res.data); }); }, []);

    function isBanned(u) { return Number(u.is_banned) === 1; }

    function updateUser(u, field, value) {
      var payload = { id: u.id, role_id: field === 'role_id' ? value : roles.find(function (r) { return r.slug === u.role_slug; }) ? roles.find(function (r) { return r.slug === u.role_slug; }).id : 4, is_banned: field === 'is_banned' ? value : u.is_banned };
      apiSend('users', payload).then(function (res) { if (res.success) load(); });
    }

    return h('div', { className: 'data-table-wrap' },
      h('div', { className: 'table-toolbar' }, h('strong', null, 'Registered Users')),
      h('table', { className: 'data-table' },
        h('thead', null, h('tr', null, h('th', null, 'Name'), h('th', null, 'Email'), h('th', null, 'Role'), h('th', null, 'Status'), h('th', null, 'Joined'), h('th', null, 'Actions'))),
        h('tbody', null, rows.map(function (u) {
          return h('tr', { key: u.id },
            h('td', null, u.name), h('td', null, u.email),
            h('td', null,
              h('select', { defaultValue: u.role_slug, onChange: function (e) {
                var role = roles.find(function (r) { return r.slug === e.target.value; });
                if (role) updateUser(u, 'role_id', role.id);
              } }, roles.map(function (r) { return h('option', { key: r.id, value: r.slug }, r.name); }))
            ),
            h('td', null, isBanned(u) ? h(Badge, { status: 'rejected', label: 'Banned' }) : h(Badge, { status: 'approved', label: 'Active' })),
            h('td', null, u.created_at),
            h('td', null, h('button', { className: 'btn btn-sm ' + (isBanned(u) ? 'btn-secondary' : 'btn-danger'), onClick: function () { updateUser(u, 'is_banned', isBanned(u) ? 0 : 1); } }, isBanned(u) ? 'Unban' : 'Ban'))
          );
        }))
      )
    );
  }

  /* ---------------- Comments moderation ---------------- */
  function CommentsManager() {
    var s1 = useState('pending'); var status = s1[0]; var setStatus = s1[1];
    var s2 = useState([]); var rows = s2[0]; var setRows = s2[1];

    function load() { apiGet('comments', { status: status }).then(function (res) { if (res.success) setRows(res.data); }); }
    useEffect(function () { load(); }, [status]);

    function act(id, action) {
      apiSend('comments', { id: id, action: action }, action === 'delete' ? 'DELETE' : 'POST').then(function (res) { if (res.success) load(); });
    }

    var tabs = ['pending', 'approved', 'rejected', 'spam'];

    return h('div', null,
      h('div', { style: { display: 'flex', gap: '8px', marginBottom: '16px' } },
        tabs.map(function (t) {
          return h('button', { key: t, className: 'btn ' + (t === status ? 'btn-primary' : 'btn-secondary') + ' btn-sm', onClick: function () { setStatus(t); } }, t.charAt(0).toUpperCase() + t.slice(1));
        })
      ),
      h('div', { className: 'data-table-wrap' },
        h('table', { className: 'data-table' },
          h('thead', null, h('tr', null, h('th', null, 'User'), h('th', null, 'Article'), h('th', null, 'Comment'), h('th', null, 'Date'), h('th', null, 'Actions'))),
          h('tbody', null, rows.map(function (c) {
            return h('tr', { key: c.id },
              h('td', null, c.user_name), h('td', null, c.article_title),
              h('td', { style: { maxWidth: '320px' } }, c.body),
              h('td', null, c.created_at),
              h('td', null,
                c.status !== 'approved' && h('button', { className: 'btn btn-secondary btn-sm', onClick: function () { act(c.id, 'approve'); } }, 'Approve'), ' ',
                c.status !== 'rejected' && h('button', { className: 'btn btn-secondary btn-sm', onClick: function () { act(c.id, 'reject'); } }, 'Reject'), ' ',
                c.status !== 'spam' && h('button', { className: 'btn btn-secondary btn-sm', onClick: function () { act(c.id, 'spam'); } }, 'Spam'), ' ',
                h('button', { className: 'btn btn-danger btn-sm', onClick: function () { ConfirmDelete(function () { act(c.id, 'delete'); }); } }, 'Delete')
              )
            );
          }))
        )
      )
    );
  }

  /* ---------------- Contact messages ---------------- */
  function MessagesManager() {
    var s1 = useState([]); var rows = s1[0]; var setRows = s1[1];
    var s2 = useState(null); var active = s2[0]; var setActive = s2[1];
    var s3 = useState(''); var reply = s3[0]; var setReply = s3[1];

    function load() { apiGet('messages').then(function (res) { if (res.success) setRows(res.data); }); }
    useEffect(function () { load(); }, []);

    function open(m) { setActive(m); setReply(m.admin_reply || ''); }

    function send(status) {
      apiSend('messages', { id: active.id, status: status, admin_reply: reply }).then(function (res) {
        if (res.success) { setActive(null); load(); }
      });
    }

    return h('div', null,
      h('div', { className: 'data-table-wrap' },
        h('table', { className: 'data-table' },
          h('thead', null, h('tr', null, h('th', null, 'From'), h('th', null, 'Subject'), h('th', null, 'Department'), h('th', null, 'Status'), h('th', null, 'Date'), h('th', null, 'Actions'))),
          h('tbody', null, rows.map(function (m) {
            return h('tr', { key: m.id },
              h('td', null, m.user_name + ' (' + m.user_email + ')'),
              h('td', null, m.subject), h('td', null, m.category),
              h('td', null, h(Badge, { status: m.status })),
              h('td', null, m.created_at),
              h('td', null, h('button', { className: 'btn btn-secondary btn-sm', onClick: function () { open(m); } }, 'View / Reply'))
            );
          }))
        )
      ),
      active && h(Modal, { title: active.subject, onClose: function () { setActive(null); } },
        h('p', { style: { marginBottom: '12px' } }, active.message),
        h('div', { className: 'form-group' },
          h('label', null, 'Admin Reply'),
          h('textarea', { rows: 4, value: reply, onChange: function (e) { setReply(e.target.value); } })
        ),
        h('div', { style: { display: 'flex', gap: '10px' } },
          h('button', { className: 'btn btn-primary', onClick: function () { send('resolved'); } }, 'Send & Resolve'),
          h('button', { className: 'btn btn-secondary', onClick: function () { send('read'); } }, 'Save as Read'),
          h('button', { className: 'btn btn-secondary', onClick: function () { setActive(null); } }, 'Close')
        )
      )
    );
  }

  /* ---------------- Site settings (admin only) ---------------- */
  function SettingsManager() {
    var s1 = useState({}); var settings = s1[0]; var setSettings = s1[1];
    var s2 = useState(''); var msg = s2[0]; var setMsg = s2[1];

    useEffect(function () { apiGet('settings').then(function (res) { if (res.success) setSettings(res.data); }); }, []);

    var fields = [
      ['site_name', 'Site Name'], ['site_tagline', 'Tagline'], ['site_description', 'Description'],
      ['contact_email', 'Contact Email'], ['contact_phone', 'Contact Phone'], ['contact_address', 'Contact Address'],
      ['facebook_url', 'Facebook URL'], ['twitter_url', 'Twitter URL'], ['youtube_url', 'YouTube URL'],
      ['theme_primary', 'Theme Primary Color'], ['meta_keywords', 'SEO Meta Keywords'],
    ];

    function save(e) {
      e.preventDefault();
      apiSend('settings', settings).then(function (res) { setMsg(res.success ? 'Settings saved.' : (res.message || 'Failed to save.')); });
    }

    return h('form', { onSubmit: save, style: { maxWidth: '640px' } },
      h(Alert, { message: msg }),
      fields.map(function (f) {
        return h('div', { className: 'form-group', key: f[0] },
          h('label', null, f[1]),
          h('input', { value: settings[f[0]] || '', onChange: function (e) { var s = Object.assign({}, settings); s[f[0]] = e.target.value; setSettings(s); } })
        );
      }),
      h('button', { type: 'submit', className: 'btn btn-primary' }, 'Save Settings')
    );
  }

  /* ---------------- App shell ---------------- */
  function App() {
    var s1 = useState('dashboard'); var tab = s1[0]; var setTab = s1[1];
    var s2 = useState(false); var sidebarOpen = s2[0]; var setSidebarOpen = s2[1];
    var s3 = useState(document.documentElement.getAttribute('data-theme') || 'light'); var theme = s3[0]; var setTheme = s3[1];

    function toggleTheme() {
      var next = theme === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', next);
      localStorage.setItem('theme', next);
      setTheme(next);
    }

    var navItems = [
      { key: 'dashboard', label: 'Dashboard', icon: 'fa-gauge-high' },
      { key: 'articles', label: 'Articles', icon: 'fa-newspaper' },
      { key: 'categories', label: 'Categories', icon: 'fa-tags' },
      { key: 'comments', label: 'Comments', icon: 'fa-comments' },
      { key: 'messages', label: 'Messages', icon: 'fa-envelope' },
    ];
    if (ROLE_SLUG === 'admin' || ROLE_SLUG === 'editor') {
      navItems.push({ key: 'users', label: 'Users', icon: 'fa-users' });
    }
    if (ROLE_SLUG === 'admin') {
      navItems.push({ key: 'settings', label: 'Settings', icon: 'fa-gear' });
    }

    var titles = { dashboard: 'Dashboard', articles: 'Article Management', categories: 'Category Management', users: 'User Management', comments: 'Comment Moderation', messages: 'Contact Messages', settings: 'Site Settings' };

    var content;
    if (tab === 'dashboard') content = h(Dashboard);
    else if (tab === 'articles') content = h(ArticlesManager);
    else if (tab === 'categories') content = h(CategoriesManager);
    else if (tab === 'users') content = h(UsersManager);
    else if (tab === 'comments') content = h(CommentsManager);
    else if (tab === 'messages') content = h(MessagesManager);
    else if (tab === 'settings') content = h(SettingsManager);

    return h('div', { className: 'admin-wrapper' },
      h('aside', { className: 'admin-sidebar' + (sidebarOpen ? ' open' : '') },
        h('div', { className: 'admin-logo' }, 'NEWS', h('span', null, 'HD'), ' Admin'),
        h('nav', { className: 'admin-nav' },
          navItems.map(function (item) {
            return h('button', {
              key: item.key,
              className: item.key === tab ? 'active' : '',
              onClick: function () { setTab(item.key); setSidebarOpen(false); },
            }, h('i', { className: 'fa-solid ' + item.icon }), item.label);
          }),
          h('button', { onClick: function () { window.location.href = SITE_URL; } }, h('i', { className: 'fa-solid fa-arrow-up-right-from-square' }), 'View Site'),
          h('button', { onClick: function () { window.location.href = LOGOUT_URL; } }, h('i', { className: 'fa-solid fa-right-from-bracket' }), 'Logout')
        )
      ),
      h('div', { className: 'admin-main' },
        h('header', { className: 'admin-header' },
          h('div', { style: { display: 'flex', alignItems: 'center', gap: '12px' } },
            h('button', { className: 'mobile-toggle', onClick: function () { setSidebarOpen(!sidebarOpen); } }, h('i', { className: 'fa-solid fa-bars' })),
            h('h2', null, titles[tab])
          ),
          h('div', { className: 'admin-header-actions' },
            h('button', { className: 'theme-toggle', onClick: toggleTheme, 'aria-label': 'Toggle theme' },
              h('i', { className: 'fa-solid fa-sun icon-light' }), h('i', { className: 'fa-solid fa-moon icon-dark' })
            ),
            h('div', { className: 'admin-user' }, USER_NAME, h('small', null, USER_ROLE))
          )
        ),
        h('div', { className: 'admin-content' }, content)
      )
    );
  }

  ReactDOM.createRoot(rootEl).render(h(App));
})();
