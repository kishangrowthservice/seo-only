# SEO Audit — kishangrowthservice/seo-only (Madrid Software)

**Audit date:** 1 Nov 2026 · **Scope:** SEO only · **Method:** full repository
inspection (every HTML/PHP source file and `blogs.json` read), plus read-only
checks of the live production site for corroboration. No files were changed.

---

## 1. Project identification

| Item | Finding (with evidence) |
|---|---|
| Business | **Madrid Software** (Madrid Software Training Solutions) — job-oriented tech training institute, Delhi. Courses: Data Science, Data Analytics, AI/GenAI, Full Stack, UI/UX, DevOps, AWS, etc. (Evidence: page titles, `llms.txt`, Organization schema.) |
| Production domain | **https://www.madridsoftwaretrainings.com** — evidence: Organization `@id`/`url` in `index.html` JSON-LD; repo `index.html` is byte-identical in size (128,088 bytes) to the live homepage response. `http://` non-www 301-redirects to `https://www.` correctly. |
| Stack | Static HTML pages + a small PHP blog system. No framework, no build step, no `package.json`. Content source for the blog is `blogs.json`. Markdown rendered with Parsedown. |
| Rendering | Fully server-rendered/static HTML — content and metadata are in the initial HTML. Good for crawlability. |
| Deployment | Not determinable from the repo: **no `.htaccess`, no server config, no CI/deploy files in the repository.** URL rewriting (clean slugs) happens server-side and is not in source control. |
| Repo ↔ production relationship | **The repository is only a partial snapshot of production** (see SEO-01). The live sitemap lists 66 URLs; the repo contains 7 HTML pages and the blog system. The live single-blog-post template, `robots.txt` and `sitemap.xml` exist only on the server. |

## 2. Route inventory (repository)

| Route/file | Type | Indexable intent |
|---|---|---|
| `index.html` → `/` | Public page | Yes |
| `datascience.html` → also served at `/data-science-training-institute-in-delhi` (clean URL) | Public course page | Yes |
| `datascience-bangalore.html`, `datascience-mumbai.html`, `datascience-noida.html`, `datascience-faridabad.html` | Public course pages (city variants) | Yes (but see SEO-08) |
| `datascience-genai.html` | Public course page | Yes |
| `blogs.php` → served at `/blogs` and `/blogs.php` | Blog listing | Yes (one of them) |
| `blog.php` → served at `/blog.php` | Blog listing (older variant) | Duplicate of the above |
| `single-blog.php` | **Referenced by `blog.php` links but MISSING from the repo** — live post pages (`/blogs/<slug>`, 200, unique titles) prove it exists on the server only | Yes |
| 32 blog posts in `blogs.json` → `/blogs/<slug>` | Dynamic (file-driven) | Yes |
| `admin-login.php`, `admin-add-blog.php`, `add_blog.php`, `logout.php` | Admin/private | No |
| `mailhandler.php`, `helpers.php`, `Parsedown.php`, `PHPMailer/` | Utility | No |
| `llms.txt` | AI-search information file | n/a (already good practice) |
| `testimonials/` (5 images), `icons/` (152 files), `images/` (478 files) | Static assets | n/a |

**Missing from the repo entirely:** `robots.txt`, `sitemap.xml`, `.htaccess`/rewrite rules, the single-post template, and ~55 production course/location pages that appear in the live sitemap.

## 3. What is already healthy (verified, do not "fix")

- Titles are unique on all 7 static pages and on live blog posts; lengths 62–75 chars.
- Meta descriptions exist on 6 of 7 static pages (exception: SEO-06) and on live posts.
- Exactly one H1 on `index.html` and `datascience.html`; heading volume is substantial.
- Blog data quality: 32 posts, **unique slugs, all with images**, most 1,200–5,300 words; `seoTitle`/`seoDescription` are captured per post by `add_blog.php`.
- Internal linking is rich: every page links to the main course slugs; the live `/blogs` listing links to all 32 posts.
- `index.html` has a substantial JSON-LD `@graph` (Organization, EducationalOrganization, WebSite, ItemList of Courses, FAQPage).
- Live `robots.txt` is permissive (incl. AI crawlers) and declares the sitemap; HTTPS + www canonical host redirect is correct (301).
- `llms.txt` is present and well-formed.

## 4. Issues

### P0 — Critical

**SEO-01 · The repository does not contain the production site's SEO-critical files.**
Evidence: no `robots.txt`, `sitemap.xml`, rewrite rules or `single-blog.php` in the repo, yet all exist live (robots 200, sitemap 200 with 66 URLs, post pages 200 with unique titles). `blog.php` links to `single-blog.php?slug=…`, a file absent from the repo.
Impact: any deploy from this repo loses crawl directives, the sitemap, clean-URL rewriting and every blog post page. SEO work done "in the repo" would silently not match production.
Root cause: files were added directly on the server and never committed.

**SEO-02 · Live sitemap contains malformed and duplicate URLs.**
Evidence (live `sitemap.xml`, 66 URLs): `…/devops./devops-course-training-in-delhi` (malformed host-path segment), `…/generative-ai-course-training-in-delh` (truncated slug), both `/contact.html` **and** `/contact-us`, both `/terms-condition` **and** `/terms-and-conditions`, and `/sitemap` (an HTML page) listed as a URL. All `lastmod` values are bulk-stamped 2025-08-06.
Impact: crawl budget wasted on broken/duplicated URLs; sitemap trust signals degraded.

### P1 — High

**SEO-03 · No canonical tags anywhere.**
Evidence: 0 canonical tags in all 7 HTML pages, both PHP templates, and the live homepage. Meanwhile duplicates demonstrably exist: the Delhi data-science course returns 200 at both `/datascience.html` and `/data-science-training-institute-in-delhi`; the blog listing returns 200 at `/blogs`, `/blogs.php` **and** `/blog.php` (two different templates, same purpose).
Impact: search engines must guess the preferred URL; ranking signals split across variants.

**SEO-04 · Sitemap covers only 2 of 32 blog posts.**
Evidence: live sitemap's only blog entries are `/blogs/data-analyst-salary-in-india` and `/blogs/ai-engineer-salary-in-india`; `blogs.json` holds 32 posts, all linked from `/blogs`.
Impact: 30 posts get no sitemap discovery support.

**SEO-05 · AggregateRating schema does not match visible content and is internally inconsistent.**
Evidence: `index.html` LocalBusiness graph claims `ratingValue 4.8, reviewCount 1536`; `datascience.html` Course schema claims `ratingValue 5, ratingCount 1536` plus embedded Reviews — yet the visible text of `datascience.html` contains no rating value or review count at all.
Impact: self-serving review markup on Organization/LocalBusiness is ineligible for rich results under Google's policies, and schema that doesn't reflect visible content risks a structured-data manual action. **The underlying truth (real review counts) is not in the repo and must be supplied by the business — nothing here should be invented.**

**SEO-06 · `datascience-genai.html` has no meta description.**
Evidence: description count 0 for that page (all others have one). Also the worst image-alt coverage (18 images without `alt`).

**SEO-07 · Repo blog templates ignore the per-post SEO fields.**
Evidence: `blog.php`/`blogs.php` never reference `seoTitle`/`seoDescription`/`seoKeywords` (grep: 0 uses), although `add_blog.php` stores them for every post. The repo's listing templates use one generic title site-wide (`blog.php`) — the live post pages are saved only by the server-only template (SEO-01).
Impact: if the repo templates were ever deployed as-is, all 32 posts would share one duplicate title and no description.

**SEO-08 · The four city course pages are 99.8–99.9% identical.**
Evidence: text similarity (SequenceMatcher on visible text) Mumbai≈Noida **0.999**, Mumbai≈Bangalore **0.998**; identical word counts (1,330) and structure; only city names and titles differ. Mumbai's description even says "Mumbai NCR" (geographically wrong).
Impact: classic templated location-page pattern — at best they compete weakly and dilute the main course page; at worst they read as doorway pages. This is a content-strategy issue, not a code fix.

### P2 — Medium

**SEO-09 · No Open Graph / Twitter Card metadata on any page or template** (0 occurrences site-wide). Social/AI sharing previews fall back to scraped guesses.

**SEO-10 · Heading structure defects on templated pages.** Two `<h1>` elements on all four city pages and on `datascience-genai.html` (the second is a tagline, e.g. "Learn to Decoded the Future with Data" — also ungrammatical); three `<h1>` in the `blogs.php` template.

**SEO-11 · Image SEO and performance hygiene.** ~732 `<img>` tags across the 7 pages: 49 without `alt`, 45 with empty `alt` (some may be decorative — needs per-image judgement), only 1 per page has width/height (CLS risk), only 1 per page uses `loading="lazy"`. No `preconnect`/`preload` in page heads.

**SEO-12 · Repository asset bloat.** `images/` = 478 files / **133 MB**, including `images/Archive.zip` (63 MB) and `icons/Archive.zip` (2.2 MB) committed inside asset folders, plus multi-MB PNGs (5.4 MB, 4.0 MB) not referenced by the audited pages. Slows every clone/deploy; any of these referenced later would be an LCP problem uncompressed.

**SEO-13 · Blog metadata lengths.** Several posts have `seoDescription` of 224–348 chars (will be truncated/rewritten in SERPs) and `seoTitle` up to 86 chars. Data is editable per post; no code change needed.

**SEO-14 · No course schema on city/GenAI pages.** `datascience.html` (Delhi) has Course schema; the five other course pages have none (0 JSON-LD blocks). Consistency gap — but see SEO-05: schema must only be added with truthful, visible-content-matching data.

### P3 — Low

**SEO-15 · `blog.php` vs `blogs.php` duplication** — two listing templates maintained in parallel; one should eventually be retired (with a redirect on the server) once canonicals exist (SEO-03).

**SEO-16 · Security note with SEO consequences (out of scope to fix here):** `admin-login.php` contains a hardcoded plaintext admin password in a **public** repository. If the blog admin is compromised, injected spam pages/links are a common outcome (a leading cause of "hacked site" SEO damage). Recommend the owner rotate the credential and move it out of source regardless of this project.

## 5. Keyword / intent mapping (repo pages only — no volumes; none were measurable from available sources, and none are invented)

| Page | Primary intent | Notes |
|---|---|---|
| `/` | Brand + course discovery ("best tech courses / training institute Delhi") | Homepage H1 is aspirational, not descriptive; title carries the keywords. |
| `datascience.html` (Delhi) | "data science course/institute in Delhi" — transactional | Strongest page: unique content (~1,605 words), Course schema. Canonical target for the city variants' topic. |
| City variants ×4 | "data science course in <city>" | Identical content (SEO-08); each city's intent is real, the content doesn't serve it uniquely. |
| `datascience-genai.html` | "data science with generative AI course" | Missing description (SEO-06); distinct topic from the Delhi page — keep separate. |
| `/blogs` + 32 posts | Informational (salaries, tools, comparisons, careers) | Good topical spread around the courses; internal links from posts to course pages were not audited post-by-post (posts render via the missing template — SEO-01). |

## 6. Limitations of this audit

- Production pages **not** present in the repo (~55 course/location pages in the live sitemap) were not content-audited; only the live homepage, blog listing, one post, robots.txt and sitemap.xml were fetched as corroborating evidence.
- No Google Search Console / analytics access — indexation status, rankings and traffic are **unknown**, not assumed.
- Core Web Vitals field data is unavailable; lab measurement of the heavy pages is part of the validation phase if approved.
- Server configuration (rewrites, headers, caching, compression) is not in the repo and could not be audited beyond the redirect/robots/sitemap responses observed.
