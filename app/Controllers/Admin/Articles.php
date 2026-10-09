<?php

namespace App\Controllers\Admin;

use App\Models\ArticleModel;
use App\Models\CategoryModel;

class Articles extends BaseAdminController
{
    public function index(): string
    {
        $user = current_user();
        $model = new ArticleModel();

        $page   = max(1, (int) ($this->request->getGet('page') ?? 1));
        $search = sanitize($this->request->getGet('q') ?? '');
        $status = sanitize($this->request->getGet('status') ?? '');

        if (! is_staff()) {
            $articles = $model->forAuthor((int) $user['id'], $search, $status, ($page - 1) * ADMIN_ITEMS_PER_PAGE, ADMIN_ITEMS_PER_PAGE);
            $total    = $model->authorCount((int) $user['id'], $search, $status);
        } else {
            $articles = $model->adminListByStatus($search, $status, ($page - 1) * ADMIN_ITEMS_PER_PAGE, ADMIN_ITEMS_PER_PAGE);
            $total    = $model->adminCountByStatus($search, $status);
        }

        $listBase  = is_admin() ? 'admin/articles' : 'author/articles';
        $pagination = paginate($total, $page, ADMIN_ITEMS_PER_PAGE, url($listBase), array_filter(['q' => $search, 'status' => $status]));

        return $this->render('Articles', 'articles', 'articles/index', [
            'articles'   => $articles,
            'pagination' => $pagination,
            'q'          => $search,
            'status'     => $status,
            'total'      => $total,
        ]);
    }

    public function create(): string
    {
        return $this->form(null, '', []);
    }

    public function edit(int $id): \CodeIgniter\HTTP\RedirectResponse|string
    {
        $model = new ArticleModel();
        $article = $model->find($id);

        if (! $article) {
            return redirect()->to(is_admin() ? 'admin/articles' : 'author/articles')->with('error', 'Article not found.');
        }

        $user = current_user();
        if (! is_staff() && (int) $article['author_id'] !== (int) $user['id']) {
            return redirect()->to(is_admin() ? 'admin/articles' : 'author/articles')->with('error', 'You can only edit your own articles.');
        }

        $tags = get_article_tags($id);
        $tagNames = implode(', ', array_column($tags, 'name'));

        $old = $article;
        $old['tags'] = $tagNames;

        return $this->form($article, $tagNames, $old);
    }

    public function save(): \CodeIgniter\HTTP\RedirectResponse|string
    {
        require_csrf();

        $user  = current_user();
        $model = new ArticleModel();

        $listUrl    = is_admin() ? 'admin/articles' : 'author/articles';
        $id         = (int) $this->request->getPost('id');
        $title      = sanitize($this->request->getPost('title') ?? '');
        $slugInput  = trim((string) $this->request->getPost('slug'));
        $slug       = slugify($slugInput !== '' ? $slugInput : $title);

        // Never allow an empty slug (e.g. symbol-only or non-ASCII titles).
        if ($slug === '') {
            $slug = 'article-' . substr(md5($title . uniqid('', true)), 0, 10);
        }

        // Keep slugs unique.
        $slugBase = $slug;
        $slugNum  = 2;
        while ($model->where('slug', $slug)->where('id !=', $id ?: 0)->countAllResults() > 0) {
            $slug = $slugBase . '-' . $slugNum++;
        }
        $content    = auto_format_content((string) ($this->request->getPost('content') ?? ''));
        $excerpt    = sanitize($this->request->getPost('excerpt') ?? '');
        if ($excerpt === '') {
            $excerpt = truncate(strip_tags($content), 160);
        }
        $categoryId = (int) $this->request->getPost('category_id');
        $status     = in_array($this->request->getPost('status') ?? '', ['draft', 'pending', 'published', 'scheduled'], true) ? $this->request->getPost('status') : 'draft';
        $isFeatured = ! empty($this->request->getPost('is_featured')) ? 1 : 0;
        $isBreaking = ! empty($this->request->getPost('is_breaking')) ? 1 : 0;
        $tags       = normalize_tags($this->request->getPost('tags') ?? '');
        $tagsRaw    = implode(', ', $tags);
        $coverImage = sanitize($this->request->getPost('cover_image') ?? '');
        $coverFile  = $this->request->getFile('cover_file');

        // Every author submission must be reviewed by an administrator.
        if (! can_publish_articles()) {
            $status = 'pending';
        }

        $old = [
            'title'        => $title,
            'slug'         => $slug,
            'excerpt'      => $excerpt,
            'content'      => $content,
            'category_id'  => $categoryId,
            'status'       => $status,
            'is_featured'  => $isFeatured,
            'is_breaking'  => $isBreaking,
            'cover_image'  => $coverImage,
            'tags'         => $tagsRaw,
        ];

        if (! $title || ! $categoryId) {
            $error = 'Title and category are required.';
            return $this->formFor($id, $error, $old);
        }

        // Handle cover upload
        if ($coverFile && $coverFile->isValid() && ! $coverFile->hasMoved()) {
            $mime = $coverFile->getMimeType();
            if (! in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
                $error = 'Cover image must be JPG, PNG, WebP or GIF.';
                return $this->formFor($id, $error, $old);
            }
            if ($coverFile->getSize() > MAX_UPLOAD_SIZE) {
                $error = 'Cover image must be under 2 MB.';
                return $this->formFor($id, $error, $old);
            }
            $dest = UPLOAD_PATH . '/articles';
            if (! is_dir($dest)) {
                mkdir($dest, 0755, true);
            }
            $name = $coverFile->getRandomName();
            $coverFile->move($dest, $name);
            $coverImage = 'uploads/articles/' . $name;
            $publicDir = ROOTPATH . 'public/uploads/articles';
            if (! is_dir($publicDir)) {
                mkdir($publicDir, 0755, true);
            }
            @copy(UPLOAD_PATH . '/articles/' . $name, $publicDir . '/' . $name);
        } elseif ($id) {
            // No new cover uploaded: keep the existing one on edit.
            $existingCover = $model->find($id);
            $coverImage = ($existingCover['cover_image'] ?? '') ?: '';
        }

        $data = [
            'title'            => $title,
            'slug'             => $slug,
            'excerpt'          => $excerpt,
            'content'          => $content,
            'category_id'      => $categoryId,
            'status'           => $status,
            'is_featured'      => $isFeatured,
            'is_breaking'      => $isBreaking,
            'cover_image'      => $coverImage ?: null,
            'meta_title'       => $title,
            'meta_description' => $excerpt,
        ];

        // Never wipe existing content if the editor failed to send any body text.
        if ($id && trim(strip_tags($content)) === '') {
            $existingContent = $model->find($id);
            if ($existingContent && trim(strip_tags((string) $existingContent['content'])) !== '') {
                $data['content'] = (string) $existingContent['content'];
            }
        }

        if ($id) {
            $existing = $model->find($id);
            if (! $existing) {
                return redirect()->to($listUrl)->with('error', 'Article not found.');
            }
            if (! is_staff() && (int) $existing['author_id'] !== (int) $user['id']) {
                return redirect()->to($listUrl)->with('error', 'You can only edit your own articles.');
            }
            $data['published_at'] = $status === 'published' && empty($existing['published_at']) ? date('Y-m-d H:i:s') : $existing['published_at'];
            $model->update($id, $data);
            sync_article_tags($id, $tags);
            log_activity((int) $user['id'], 'article_update', 'article', $id);
            return redirect()->to($listUrl)->with('success', 'Article updated successfully.');
        }

        $data['author_id'] = (int) $user['id'];
        $data['published_at'] = $status === 'published' ? date('Y-m-d H:i:s') : null;
        $newId = $model->insert($data);
        sync_article_tags((int) $newId, $tags);
        log_activity((int) $user['id'], 'article_create', 'article', (int) $newId);

        return redirect()->to($listUrl)->with('success', 'Article created successfully.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        require_csrf();
        $user  = current_user();
        $model = new ArticleModel();
        $listUrl = is_admin() ? 'admin/articles' : 'author/articles';
        $article = $model->find($id);

        if (! $article) {
            return redirect()->to($listUrl)->with('error', 'Article not found.');
        }
        if (! is_staff() && (int) $article['author_id'] !== (int) $user['id']) {
            return redirect()->to($listUrl)->with('error', 'You can only delete your own articles.');
        }

        $model->delete($id);
        log_activity((int) $user['id'], 'article_delete', 'article', $id);

        return redirect()->to($listUrl)->with('success', 'Article deleted successfully.');
    }

    public function preview(int $id): \CodeIgniter\HTTP\RedirectResponse|string
    {
        $model   = new ArticleModel();
        $article = $model->findByIdWithDetails($id);

        if (! $article) {
            return redirect()->to('admin/articles')->with('error', 'Article not found.');
        }

        $tags   = get_article_tags((int) $article['id']);
        $related = $model->related((int) $article['category_id'], (int) $article['id'], 3);

        $data = [
            'meta'         => page_meta(
                $article['meta_title'] ?: $article['title'],
                $article['meta_description'] ?: truncate(strip_tags($article['excerpt'] ?? ''), 160),
                $article['cover_image'],
                url('admin/articles/preview/' . (int) $article['id'])
            ),
            'activeNav'    => $article['category_slug'],
            'article'      => $article,
            'tags'         => $tags,
            'related'      => $related,
            'trending'     => $model->trending(5),
            'user'         => current_user(),
            'isBookmarked' => false,
            'extraScripts' => [],
            'extraCss'     => [],
        ];

        return view('site/article', $data);
    }

    public function status(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        require_csrf();

        if (! can_publish_articles()) {
            return redirect()->to('admin/articles')->with('error', 'You do not have permission to change article status.');
        }

        $user    = current_user();
        $model   = new ArticleModel();
        $article = $model->find($id);

        if (! $article) {
            return redirect()->to('admin/articles')->with('error', 'Article not found.');
        }

        $next    = strtolower((string) $this->request->getPost('status') ?? '');
        $allowed = ['draft', 'pending', 'published', 'scheduled'];
        if (! in_array($next, $allowed, true)) {
            return redirect()->to('admin/articles')->with('error', 'Invalid status.');
        }

        $data = ['status' => $next];
        if ($next === 'published' && empty($article['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        if ($next !== 'published') {
            $data['published_at'] = null;
        }

        $model->update($id, $data);
        log_activity((int) $user['id'], 'article_status', 'article', $id);

        return redirect()->to('admin/articles')->with('success', 'Article marked as ' . $next . '.');
    }

    public function upload(): \CodeIgniter\HTTP\ResponseInterface
    {
        require_csrf();

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Method not allowed']);
        }

        $file = $this->request->getFile('image');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No valid image uploaded']);
        }

        $mime = $file->getMimeType();
        if (! in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Only JPG, PNG, WebP and GIF images are allowed']);
        }
        if ($file->getSize() > MAX_UPLOAD_SIZE) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Image must be under 2 MB']);
        }

        $dest = UPLOAD_PATH . '/articles';
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        $name = $file->getRandomName();
        $file->move($dest, $name);

        $publicDir = ROOTPATH . 'public/uploads/articles';
        if (! is_dir($publicDir)) {
            mkdir($publicDir, 0755, true);
        }
        @copy(UPLOAD_PATH . '/articles/' . $name, $publicDir . '/' . $name);

        return $this->response->setJSON([
            'success' => true,
            'url'     => base_url('uploads/articles/' . $name),
        ]);
    }

    private function form(?array $article, string $tags, array $old): string
    {
        $id = $article ? (int) $article['id'] : 0;

        return $this->render(
            $article ? 'Edit Article' : 'New Article',
            'articles',
            'articles/form',
            [
                'article'    => $article,
                'categories' => get_categories(),
                'tags'       => $tags,
                'error'      => '',
                'old'        => $old,
            ],
            [
                'extraScripts' => ['editor.js'],
            ]
        );
    }

    private function formFor(int $id, string $error, array $old): string
    {
        $model = new ArticleModel();
        $article = $id ? $model->find($id) : null;

        if ($id && ! $article) {
            return redirect()->to(is_admin() ? 'admin/articles' : 'author/articles')->with('error', 'Article not found.');
        }

        return $this->render(
            $article ? 'Edit Article' : 'New Article',
            'articles',
            'articles/form',
            [
                'article'    => $article,
                'categories' => get_categories(),
                'tags'       => $old['tags'] ?? '',
                'error'      => $error,
                'old'        => $old,
            ],
            [
                'extraScripts' => ['editor.js'],
            ]
        );
    }
}
