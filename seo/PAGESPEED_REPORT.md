# PageSpeed Insights — measured results (1 Nov 2026, ~05:01–05:05 IST)

Method: Google PageSpeed Insights web (pagespeed.web.dev), one run per URL,
Mobile + Desktop tabs read from the same report. Values are Lighthouse LAB
results (not CrUX field data). The keyless PSI API was also attempted first:
its shared daily quota is exhausted (HTTP 429), so the web UI was used.
Local Lighthouse against the live site is blocked in this environment by
the authenticated egress proxy (ERR_TUNNEL_CONNECTION_FAILED).

Server-side sweep of all 66 sitemap URLs (curl, same morning): every URL
200; TTFB median 1.55 s (max 1.98 s); total load median 2.26 s; HTML size
median 141 KB (max 541 KB = /blogs).

| Page | Mobile Perf | Desktop Perf | Mobile LCP | Desktop LCP | Other (Mobile) |
|---|---|---|---|---|---|
| / (home) | 40 | 62 | 9.6 s | 1.5 s | TBT 2,700 ms, SI 5.7 s; A11y 80, BP 88, SEO 92 |
| /data-science-training-institute-in-delhi | 66 | 57 | 9.5 s | 2.3 s | SEO 100, A11y 88 |
| /data-analytics-course-training-in-delhi | 57 | 66 | 10.6 s | 2.1 s | SEO 100, A11y 88 |
| /blogs | 50 | 45 | 10.9 s | 3.2 s | CLS 0.197 (mobile), SEO 100 |
| /blogs/data-analyst-salary-in-india | 62 | 62 | 9.2 s | 2.0 s | BP 73/77 (lowest) |
| /contact-us | 66 | 84 | 13.3 s | 2.3 s | SEO 100; best desktop score on the site |

Homepage (Mobile) top PageSpeed items: efficient cache lifetimes (est.
saving 11,293 KiB), improve image delivery (est. saving 5,404 KiB),
minimise main-thread work 4.6 s, JavaScript execution 3.4 s, unused
JavaScript ~351 KiB.

Reading: SEO category is already 92–100 live. Performance is capped by
(1) slow server response (TTFB ~1.55 s before anything loads), (2) NO
browser cache headers on static assets, (3) unoptimised images, (4) heavy
homepage JavaScript. Fixes (2) and (3) are prepared in this repo / server
pack; (1) needs hosting-level caching or a CDN; (4) needs homepage script
work. A mobile Performance score of 100 is not achievable on the current
hosting; a realistic post-fix target is Desktop 90+ and Mobile 75–90.
