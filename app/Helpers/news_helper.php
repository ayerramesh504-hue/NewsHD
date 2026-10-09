<?php

use App\Models\CategoryModel;
use App\Models\SiteSettingModel;

if (! function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('url')) {
    function url(string $path = ''): string
    {
        return site_url($path);
    }
}

if (! function_exists('asset')) {
    function asset(string $path): string
    {
        $path = ltrim($path, '/');
        if (strncasecmp($path, 'assets/', 7) !== 0) {
            $path = 'assets/' . $path;
        }
        return base_url($path);
    }
}

if (! function_exists('api_url')) {
    function api_url(string $path = ''): string
    {
        return site_url('api/' . ltrim($path, '/'));
    }
}

if (! function_exists('slugify')) {
    function slugify(string $text): string
    {
        if (class_exists('Transliterator')) {
            $t = Transliterator::create('Any-Latin; Latin-ASCII');
            if ($t) {
                $text = $t->transliterate($text) ?? $text;
            }
        }
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text) ?? '';
        $text = preg_replace('/[\s-]+/', '-', $text) ?? '';
        return trim($text, '-');
    }
}

if (! function_exists('normalize_tags')) {
    function normalize_tags(string $input): array
    {
        $seen = [];
        $tags = [];
        foreach (explode(',', $input) as $raw) {
            $name = trim(ltrim($raw, '# '));
            if ($name === '') {
                continue;
            }
            $key = mb_strtolower($name);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $tags[] = $name;
        }
        return $tags;
    }
}

if (! function_exists('sanitize')) {
    function sanitize(string $input): string
    {
        return trim(strip_tags($input));
    }
}

if (! function_exists('get_setting')) {
    function get_setting(string $key, ?string $default = null): ?string
    {
        try {
            return model(SiteSettingModel::class)->get($key, $default);
        } catch (Throwable $e) {
            return $default;
        }
    }
}

if (! function_exists('log_activity')) {
    function log_activity(?int $userId, string $action, ?string $entityType = null, ?int $entityId = null): void
    {
        try {
            $request = service('request');
            model('App\Models\ActivityLogModel')->insert([
                'user_id'     => $userId,
                'action'      => $action,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'ip_address'  => $request->getIPAddress() ?: null,
                'user_agent'  => mb_substr($request->getUserAgent()->getAgentString() ?? '', 0, 255),
            ]);
        } catch (Throwable $e) {
            // Non-critical
        }
    }
}

if (! function_exists('format_date')) {
    function format_date(?string $datetime, string $format = 'M j, Y'): string
    {
        if (! $datetime) {
            return '';
        }
        return date($format, strtotime($datetime));
    }
}

if (! function_exists('truncate')) {
    function truncate(string $text, int $length = 150): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . '...';
    }
}

if (! function_exists('auto_format_content')) {
    /**
     * Convert plain text written by a non-technical author into safe HTML.
     *
     * Rules:
     *  - A blank line starts a new paragraph.
     *  - A line starting with "# " becomes an H2 heading, "## " an H3, etc.
     *  - Lines starting with "- " or "* " become a bullet list.
     *  - Lines starting with "1. " become a numbered list.
     *
     * Content that already contains HTML block tags is passed through untouched.
     */
    function auto_format_content(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        // Already structured HTML (e.g. legacy editor content) — leave as is.
        if (preg_match('/<(p|h[1-6]|ul|ol|li|blockquote|figure|img|iframe|pre|table|div)[\s>]/i', $text)) {
            return $text;
        }

        $text   = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $blocks = preg_split('/\n\s*\n/', $text) ?: [];
        $out    = [];

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }

            // Heading: "# " -> h2, "## " -> h3, ...
            if (preg_match('/^(#{1,4})\s+(.+)$/', $block, $m)) {
                $level = min(6, strlen($m[1]) + 1);
                $out[] = '<h' . $level . '>' . trim($m[2]) . '</h' . $level . '>';
                continue;
            }

            // Bullet list
            if (preg_match('/^[-*]\s+/m', $block)) {
                $items = [];
                foreach (preg_split('/\n/', $block) ?: [] as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }
                    $items[] = preg_match('/^[-*]\s+(.+)$/', $line, $m)
                        ? '<li>' . trim($m[1]) . '</li>'
                        : '<li>' . $line . '</li>';
                }
                $out[] = '<ul>' . implode('', $items) . '</ul>';
                continue;
            }

            // Numbered list
            if (preg_match('/^\d+\.\s+/m', $block)) {
                $items = [];
                foreach (preg_split('/\n/', $block) ?: [] as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }
                    $items[] = preg_match('/^\d+\.\s+(.+)$/', $line, $m)
                        ? '<li>' . trim($m[1]) . '</li>'
                        : '<li>' . $line . '</li>';
                }
                $out[] = '<ol>' . implode('', $items) . '</ol>';
                continue;
            }

            // Paragraph (single newlines become line breaks)
            $out[] = '<p>' . nl2br($block) . '</p>';
        }

        return implode("\n\n", $out);
    }
}

if (! function_exists('upload_image')) {
    function upload_image(?array $file, string $subdir): ?string
    {
        if (! $file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            return null;
        }
        $finfo  = new finfo(FILEINFO_MIME_TYPE);
        $mime   = $finfo->file($file['tmp_name']);
        if (! in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
            return null;
        }
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => null,
        };
        if (! $ext) {
            return null;
        }
        $dir = UPLOAD_PATH . '/' . $subdir;
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = uniqid('', true) . '.' . $ext;
        $dest     = $dir . '/' . $filename;
        if (! move_uploaded_file($file['tmp_name'], $dest)) {
            return null;
        }
        return 'uploads/' . $subdir . '/' . $filename;
    }
}

if (! function_exists('get_categories')) {
    function get_categories(): array
    {
        return model(CategoryModel::class)->ordered();
    }
}

if (! function_exists('category_icon')) {
    function category_icon(string $slug): string
    {
        $icons = [
            'politics'      => 'fa-landmark',
            'sports'        => 'fa-futbol',
            'business'      => 'fa-chart-line',
            'technology'    => 'fa-microchip',
            'entertainment' => 'fa-film',
            'world'         => 'fa-globe',
        ];
        return $icons[$slug] ?? 'fa-newspaper';
    }
}

if (! function_exists('get_breaking_news')) {
    function get_breaking_news(int $limit = 8): array
    {
        try {
            $items = model('App\Models\ArticleModel')->breaking($limit);
            if (empty($items)) {
                $items = model('App\Models\ArticleModel')->featured($limit);
            }
            return $items;
        } catch (Throwable $e) {
            return [];
        }
    }
}

if (! function_exists('increment_article_views')) {
    function increment_article_views(int $articleId): void
    {
        model('App\Models\ArticleModel')->incrementViews($articleId);
    }
}

if (! function_exists('get_article_tags')) {
    function get_article_tags(int $articleId): array
    {
        return model('App\Models\TagModel')->forArticle($articleId);
    }
}

if (! function_exists('sync_article_tags')) {
    function sync_article_tags(int $articleId, array $tagNames): void
    {
        model('App\Models\ArticleTagModel')->sync($articleId, $tagNames);
    }
}

if (! function_exists('validate_password_strength')) {
    function validate_password_strength(string $password): ?string
    {
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters.';
        }
        if (! preg_match('/[A-Z]/', $password)) {
            return 'Password must contain an uppercase letter.';
        }
        if (! preg_match('/[a-z]/', $password)) {
            return 'Password must contain a lowercase letter.';
        }
        if (! preg_match('/[0-9]/', $password)) {
            return 'Password must contain a number.';
        }
        return null;
    }
}

if (! function_exists('page_meta')) {
    function page_meta(string $title, ?string $description = null, ?string $image = null, ?string $pageUrl = null): array
    {
        $logoUrl = get_setting('logo_url');
        return [
            'title'       => $title . ' | ' . (get_setting('site_name') ?: APP_NAME),
            'description' => $description ?: (get_setting('site_description') ?: ''),
            'image'       => $image ?: ($logoUrl ? asset($logoUrl) : ''),
            'url'         => $pageUrl ?: site_url(),
        ];
    }
}
