<?php
/**
 * Sitemap generator for the Madrid Software website (repo-owned URLs only).
 *
 * Usage (CLI):  php generate-sitemap.php
 * Writes sitemap.xml next to this file.
 *
 * Coverage: homepage, the course pages whose sources live in this repo
 * (at their clean production URLs), the blog index, and every post in
 * blogs.json. Post <lastmod> uses the post's own `date` field; static
 * pages omit <lastmod> rather than inventing a date.
 *
 * Production base URL is taken from the site's own Organization schema
 * (index.html) — https://www.madridsoftwaretrainings.com
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

$base = 'https://www.madridsoftwaretrainings.com';

$static = [
    ['/', '1.0'],
    ['/data-science-training-institute-in-delhi', '0.9'],
    ['/data-science-course-with-genai-training-in-delhi', '0.8'],
    ['/blogs', '0.8'],
];

$blogsFile = __DIR__ . '/blogs.json';
$blogs = [];
if (file_exists($blogsFile)) {
    $decoded = json_decode(file_get_contents($blogsFile), true);
    if (is_array($decoded)) {
        $blogs = $decoded;
    }
}

$out = [];
$out[] = '<?xml version="1.0" encoding="UTF-8"?>';
$out[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

foreach ($static as [$path, $priority]) {
    $out[] = '  <url>';
    $out[] = '    <loc>' . htmlspecialchars($base . $path, ENT_XML1) . '</loc>';
    $out[] = '    <priority>' . $priority . '</priority>';
    $out[] = '  </url>';
}

$count = 0;
foreach ($blogs as $blog) {
    if (empty($blog['slug'])) {
        continue;
    }
    $out[] = '  <url>';
    $out[] = '    <loc>' . htmlspecialchars($base . '/blogs/' . $blog['slug'], ENT_XML1) . '</loc>';
    if (!empty($blog['date']) && strtotime($blog['date'])) {
        $out[] = '    <lastmod>' . date('Y-m-d', strtotime($blog['date'])) . '</lastmod>';
    }
    $out[] = '    <priority>0.7</priority>';
    $out[] = '  </url>';
    $count++;
}

$out[] = '</urlset>';

file_put_contents(__DIR__ . '/sitemap.xml', implode("\n", $out) . "\n");
echo 'sitemap.xml written: ' . (count($static) + $count) . " URLs (" . $count . " blog posts)\n";
