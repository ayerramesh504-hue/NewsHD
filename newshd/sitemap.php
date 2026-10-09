<?php
/**
 * Dynamic XML Sitemap
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: application/xml; charset=utf-8');

$articles = db()->query(
    "SELECT slug, updated_at FROM articles WHERE status = 'published' ORDER BY updated_at DESC LIMIT 5000"
)->fetchAll();
$categories = get_categories();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc><?= e(url('')) ?></loc>
    <changefreq>hourly</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc><?= e(url('search.php')) ?></loc>
    <changefreq>daily</changefreq>
    <priority>0.5</priority>
  </url>
  <?php foreach ($categories as $cat): ?>
  <url>
    <loc><?= e(url('category.php?slug=' . $cat['slug'])) ?></loc>
    <changefreq>daily</changefreq>
    <priority>0.7</priority>
  </url>
  <?php endforeach; ?>
  <?php foreach ($articles as $a): ?>
  <url>
    <loc><?= e(url('article.php?slug=' . $a['slug'])) ?></loc>
    <lastmod><?= e(date('c', strtotime($a['updated_at']))) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
  <?php endforeach; ?>
</urlset>
