# SEO Implementation Log — Batch A (approved by the project owner, 1 Nov 2026)

All changes are repo-side and SEO-only. No design, branding, content meaning,
stack, or functionality was changed. Base domain used throughout:
`https://www.madridsoftwaretrainings.com` (evidence: Audit §1).

| ID | File(s) | Change | Issue |
|---|---|---|---|
| A1 | `robots.txt` (new) | Exact copy of the live production robots.txt (AI-bot allows, `User-agent: * Allow: /`, Sitemap declaration) so the file exists in source control with zero behaviour change. | SEO-01 |
| A2 | `generate-sitemap.php` (new), `sitemap.xml` (new, generated) | CLI generator reads `blogs.json` and emits a valid sitemap: homepage, the 6 repo-owned course pages at their clean production URLs, `/blogs`, and all 32 post URLs. Post `<lastmod>` = the post's own `date`; static pages omit `lastmod` (no invented dates). The live sitemap's malformed/duplicate entries are not reproduced. | SEO-01, SEO-04, SEO-02 (repo side) |
| A3 | `index.html`, `datascience.html`, `datascience-bangalore.html`, `datascience-mumbai.html`, `datascience-noida.html`, `datascience-faridabad.html`, `datascience-genai.html` | One self-referencing `<link rel="canonical">` per page pointing at its clean production URL (e.g. `datascience.html` → `/data-science-training-institute-in-delhi`; `datascience-genai.html` → `/data-science-course-with-genai-training-in-delhi`, confirmed by exact title match with the live page). | SEO-03 |
| A4a | `blogs.php`, `blog.php` | Canonical to `https://www.madridsoftwaretrainings.com/blogs` on both listing templates (consolidates the three live listing URLs at the signal level until server 301s are approved — Batch B4). `blogs.php` also received OG tags. | SEO-03, SEO-09 |
| A4b | `single-blog.php` (new) | The missing single-post template, restored to the repo and modelled on `blog.php`'s own header/design (same chrome, Tailwind classes, card markup for related posts). Renders each post with: `<title>` and meta description from the post's stored `seoTitle`/`seoDescription`, self-canonical to `/blogs/<slug>`, OG article tags + `article:published_time`, Article JSON-LD built only from the post's own fields (title, description, image, date, author), breadcrumb, read time via the repo's existing `helpers.php`, and 3 related-post links. Unknown slug → HTTP 404 + `noindex` + a helpful message. | SEO-01, SEO-07, SEO-03 |
| A5 | `datascience-genai.html` | Added the missing meta description, written from the page's own visible content (no new claims): "Master Data Science with Generative AI at Madrid Software in Delhi — from basics to real-world projects, with hands-on training designed for beginners." | SEO-06 |
| A6 | 4 city pages, `datascience-genai.html`, `blogs.php` | Duplicate `<h1>` demotions with identical classes (zero visual change): the tagline second H1 → `<h2>` on the 5 course pages; in `blogs.php` the badge H1 and "Fresh Articles" H1 → `<h2>`, leaving exactly one H1 per page/template. | SEO-10 |
| A7 | 7 static pages (+ `blogs.php`, `single-blog.php` in A4) | Open Graph (`og:type/title/description/url/image`) and Twitter Card tags, populated from each page's existing title/description and its first content image (fallback: the site's logo from its own schema). No copy invented. | SEO-09 |
| A8 | 7 static pages | Image hygiene: every `<img>` now carries an `alt` attribute. Export-artifact UI images (filenames like `Group 37514.png`, `Div [hidden].png`, `image (19).png`) received `alt=""` (correct treatment for decorative graphics — filename-derived junk alt text was tried, reviewed, and deliberately rejected); the 5 testimonial portraits on the homepage received their names as alt (matching the visible testimonial captions); `loading="lazy"` added to all images after the first H1 (hero/header imagery stays eager). Width/height were NOT added — intrinsic sizes aren't uniformly knowable from source and inventing them would distort layout. | SEO-11 |

## Deliberately NOT done in Batch A (see Plan, Batch B)

- No change to the AggregateRating/Review schema (SEO-05) — needs the owner's verified review figures.
- No city-page content changes (SEO-08) — content project, owner decision.
- No edits to the live/server sitemap (SEO-02) or server redirects (B4) — production/server scope, needs owner confirmation of how the live sitemap is generated.
- No credential changes (SEO-16) — flagged to owner.
- No asset deletions (SEO-12).
- No width/height attributes, no image compression — follow-up performance task (Roadmap).
