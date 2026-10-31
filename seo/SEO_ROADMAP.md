# SEO Roadmap — seo-only (Madrid Software)

Forward-looking companion to `SEO_AUDIT.md`. Batch A (repo-local fixes) is
implemented and validated; the items below remain, in recommended order.

## Needs owner input (Batch B — see Implementation Plan for detail)

1. **B1 · Rating schema truth (SEO-05):** supply the real review source/figures
   (e.g. Google Business Profile) or approve removing `aggregateRating`/`review`
   blocks from `index.html` and `datascience.html`. Highest risk-reduction per effort.
2. **B5 · Admin credential (SEO-16):** rotate the hardcoded password in
   `admin-login.php`, move it out of source. (Security task with direct SEO
   downside if ignored — hacked sites get spam-injected.)
3. **B3 · Live sitemap cleanup (SEO-02):** confirm how the production sitemap
   is generated, then remove the malformed (`/devops./…`), truncated
   (`…-delh`) and duplicate (`/contact.html`, `/terms-condition`) entries;
   align it with the repo generator's output (40 verified URLs) plus the
   production-only course pages after checking each one returns 200.
4. **B4 · Server redirects:** 301 `/blog.php` → `/blogs`, `/blogs.php` → `/blogs`,
   `/*.html` course files → their clean URLs. Requires server access + approval.
5. **B2 · City pages (SEO-08):** decide per city — unique local content
   (owner-supplied facts) or consolidation into the Delhi course page.
6. **B6 · Asset cleanup (SEO-12):** remove `Archive.zip` files (65 MB) and
   unreferenced multi-MB images from the repo; compress in-use hero images.

## Data unlocks

7. **Search Console:** verify the property, submit the cleaned sitemap, and
   use Coverage/Pages reports to answer what this audit could not: which of
   the 66 sitemap URLs and 32 posts are actually indexed, and which URL
   variants Google chose as canonical.
8. **Core Web Vitals lab pass:** Lighthouse on the homepage and Delhi course
   page (local copies), then targeted fixes — hero image weight/format first.

## Editorial micro-tasks

9. **Blog meta tidy-up (SEO-13):** trim the 7 over-long `seoDescription`
   values (224–348 chars) in `blogs.json` to ~155 chars, owner-approved wording.
10. **Post internal linking:** add 1–2 contextual links from relevant post
    bodies to their matching course pages (per-post, reviewable edits only).
11. **Course schema consistency (SEO-14):** extend Course JSON-LD to the
    GenAI page (and city pages if they survive B2) using only facts visible
    on each page — after B1 sets the precedent for truthful schema.

## Standing rules for future SEO work on this repo

- Repo ≠ production: always diff against the server before deploying any
  template or sitemap change (SEO-01).
- No invented facts, ratings, addresses, prices or metrics in content or schema.
- Canonical/redirect changes are production decisions — approval first.
