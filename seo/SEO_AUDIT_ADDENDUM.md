# SEO Audit Addendum — Live-Site Verification Round (1 Nov 2026)

Follow-up to `SEO_AUDIT.md`, produced by probing all 66 URLs in the live
sitemap individually. This round changed several audit conclusions from
"suspected" to "proven" — and found one new site-wide issue.

## New P0 finding — the server returns HTTP 200 for its 404 page (soft 404s)

**Evidence:** `GET /this-page-does-not-exist-xyz123` returns **200** with a
946-byte page titled "404 Not Found". Every missing URL on this host behaves
this way.
**Impact:** status-code checks are meaningless on this server; Google treats
such URLs as soft 404s, wastes crawl budget on them, and the behaviour masks
real breakage (it hid the four broken sitemap entries below).
**Fix (server-side, Batch B):** make the error page return a real 404 status.

## Live sitemap: verified composition of the 66 URLs

| Category | Count | Detail |
|---|---|---|
| Real pages | 59 | Kept in the cleaned proposal |
| Soft-404 entries (page does not exist) | 4 | `/terms-and-conditions`, `/devops./devops-course-training-in-delhi`, `/generative-ai-course-training-in-delh`, `/full-stack-course-in-delhi` |
| Wrong-content duplicates | 2 | `/contact.html` and `/terms-condition` both serve the Contact page |
| Not a page | 1 | `/sitemap` serves the sitemap XML itself |

Notes:
- `/full-stack-course-in-delhi` being a soft 404 matters beyond the sitemap:
  the site's own navigation (blog templates) links to it. The working
  full-stack URL in the sitemap is `/full-stack-developer-course-training-in-delhi`.
- **Five sitemap URLs share the identical title "Madrid Software – Contact Us":**
  `/about-us`, `/contact-us`, `/privacy-policy`, `/terms-condition`,
  `/contact.html`. At minimum, `/about-us` and `/privacy-policy` carry a
  wrong, copy-pasted `<title>` (their page sizes differ from the contact
  page, so they appear to be real pages with a metadata bug — a cheap,
  high-value fix on the server copies).
- **Cleaned production sitemap proposal:** `sitemap-production-proposal.xml`
  (this folder) — 89 URLs: the 59 verified real pages + all 30 blog posts
  missing from the live sitemap. No `lastmod` values (none are verifiable
  for server-side pages; the repo generator already emits post dates for
  the 40 repo-owned URLs). It is a *proposal* for whoever maintains the
  server sitemap — nothing on the server was changed.

## Batch B1 evidence — rating schema (SEO-05) is uncorroborated

Visible-text search of the **live** homepage and the live Delhi data-science
page found **zero** occurrences of "4.8", "1536", "5.0" or "Google Rating".
The AggregateRating claims in schema (4.8/1536 on the homepage graph,
5.0/1536 on the course page) have no matching visible content anywhere on
the live site either. Recommendation stands: the owner either supplies the
verified figures (and they get displayed on-page) or the blocks are removed.
No figures will be invented.

## Performance indicators (NOT Core Web Vitals)

Lighthouse lab runs could not be completed from this environment: the
sandbox's Chrome cannot establish a connection to the live site through
the egress proxy (`ERR_TUNNEL_CONNECTION_FAILED`), so **CWV remains
unverified** rather than estimated.
Server-response indicators via curl (through the same proxy, so treat as
upper bounds): homepage TTFB ≈ 2.7 s, Delhi course page ≈ 1.8 s, blog post
≈ 2.0 s; HTML sizes 128 KB / 176 KB / 53 KB. Even allowing for proxy
overhead, TTFB is high enough to warrant a hosting-side look (caching /
PHP response) before any front-end micro-optimisation.
