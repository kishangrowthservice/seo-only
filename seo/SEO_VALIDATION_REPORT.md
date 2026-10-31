# SEO Validation Report — Batch A (1 Nov 2026)

Every check below was actually executed. Anything not executed is marked
UNVERIFIED rather than claimed.

## Tooling

- PHP 8.3 CLI (installed for this validation) — `php -l` + PHP built-in server renders.
- Python checks for meta/canonical/heading/alt counts (before values from the audit).
- Live production checks with curl (read-only).

## Results

| # | Check | Method | Result |
|---|---|---|---|
| V1 | PHP syntax — `blogs.php`, `blog.php`, `single-blog.php`, `generate-sitemap.php` | `php -l` | ✅ No syntax errors in all 4 files |
| V2 | Single-post rendering | Local server render of `single-blog.php?slug=` for 2 posts (`data-analyst-salary-in-india`, `deepseek-vs-chatgpt`) | ✅ Each renders its own stored seoTitle/seoDescription; exactly **one** canonical → its `/blogs/<slug>` URL; 5 OG tags; Article JSON-LD parses as valid JSON with correct headline; exactly 1 H1. Post 1's rendered title matches the live post page title exactly ("Data Analyst Salary In India : [Updated - 2025]") |
| V3 | 404 behaviour | Render of an unknown slug | ✅ HTTP 404, `noindex`, "Post not found" title |
| V4 | Listing templates | Local render of `blogs.php` / `blog.php` | ✅ Both 200; canonical → `/blogs`; `blogs.php` H1 count = 1 |
| V5 | Sitemap validity + coverage | XML parse; count `<loc>` | ✅ Well-formed; **40 URLs** (8 static + 32 posts) |
| V6 | Sitemap URL liveness | HTTP GET of **all 40 URLs** against production | ✅ **All 40 return 200** — no broken, redirected, or non-indexable entries |
| V7 | robots.txt parity | `diff` repo file vs live `https://www.madridsoftwaretrainings.com/robots.txt` | ✅ Byte-identical |
| V8 | Static-page canonicals | Grep per page; presence inside `<head>` | ✅ Exactly 1 per page on all 7 pages; targets are among the V6-verified 200 URLs |
| V9 | Meta descriptions | Grep per page | ✅ Present on all 7 pages (GenAI gap closed) |
| V10 | OG/Twitter coverage | Grep per page | ✅ 5 `og:` tags + Twitter Card tags on all 7 pages (previously 0) |
| V11 | Heading structure | H1 count per page | ✅ Exactly 1 H1 on all 7 static pages (city/GenAI pages were 2) and in `blogs.php` (was 3) |
| V12 | Image alt coverage | Count `<img>` without `alt` per page | ✅ 0 remaining on all 7 pages (was 49 missing); lazy loading on 59–125 below-the-fold images per page |
| V13 | No visual regression by construction | Diff review | ✅ Heading demotions reuse identical classes; head additions are metadata only; the only body-attribute additions are `alt`/`loading` on images. (Screenshot-level visual diff: UNVERIFIED — pages depend on external assets not all present locally.) |
| V14 | Canonical duplication bug found & fixed during validation | V2 initially showed **two** canonicals on post pages (the `/blogs` canonical inherited from `blog.php`'s head during assembly of `single-blog.php`) | ✅ Removed; re-render confirms exactly 1 |
| V15 | Junk-alt regression caught & fixed during validation | Manual review of auto-derived alt texts ("Group 37514", "Div [hidden]") | ✅ Replaced with `alt=""` for decorative UI images; names only for testimonial portraits |

## Before → after summary

| Metric | Before | After |
|---|---|---|
| Pages with a canonical tag | 0 / 7 (+0 templates) | 7 / 7 + all blog templates |
| Pages with meta description | 6 / 7 | 7 / 7 |
| Pages with OG tags | 0 / 7 | 7 / 7 |
| Blog posts in repo sitemap | 2 / 32 (live sitemap) | 32 / 32 (repo sitemap) |
| Pages with exactly 1 H1 | 2 / 7 | 7 / 7 |
| Images missing `alt` | 49 | 0 |
| `single-blog.php` in repo | Missing | Present, renders per-post SEO meta |
| `robots.txt` / sitemap generator in repo | Missing | Present |

## Not verified / outstanding

- **Deployment parity:** Batch A is delivered to the repository. The live server still runs its own copies (incl. its own `single-blog.php` and sitemap) — reconciling/deploying is a separate, deliberate step (Plan, Risks). Nothing in this report claims the live site has changed.
- **Core Web Vitals:** not measured in this batch (no field data; lab pass is Roadmap item 2).
- **Search Console data:** unavailable (owner's Google account required).
- City-page duplication, rating schema, live-sitemap cleanup, server 301s, credential rotation: Batch B, awaiting owner input (see Plan).
