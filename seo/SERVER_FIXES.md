# Server fixes — ready to apply at deployment (Apache)

Status on 1 Nov 2026 (re-verified live): **NOT yet applied.** These fixes live on the
server (Apache `.htaccess` / error handling), which is not part of this repository,
so they can only take effect during the deployment step with hosting access.
Everything below is prepared and copy-ready; applying it is a ~15-minute job.

## Problem 1 — Soft 404s site-wide (P0)

Verified live (1 Nov 2026): every non-existent URL returns **HTTP 200** with a page
titled "404 Not Found" — including URLs listed in the live sitemap:

| URL | Live status | Body |
|---|---|---|
| `/zzz-garbage-98765` (any junk URL) | 200 | "404 Not Found" |
| `/terms-and-conditions` | 200 | "404 Not Found" |
| `/full-stack-course-in-delhi` | 200 | "404 Not Found" (also linked from site navigation!) |
| `/devops./devops-course-training-in-delhi` | 200 | "404 Not Found" (malformed URL) |
| `/generative-ai-course-training-in-delh` | 200 | "404 Not Found" (typo slug) |

Google treats these as soft 404s: crawl budget is wasted, and dead URLs stay
"indexed" as if they were real pages.

### Fix
1. Upload `404.php` (in the repository root) to the server root. It sends a real
   `http_response_code(404)` and renders a branded page with working links.
2. Add this line to `.htaccess`:
   ```
   ErrorDocument 404 /404.php
   ```
3. IMPORTANT: first inspect the existing `.htaccess`. If it contains a catch-all
   rewrite that routes unknown paths to a page (a common cause of this bug), that
   rule — not ErrorDocument — is overriding the status. In that case the fallback
   target must be `404.php` and must not reset the status to 200. Do not stack a
   second catch-all on top of the existing one.

## Problem 2 — Missing 301 redirects

Add to `.htaccess` (Apache mod_rewrite / RedirectPermanent):

```
# Blog listing duplicates -> canonical listing
RedirectPermanent /blog.php https://www.madridsoftwaretrainings.com/blogs
RedirectPermanent /blogs.php https://www.madridsoftwaretrainings.com/blogs

# Navigation links to a dead slug; the working course page is:
RedirectPermanent /full-stack-course-in-delhi https://www.madridsoftwaretrainings.com/full-stack-developer-course-training-in-delhi

# Malformed / typo URLs that appear in the old sitemap
RedirectPermanent /devops./devops-course-training-in-delhi https://www.madridsoftwaretrainings.com/devops-course-training-in-delhi
RedirectPermanent /generative-ai-course-training-in-delh https://www.madridsoftwaretrainings.com/generative-ai-course-in-delhi

# Duplicate contact URL
RedirectPermanent /contact.html https://www.madridsoftwaretrainings.com/contact-us

# .html duplicates of canonical clean URLs
RedirectPermanent /datascience.html https://www.madridsoftwaretrainings.com/data-science-training-institute-in-delhi
RedirectPermanent /index.html https://www.madridsoftwaretrainings.com/
```

Notes:
- `/terms-and-conditions` deliberately gets NO redirect: there is no real terms
  page to point at (`/terms-condition` currently serves the Contact page — a
  separate content bug). After the 404 fix it will return an honest 404 until a
  real terms page exists; then redirect or restore it.
- Verify each target returns 200 before adding its rule (checklist below).

## Problem 3 — Live sitemap replacement

Replace the server `sitemap.xml` (66 URLs, includes the soft-404 and malformed
entries above) with `seo/sitemap-production-proposal.xml` from this repo
(85 verified URLs), or regenerate it from the cleaned generator once the repo
files are deployed.

## Post-deploy verification checklist

```
curl -s -o /dev/null -w '%{http_code}' https://www.madridsoftwaretrainings.com/zzz-garbage-98765   # expect 404
curl -s -o /dev/null -w '%{http_code}' https://www.madridsoftwaretrainings.com/terms-and-conditions # expect 404
curl -s -o /dev/null -w '%{http_code} %{redirect_url}' https://www.madridsoftwaretrainings.com/blog.php  # expect 301 -> /blogs
curl -s -o /dev/null -w '%{http_code} %{redirect_url}' https://www.madridsoftwaretrainings.com/full-stack-course-in-delhi # expect 301 -> working page
curl -s -o /dev/null -w '%{http_code}' https://www.madridsoftwaretrainings.com/                      # expect 200
curl -s -o /dev/null -w '%{http_code}' https://www.madridsoftwaretrainings.com/blogs                 # expect 200
```
Plus: submit the replaced sitemap in Google Search Console and watch the
"Soft 404" and "Duplicate" buckets in Page Indexing over the following weeks.

---

# Addendum (senior hardening round) — headers, mail certificate, deployment order

## Problem 4 — No security headers

Add to `.htaccess` (after the redirect rules):

```
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
    Header set Permissions-Policy "geolocation=(), microphone=(), camera=()"
    Header set Strict-Transport-Security "max-age=31536000" env=HTTPS
</IfModule>

# Keep credential files off the web entirely (form endpoints such as
# mailhandler.php and add_blog.php must stay reachable - they protect
# themselves in code; credential files must never be served at all)
<FilesMatch "^(admin-credentials|mail-credentials)\.php$">
    Require all denied
</FilesMatch>
<DirectoryMatch "/backups">
    Require all denied
</DirectoryMatch>
```
(Adjust to the server's existing rules; merge, do not duplicate blocks.
CSP is deliberately not included: the pages load several third-party CDNs
and ad tags, so a CSP must be built from the real asset list or it will
break the site - it is a follow-up task, not a copy-paste.)

## Problem 5 — Mail server certificate is invalid for its hostname

Proven 1 Nov 2026: a verified-TLS connection to
`mail.madridsoftwaretrainings.com:465` fails with "certificate hostname
mismatch". This is why `mailhandler.php` had verification disabled.
Fix at the host: issue a proper certificate covering the mail hostname
(cPanel AutoSSL / Let's Encrypt usually covers `mail.<domain>`), then set
`MAIL_TLS_VERIFY` to true (the default in the hardened handler) and confirm
a test enquiry still arrives.

## Deployment order (do not shuffle)

1. **Rotate the mailbox password** in the hosting panel (it is public and
   proven live). Set the NEW password only in the server-side
   `mail-credentials.php`.
2. **Fix the mail certificate** (above). Until this is done, the handler's
   temporary `MAIL_TLS_VERIFY=false` may be used - and nothing else may
   use it as an excuse to stay.
3. **Set the admin password hash** in the server-side
   `admin-credentials.php` (generator command is in the example file).
4. Upload the repo files (file-by-file; NEVER overwrite the server's real
   `Parsedown.php`, and do not expect the repo to contain every live page).
5. Merge the `.htaccess` pieces: ErrorDocument 404 -> redirects -> headers
   -> file protections.
6. Replace the server sitemap with `seo/sitemap-production-proposal.xml`.
7. Run the verification checklist (SERVER_FIXES.md, first pack) PLUS:
   admin login works with the NEW password only; a test blog post can be
   created (and `backups/` gains a copy); a test enquiry arrives by mail;
   `curl -I` shows the new headers; the 4 city pages show the noindex tag.

## Problem 6 — No browser cache headers (PageSpeed's biggest single saving)

PageSpeed (1 Nov 2026) estimates **11,293 KiB** of repeat-download waste on
the homepage alone because static assets ship with no cache lifetimes.
Add to `.htaccess`:

```
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 month"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType text/html "access plus 0 seconds"
</IfModule>
```
(HTML stays uncached so content edits show immediately; version file names
or query strings when CSS/JS change, or shorten those two to 1 week.)
