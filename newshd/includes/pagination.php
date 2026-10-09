<?php
/**
 * Reusable pagination helper
 */

declare(strict_types=1);

function paginate(int $total, int $page, int $perPage, string $baseUrl, array $query = []): array
{
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;

    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $page,
        'total_pages' => $totalPages,
        'offset'      => $offset,
        'has_prev'    => $page > 1,
        'has_next'    => $page < $totalPages,
        'prev_page'   => $page - 1,
        'next_page'   => $page + 1,
        'base_url'    => $baseUrl,
        'query'       => $query,
    ];
}

function render_pagination(array $p): string
{
    if ($p['total_pages'] <= 1) {
        return '';
    }

    $query = $p['query'];
    $buildUrl = function (int $pageNum) use ($p, $query): string {
        $q = array_merge($query, ['page' => $pageNum]);
        return $p['base_url'] . '?' . http_build_query($q);
    };

    $html = '<nav class="pagination" aria-label="Pagination"><ul>';

    if ($p['has_prev']) {
        $html .= '<li><a href="' . e($buildUrl($p['prev_page'])) . '" aria-label="Previous">&laquo; Prev</a></li>';
    }

    $start = max(1, $p['current'] - 2);
    $end = min($p['total_pages'], $p['current'] + 2);

    if ($start > 1) {
        $html .= '<li><a href="' . e($buildUrl(1)) . '">1</a></li>';
        if ($start > 2) {
            $html .= '<li><span class="dots">...</span></li>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $p['current'] ? ' class="active" aria-current="page"' : '';
        $html .= '<li' . $active . '><a href="' . e($buildUrl($i)) . '">' . $i . '</a></li>';
    }

    if ($end < $p['total_pages']) {
        if ($end < $p['total_pages'] - 1) {
            $html .= '<li><span class="dots">...</span></li>';
        }
        $html .= '<li><a href="' . e($buildUrl($p['total_pages'])) . '">' . $p['total_pages'] . '</a></li>';
    }

    if ($p['has_next']) {
        $html .= '<li><a href="' . e($buildUrl($p['next_page'])) . '" aria-label="Next">Next &raquo;</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

function pagination_json(array $p): array
{
    return [
        'total'       => $p['total'],
        'per_page'    => $p['per_page'],
        'current'     => $p['current'],
        'total_pages' => $p['total_pages'],
        'has_prev'    => $p['has_prev'],
        'has_next'    => $p['has_next'],
    ];
}
