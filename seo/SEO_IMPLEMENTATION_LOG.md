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

## Batch B3 — City pages noindexed by owner decision (1 Nov 2026)

The owner chose the noindex option for the four near-duplicate city course pages (bodies 99.8–99.9% identical to the Delhi page; only the Saket, Delhi address is real). Changes (repo; live effect on deployment):
- `datascience-mumbai.html`, `datascience-noida.html`, `datascience-bangalore.html`, `datascience-faridabad.html` — added `<meta name="robots" content="noindex, follow" />`. Pages stay live and usable (ads/direct visits unaffected), links on them are still followed, and the change is fully reversible by removing one line. The Delhi course page, GenAI page and homepage are NOT noindexed (verified: no noindex present there).
- `generate-sitemap.php` + regenerated `sitemap.xml` — the four city URLs removed (sitemap 40 → 36 URLs); noindexed URLs should not be listed in a sitemap.
- `seo/sitemap-production-proposal.xml` — the same four city URLs removed (89 → 85 URLs), so the server-side sitemap replacement matches this decision.

Note: after deployment Google will drop these pages from its index over the following recrawls; rankings impact is limited to those four duplicate URLs, which is the intended outcome.

## Server-fix pack prepared — soft-404 + redirects (1 Nov 2026)

Re-verified live on 1 Nov 2026: the soft-404 problem is unchanged — junk URLs, `/terms-and-conditions`, `/full-stack-course-in-delhi`, `/devops./devops-course-training-in-delhi` and `/generative-ai-course-training-in-delh` all still return HTTP 200 with a "404 Not Found" title (server: Apache). This cannot be fixed from the repository: it is the server's error handling / rewrite rules, which are not in the repo.

Prepared and pushed, ready for the deployment step:
- `404.php` (repo root) — a real 404 handler (`http_response_code(404)`, noindex, branded page with working links to Home / Data Science / Blog / Contact and the site's published phone number). php -l clean.
- `seo/SERVER_FIXES.md` — the full deployment recipe: `ErrorDocument 404 /404.php`, a warning to inspect the existing catch-all rewrite first (the likely root cause), the complete 301 table (/blog.php and /blogs.php → /blogs; the navigation-linked dead full-stack slug → the working course page; the malformed devops URL and the typo GenAI slug → their real pages; /contact.html → /contact-us; /datascience.html and /index.html → their canonical clean URLs), the deliberate decision NOT to redirect /terms-and-conditions anywhere (no real terms page exists; /terms-condition wrongly serves the Contact page — flagged as a separate content bug), the sitemap replacement step, and a curl verification checklist.

Applying this needs hosting access and ~15 minutes at deployment time.

## Senior hardening round — all remaining technical SEO + security code fixes (1 Nov 2026)

Owner instruction: fix all technical SEO and the security issues, senior-level plan and execution. Everything below is implemented and validated in the repo; the live server still needs the deployment + rotations in seo/SERVER_FIXES.md (deployment order section).

### Security
- `mailhandler.php` REWRITTEN: SMTP password removed from code (server-only env or git-ignored `mail-credentials.php`; new `mail-credentials.example.php` documents rotation-first setup). TLS verification ON by default with a temporary, explicitly-named opt-out while the mail cert's hostname mismatch is fixed at the host. No more public leakage of PHPMailer internals (errors are logged, visitors get a plain "error"); header-injection stripping on all fields; Reply-To restored; honeypot field + per-IP throttle (5/10 min) against form spam. Fails closed (500, no send) when credentials are not configured.
- Sessions: `session_set_cookie_params` (HttpOnly, SameSite=Lax, Secure on HTTPS) in admin-login.php, admin-add-blog.php, add_blog.php.
- CSRF: per-session token generated in the admin panel, hidden field in the blog form, `hash_equals` verification in add_blog.php before any state change.
- Uploads: real MIME verification (finfo) in addition to the extension whitelist; directory creation 0777 -> 0755.
- Stored-XSS door closed: single-blog.php enables Parsedown safe mode + markup escaping (guarded by method_exists). Verified beforehand: all 32 stored posts contain ZERO raw HTML, so rendering output is unchanged.
- blogs.json single-point-of-failure mitigated: add_blog.php now writes a timestamped backup into `backups/` (deny-all .htaccess written on first use) before every overwrite.
- helpers.php: dead `asset()` helper no longer builds http:// URLs with a raw port (now https, no port).

### Technical SEO
- `llms.txt` cleaned: 67 -> 27 links. Removed the malformed devops URL and the typo GenAI URL (both soft-404s, one even labelled "(Typo)" in the file), the dead full-stack slug (correct one kept), the /contact.html duplicate, both broken terms links, the /sitemap HTML URL (now the real sitemap.xml), and the ENTIRE 28-link "Location-Specific" doorway section (cities/localities/countries with no centre) - replaced by one factual Locations note (Saket, New Delhi + online). Kept links verified live (200 sweep).
- `blogs.php`: its 2 links to the dead /full-stack-course-in-delhi slug now point to the working course page.
- Stuffed generic `meta keywords` removed from blog.php, blogs.php, single-blog.php.
- `datascience.html` meta description 174 -> 134 chars (all 3 copies: description/OG/Twitter).
- 11 blog seoTitles trimmed from 61-86 to 41-59 chars using the posts' own wording; field-level diff vs repo confirms ONLY those 11 seoTitle fields changed.
- `robots.txt`: admin/form/credential/backup paths disallowed for all crawlers (AI-bot allows and sitemap line unchanged).
- `README.md`: replaced the "Madrid-sfotware / Test" placeholder with real project notes (partial-snapshot warning, credential policy).

### Server pack v2 (seo/SERVER_FIXES.md addendum)
Security-headers .htaccess block (HSTS/nosniff/SAMEORIGIN/Referrer-Policy/Permissions-Policy; CSP deliberately deferred - it must be built from the real third-party asset list), credential-file and backups deny rules, the mail-certificate fix (hostname mismatch proven 1 Nov 2026), and a strict 7-step deployment order: rotate mailbox password -> fix cert -> set admin hash -> upload files -> merge .htaccess -> swap sitemap -> verify.

### Validation actually run
php -l clean on every PHP file; no old passwords remain in any repo file (git history still has them - rotation is the fix, see report); full local flow test (built-in server, throwaway credentials): wrong password rejected, login 302, session cookie carries HttpOnly + SameSite=Lax, CSRF token present in form, tokenless POST -> 403, token POST passes guards, mailhandler unconfigured -> 500 with a plain "error" and zero internal details; blog post render complete (1 canonical, keywords meta gone, 3 topical related links); blogs.json diff = 11 seoTitle fields only.
