# SEO Implementation Plan — seo-only (Madrid Software)

Companion to `SEO_AUDIT.md` (issue IDs refer to it). Guiding rules from the
project brief: SEO only; no redesign, no stack change, no new pages for
keyword targeting, no invented business facts or metrics; risky changes
(canonical domain decisions, redirects, robots changes) require approval.

**Confirmed production domain (evidence-based):** `https://www.madridsoftwaretrainings.com`
— see Audit §1. All canonical/sitemap URLs below use it.

---

## Batch A — Safe, repo-local fixes (low risk; recommended to approve as one batch)

| # | Fix (issue) | Files | Change | Verification |
|---|---|---|---|---|
| A1 | Add `robots.txt` to the repo (SEO-01) | `robots.txt` (new) | **Exact copy of the live file** (AI-bot allows + `User-agent: * Allow: /` + Sitemap line) so the repo matches production behaviour — no policy change. | Diff against live robots.txt = identical. |
| A2 | Add a repo `sitemap.xml` generator for repo-owned URLs (SEO-01, SEO-04) | `sitemap.xml` (generated), `generate-sitemap.php` (new, CLI-only utility) | A small PHP script reads `blogs.json` + the static page list and emits a standards-compliant sitemap: homepage, 6 course pages (at their **clean production URLs**), `/blogs`, and **all 32 post URLs**. No fake `lastmod` — post `<lastmod>` uses the post's own `date` field; static pages omit `lastmod`. Malformed/duplicate live entries (SEO-02) are **not** reproduced. | XML validates; every URL returns 200 live; count = 1 + 6 + 1 + 32 = 40. |
| A3 | Self-referencing canonicals on static pages (SEO-03) | 7 × `*.html` | One `<link rel="canonical">` per page pointing at its clean production URL (e.g. `datascience.html` → `https://www.madridsoftwaretrainings.com/data-science-training-institute-in-delhi`, `index.html` → `https://www.madridsoftwaretrainings.com/`). City pages canonical to their own clean city URLs (they are intended as separate landing pages). | Grep: exactly 1 canonical per page; each URL live-200. |
| A4 | Canonicals + per-post meta in blog templates (SEO-03, SEO-07) | `blogs.php`, `blog.php` | Listing templates get a canonical to `https://www.madridsoftwaretrainings.com/blogs`. Additionally, create the missing **`single-blog.php`** in the repo, modelled on the existing templates' design, rendering each post with its stored `seoTitle`, `seoDescription`, canonical `/blogs/<slug>`, and Article JSON-LD built only from the post's own fields (title, date, author, image). This restores the repo↔production parity for the most SEO-critical template. ⚠️ Deployment note: the server already has a working version — deploying ours must be a deliberate, compared replacement, not an overwrite (see Risks). | Local PHP render test of 3 sample posts: unique titles/descriptions, canonical correct, JSON-LD parses. |
| A5 | Missing meta description (SEO-06) | `datascience-genai.html` | Add a description written from the page's own visible content (no invented claims), ~150 chars. | Grep + head diff. |
| A6 | Heading fixes (SEO-10) | 4 city pages, `datascience-genai.html`, `blogs.php` | Demote the tagline second `<h1>` to `<h2>` (same classes/styles — no visual change); reduce listing template to one `<h1>`. | H1 count = 1 per page; visual spot-check unchanged. |
| A7 | Open Graph + Twitter basics (SEO-09) | 7 × `*.html`, `blogs.php`, `single-blog.php` (new) | `og:title/description/type/url/image` from each page's existing title/description and an existing site image; `twitter:card=summary_large_image`. No new copy invented. | Grep per page; values match page meta. |
| A8 | Image alt + loading hygiene (SEO-11) | 7 × `*.html` | Add factual `alt` text (from surrounding headings/filenames) to the 49 images missing it; add `loading="lazy"` to below-the-fold images (never to the hero image); add width/height **only where the intrinsic size is known from the file itself**. Decorative images keep `alt=""`. | Alt coverage report before/after; no layout change (no dimensions invented). |

## Batch B — Requires owner input or a decision (do NOT do silently)

| # | Item (issue) | What's needed |
|---|---|---|
| B1 | **AggregateRating/Review schema (SEO-05)** | Owner must confirm the real review source and numbers (e.g. Google Business Profile rating/count). Options: (a) correct the schema to the verified figures **and** show them visibly on the page, or (b) remove `aggregateRating`/`review` blocks. Default recommendation if unverifiable: **remove** — deceptive-schema risk outweighs benefit. No numbers will be invented. |
| B2 | **City-page duplication (SEO-08)** | Content decision: either invest in genuinely city-specific content per page (real centre details, city-specific FAQs — facts the owner must supply) or consolidate. No bulk rewrite will be done under "SEO fixes"; this is a content project. Interim safe step (in Batch A3) is only to canonical each city page to itself so intent stays clear. |
| B3 | **Live sitemap cleanup (SEO-02)** | The malformed/duplicate entries live in the server-side sitemap. If the sitemap is generated by a tool/plugin on the server, fix it there; the repo generator (A2) covers repo-owned URLs only. Owner to confirm how the live sitemap is produced before anyone edits it. |
| B4 | **Duplicate listing URLs (SEO-03/SEO-15)** | Server-side 301s: `/blog.php` → `/blogs`, `/blogs.php` → `/blogs`, `/datascience.html` → clean URL (and equivalents). This is a redirect change on production = **approval + server access required**. Canonicals (Batch A) are the safe first step. |
| B5 | **Admin credential (SEO-16)** | Owner rotates the hardcoded password in `admin-login.php` and moves it to an environment/config value outside the repo. Flagged, not fixed by this project (outside SEO scope, but the single biggest hacked-site risk on this repo). |
| B6 | **Asset bloat (SEO-12)** | Deleting `Archive.zip` files and unused multi-MB images from the repo is safe for the site (they are not referenced by audited pages) but is a repo-hygiene decision for the owner; image *compression* of in-use images is a separate, testable task. |

## Roadmap (after Batch A)

1. Search Console verification + sitemap submission (needs owner's Google account) — unlocks real indexation data; several audit questions (which city pages are indexed, post coverage) can only be answered there.
2. Lab Core Web Vitals pass on the homepage and Delhi course page (Lighthouse, local copies) — then targeted fixes (hero image compression/format, lazy-loading already in A8).
3. Blog metadata tidy-up (SEO-13): trim the 7 over-long descriptions in `blogs.json` — editorial micro-edits, owner-approved wording.
4. Post-level internal linking: add 1–2 contextual course-page links inside relevant post bodies (only where genuinely relevant; data is in `blogs.json`, edits are per-post and reviewable).

## Risks & safeguards

- **Repo ≠ production (SEO-01) cuts both ways:** Batch A changes are repo-side. Deploying them — especially `single-blog.php` (A4) and `sitemap.xml` (A2) — must be reconciled with the server's existing versions first (diff, then replace deliberately). Nothing in Batch A should be pushed to the live server blind.
- Canonicals (A3/A4) point at the evidence-confirmed `https://www.` host; if the owner later confirms a different preferred host, they are one-line changes.
- No redirects, no robots policy change, no content deletion, and no schema additions with unverified data are included in Batch A.
- Every Batch A change is verifiable locally (grep/render/XML validation) and will be reported in `SEO_IMPLEMENTATION_LOG.md` + `SEO_VALIDATION_REPORT.md` with before/after evidence.

## Definition of done for Batch A

Build-equivalent checks pass (PHP files lint with `php -l`, sitemap XML validates), rendered output spot-checked for 3 blog posts and all 7 static pages, no visual changes (heading demotions reuse existing classes), and the log + validation report are written.
