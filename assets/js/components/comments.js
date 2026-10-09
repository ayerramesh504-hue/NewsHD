(function () {
  const { useState, useEffect, createElement: h } = React;
  const rootEl = document.getElementById('commentsRoot');
  if (!rootEl) return;

  const articleId = parseInt(rootEl.dataset.articleId, 10);
  const api = rootEl.dataset.api;
  const loggedIn = rootEl.dataset.loggedIn === '1';
  const userId = parseInt(rootEl.dataset.userId, 10);
  const loginUrl = rootEl.dataset.loginUrl;
  const EDIT_WINDOW = 900;

  function CommentItem({ comment, onRefresh, depth }) {
    const [replying, setReplying] = useState(false);
    const [replyText, setReplyText] = useState('');
    const [editing, setEditing] = useState(false);
    const [editText, setEditText] = useState(comment.body);
    const canEdit = loggedIn && comment.user_id == userId &&
      (Date.now() - new Date(comment.created_at).getTime()) / 1000 <= EDIT_WINDOW;

    async function postAction(payload) {
      const res = await fetch(api, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
        body: JSON.stringify(payload),
      });
      return res.json();
    }

    return h('div', { className: 'comment-item', style: depth ? { marginLeft: '24px' } : null },
      h('div', { className: 'comment-header' },
        h('span', { className: 'comment-author' }, comment.user_name),
        h('span', { className: 'comment-date' }, new Date(comment.created_at).toLocaleString())
      ),
      editing
        ? h('div', null,
            h('textarea', { value: editText, onChange: function (e) { setEditText(e.target.value); }, rows: 3, style: { width: '100%' } }),
            h('button', { className: 'btn btn-primary', style: { marginTop: '8px' }, onClick: async function () {
              await postAction({ action: 'edit', comment_id: comment.id, body: editText });
              setEditing(false); onRefresh();
            }}, 'Save')
          )
        : h('p', null, comment.body),
      h('div', { className: 'comment-actions' },
        loggedIn && h('button', { onClick: function () { setReplying(!replying); } }, 'Reply'),
        canEdit && h('button', { onClick: function () { setEditing(true); } }, 'Edit'),
        loggedIn && comment.user_id == userId && h('button', { onClick: async function () {
          if (confirm('Delete comment?')) { await postAction({ action: 'delete', comment_id: comment.id }); onRefresh(); }
        }}, 'Delete'),
        loggedIn && h('button', { onClick: async function () { await postAction({ action: 'vote', comment_id: comment.id, vote: 1 }); onRefresh(); } },
          '👍 ' + (comment.likes || 0)),
        loggedIn && h('button', { onClick: async function () { await postAction({ action: 'vote', comment_id: comment.id, vote: -1 }); onRefresh(); } },
          '👎 ' + (comment.dislikes || 0))
      ),
      replying && h('div', { style: { marginTop: '8px' } },
        h('textarea', { value: replyText, onChange: function (e) { setReplyText(e.target.value); }, rows: 2, style: { width: '100%' }, placeholder: 'Write a reply...' }),
        h('button', { className: 'btn btn-primary', style: { marginTop: '8px' }, onClick: async function () {
          await postAction({ action: 'create', article_id: articleId, body: replyText, parent_id: comment.id });
          setReplyText(''); setReplying(false); onRefresh();
        }}, 'Post Reply')
      ),
      comment.replies && comment.replies.map(function (r) {
        return h(CommentItem, { key: r.id, comment: r, onRefresh: onRefresh, depth: (depth || 0) + 1 });
      })
    );
  }

  function CommentsApp() {
    const [comments, setComments] = useState([]);
    const [body, setBody] = useState('');
    const [message, setMessage] = useState('');

    async function loadComments() {
      const res = await fetch(api + '?article_id=' + articleId);
      const data = await res.json();
      if (data.success) setComments(data.comments);
    }

    useEffect(function () { loadComments(); }, []);

    async function submitComment(e) {
      e.preventDefault();
      if (!body.trim()) return;
      const res = await fetch(api, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
        body: JSON.stringify({ action: 'create', article_id: articleId, body: body }),
      });
      const data = await res.json();
      setMessage(data.message || '');
      if (data.success) { setBody(''); loadComments(); }
    }

    if (!loggedIn) {
      return h('div', { className: 'login-prompt' },
        h('p', null, 'Please log in to post a comment.'),
        h('a', { href: loginUrl, className: 'btn btn-primary' }, 'Login to Comment')
      );
    }

    return h('div', null,
      h('form', { className: 'comment-form', onSubmit: submitComment },
        h('textarea', { value: body, onChange: function (e) { setBody(e.target.value); }, placeholder: 'Share your thoughts...', required: true }),
        h('button', { type: 'submit' }, 'Post Comment')
      ),
      message && h('div', { className: 'alert alert-success', style: { marginTop: '12px' } }, message),
      h('div', { style: { marginTop: '24px' } },
        comments.length === 0
          ? h('p', { className: 'empty-state' }, 'No comments yet. Be the first!')
          : comments.map(function (c) {
              return h(CommentItem, { key: c.id, comment: c, onRefresh: loadComments, depth: 0 });
            })
      )
    );
  }

  ReactDOM.createRoot(rootEl).render(h(CommentsApp));
})();
