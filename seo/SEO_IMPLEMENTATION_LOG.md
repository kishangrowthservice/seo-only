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

## Batch B1 — Rating schema unified to the verified figure (1 Nov 2026)

Decision path: the audit found two contradictory claims — homepage 4.8/1536 vs data-science page 5.0/1536, neither visible on-page. The owner directed that one verified figure be applied to both pages. Verification: the business's public listing data (Google knowledge-panel style result for "Madrid Software Training Solutions", Saket address and phone matching the site) shows **Rating: 4.8 (1536 reviews)** — identical to the homepage figure. The 5.0 on the course page is the outlier and is treated as the error.

Changes (repo files; live server unchanged until deployment):
- `datascience.html` — Course `aggregateRating.ratingValue` 5 → **4.8** (ratingCount 1536 unchanged), so both pages now state the same verified 4.8/1536.
- `datascience.html` — removed the two schema-only embedded `Review` objects (Sakshi Mehra, Neha Verma): those review texts appear nowhere in the page's visible content and cannot be verified from the repository. If the owner confirms they are genuine student reviews, the correct fix is to publish them visibly on the page first, then restore the markup.
- `index.html` + `datascience.html` — the rating is now visible on-page in the success-stories section of both pages ("Rated 4.8 out of 5 based on 1,536 reviews on Google"), so the schema now matches visible content as Google's policy requires.
- `index.html` — incidental fix found during validation: the homepage JSON-LD graph had a trailing comma in the `sameAs` array, which made the entire block invalid JSON; the comma was removed and the block now parses.

Validation: every JSON-LD block in both files parses; both aggregateRatings now read ratingValue 4.8 / count 1536; the visible sentence is present in the rendered text of both pages; no `Review` schema remains on the course page.

## Batch B2 — Auth hardening, meta trims, city-description fixes, topical related posts (1 Nov 2026)

Owner instruction for this round: apply the fixes; ask before deleting anything. Nothing was deleted.

### 1. Admin auth hardening (security)
- `admin-login.php` — the plaintext password (`admin` / `password123`, public in this repo, treated as compromised) is GONE from the code. Login now verifies with `password_verify()` against a hash supplied only on the server: env `ADMIN_USER` / `ADMIN_PASS_HASH`, or a git-ignored `admin-credentials.php` (format + hash-generation command in the new `admin-credentials.example.php`; `.gitignore` now excludes the real file). Login fails closed ("not configured") when no hash is set, and the session id is regenerated on login.
- `add_blog.php` — NEW FINDING, worse than the password: this endpoint had **no session check at all**; anyone could POST a blog post straight into `blogs.json` without logging in. It now returns 403 unless the admin session is present (same guard as `admin-add-blog.php`).
- Verified locally (PHP 8.3, throwaway test credentials, discarded): wrong password rejected; the old public password rejected; correct password 302 → panel; add_blog.php 403 without session, passes the guard with one. php -l clean.
- Still required at deployment: set the new password hash on the server (one `php -r` command, in the example file). Until the hardened files are deployed, the LIVE login still uses the old public password.

### 2. Blog meta descriptions trimmed (blogs.json)
11 posts had seoDescriptions of 171–348 chars (search results truncate ~160). Each was shortened to 128–164 chars using only the post's own wording — no new claims. Field-level diff against the repo version verified: exactly those 11 `seoDescription` fields changed, all other content byte-identical after parsing.

### 3. City-page description corrections
All four city pages said "<City> NCR" in their meta/OG/Twitter descriptions. Mumbai and Bangalore are not in the NCR: fixed to "Mumbai" / "Bangalore". Noida and Faridabad ARE in the NCR and were left as-is. Also fixed the missing space in ",offering" → ", offering" in all four (3 occurrences per page: description + OG + Twitter). The deeper city-page duplication (99.9% identical body content) still needs the owner's content decision — not touched.

### 4. Topical related posts + clean internal links
`single-blog.php` already had a Related Articles block (Batch A) but it picked the latest 3 posts regardless of topic, and linked them via `single-blog.php?slug=`. Now: related posts are ranked by shared `seoKeywords` with the current post (recency as tiebreak), and all blog internal links use the clean canonical form `/blogs/<slug>` (verified live: clean URLs return 200; also fixed the same query-string link in `blog.php`; `blogs.php` already used clean links). Local render validation (with a throwaway stub for the repo's placeholder Parsedown.php — the real library is server-only — and an mbstring polyfill for the sandbox): page renders fully, exactly one canonical, related section shows 3 topically matched posts with clean links, and the 404 + noindex path for bad slugs still works.

### Awaiting owner approval (deletions — not done, per instruction)
- `images/Archive.zip` (~63 MB) and any oversized/unreferenced images: candidates from the audit. Deleting images that live pages still reference would break the site, so the safe sweep is: confirm the candidate list against live pages first, then delete on approval.
