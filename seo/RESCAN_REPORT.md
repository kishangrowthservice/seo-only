# Full rescan — no sugarcoating (1 Nov 2026)

Method: the complete repo tree was re-pulled and every code/text file re-read
(including files the original audit skimmed: `mailhandler.php`, `helpers.php`,
`logout.php`, `blog.php`, `blogs.php`, `admin-add-blog.php`, `llms.txt`), all
HTML/JSON re-validated programmatically, image inventory cross-referenced, and
key facts re-checked against the live site the same day.

## CRITICAL

1. **A live mailbox password is sitting in plaintext in this PUBLIC repo.**
   `mailhandler.php` (line 28) hardcodes the SMTP password for
   `madrid@madridsoftwaretrainings.com` (the value is not reproduced here).
   Anyone on the internet can read it and send mail as the institute, or try
   the mailbox directly. This is worse than the old admin password.
   → Owner must rotate that mailbox password immediately and move it to a
   server-only config, exactly like the admin login fix in Batch B2.
   The same file also disables TLS certificate verification
   (`verify_peer => false`, `allow_self_signed => true`), so lead-form data and
   those credentials travel over an unverified connection, and its error path
   prints PHPMailer internals (`ErrorInfo`, exception messages) to the public.

2. **Nothing from Batches A/B1/B2/B3 is live.** Re-checked today: the live
   Delhi course page and a live blog post still have **zero canonical tags and
   zero Open Graph tags**. The live sitemap is still the broken 66-URL one, the
   soft-404s still return 200, the live admin still uses the old public
   password, and the live city pages are not noindexed. Every fix so far exists
   only in this repository. Until deployment, the real website has not improved
   by one pixel.

3. **This repo is not the website.** The live site has ~59 real pages; the repo
   holds 7 HTML pages and the blog system. The live rewrite rules, the real
   Parsedown library, and most course pages exist only on the server. The repo
   cannot be deployed as-is or even run standalone (`Parsedown.php` is an
   8-line placeholder: "full Parsedown class code goes here"). Any deployment
   done carelessly from this repo would break the live blog.

## SERIOUS

4. **Stored-XSS path in the blog.** Post content is stored as raw markdown and
   rendered through Parsedown with no safe mode / sanitisation. Combined with
   the years the admin password was public (and the period `add_blog.php` had
   no auth at all), any injected HTML/JS in a post body would execute on the
   public blog. All 32 current posts should be eyeball-checked for injected
   markup at deployment time. (Titles/authors ARE escaped in templates; the
   body is the open door.)
5. **No CSRF protection anywhere** — admin post creation and the public lead
   form have no tokens. Session cookies are started without `HttpOnly` /
   `Secure` / `SameSite` flags.
6. **Image upload validation is extension-only** (no MIME/content check) and
   creates world-writable `0777` directories. Low exploitability today
   (random filenames, image extensions), still sloppy.
7. **`llms.txt` works against the SEO fixes.** It links the dead slug
   `/full-stack-course-in-delhi` (a soft-404) and promotes the city doorway
   pages (Mumbai/Noida/Bangalore/Faridabad data-analytics and data-science
   URLs) to AI crawlers — the same duplicate network just noindexed in the
   repo HTML.
8. **The repo's own blog listing links to a dead page.** `blogs.php` contains
   2 links to `/full-stack-course-in-delhi` (the soft-404 slug).
9. **blogs.json is a single point of failure.** All 32 posts live in one JSON
   file that `add_blog.php` rewrites wholesale on every new post — no backup,
   no versioning inside the app. One corrupt write loses the entire blog.
10. **Weight.** Course pages ship 105–135 `<img>` tags each (~134–172 KB of
    HTML before a single image loads), and the image library holds multi-MB
    originals (a 5.2 MB PNG, a 3.9 MB PNG, a 2.4 MB GIF). Core Web Vitals were
    never measurable from the audit sandbox and remain UNVERIFIED — anyone
    claiming the site is "fast enough" is guessing.

## REPO HYGIENE (deletion still on owner hold — listed, not touched)

11. Total image/icon bulk: `images/` 476 files ≈ 127 MB at top level, incl.
    `Archive.zip` (61.9 MB); `icons/` another 151 files ≈ 4.2 MB incl. a
    second `Archive.zip` (2.1 MB); `__MACOSX` junk folders in both.
    Only **29 of the 476** top-level images are referenced by anything in this
    repo. The rest are either used by the server-only pages or are dead
    weight — the repo alone cannot prove which, which is exactly why deletion
    needs the live cross-check first.
12. `README.md` says "# Madrid-sfotware / Test". `helpers.php` contains a
    dead `asset()` helper that builds `http://` URLs with a raw port if ever
    used (currently unused). PHPMailer is a loose vendored copy with no
    composer manifest, so its version/updates are untracked.
13. `robots.txt` allows everything including the admin area (no `Disallow`
    for admin paths) — minor, but free to fix at deployment.

## MINOR SEO LEFTOVERS

14. `datascience.html` meta description is still 174 chars; 11 of 32 blog
    `seoTitle`s exceed 60 chars (SERP truncation risk). A stuffed generic
    `meta keywords` tag (every course name) still ships on all blog templates
    — Google ignores it; it only makes the site look dated.

## WHAT IS ACTUALLY FINE (verified in this rescan)

- Repo HTML pages: unique titles/descriptions, 1 H1 each, self-canonicals,
  OG/Twitter complete, every JSON-LD block parses, 0 images missing `alt`,
  rating schema unified at the verified 4.8/1536 and visible on-page.
- `blogs.json`: 32 posts, no duplicate slugs/titles, every SEO field filled,
  all 32 post images exist in `images/blogs/`; the 11 trimmed descriptions
  verified field-by-field.
- Blog templates use per-post SEO fields, clean `/blogs/<slug>` internal
  links, keyword-ranked related posts; unknown slugs return a true 404 +
  noindex in the template.
- Admin auth in the repo is now hashed and fail-closed; `add_blog.php`
  requires a session; uploads are extension-whitelisted with random names.

## BOTTOM LINE

The repository is now in decent shape. The business risk is concentrated in
three places: **(1) a public mailbox password that must be rotated today,
(2) a live website that has received none of the fixes, and (3) server-only
pieces nobody has backed up.** Fix order: rotate the mailbox password →
deploy carefully (repo files + SERVER_FIXES.md + credentials setup) → verify
live → then Search Console and the CWV measurement that is still outstanding.
