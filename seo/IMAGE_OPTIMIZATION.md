# Image optimisation — referenced images (1 Nov 2026)

## What was done
Every image actually loaded by the 66 live pages was inventoried (568
unique raster files, 176.7 MB served). Each was hash-compared with its
repo copy: **543 were identical** and were optimised **in place — same
filename, same format, nothing deleted, no page markup changed**:
JPEG -> quality 82, progressive; PNG -> lossless optimise, photographic
PNGs over 100 KB quantised; anything wider/taller than 1920 px resized to
fit (aspect kept). If the optimised output was not smaller, the original
bytes were kept (93 files). Every output file was re-opened and verified.

## Result
**543 files: 152.4 MB -> 64.2 MB (saved 88.1 MB, 58%).**
449 files actually changed; the rest were already optimal.

| Page | Images | Before | After |
|---|---|---|---|
| Homepage | 82 | 11.7 MB | 8.5 MB |
| Blog index (/blogs) | 45 | 120.6 MB | 43.3 MB |
| Blog post (data-analyst-salary) | 11 | 0.5 MB | 0.3 MB |
| Data Science Delhi | 80 | 3.9 MB | 3.7 MB |
| Data Analytics Delhi | 80 | 6.4 MB | 6.2 MB |
| Contact | 12 | 0.3 MB | 0.2 MB |

## Biggest single wins
| File | Before | After |
|---|---|---|
| images/blogs/blog_69340c9d49d101.01312339.jpg | 17.6 MB | 0.26 MB |
| images/blogs/blog_69c50745c49786.86844531.jpg | 14.4 MB | 0.18 MB |
| images/blogs/blog_68a06577903611.62228965.jpg | 12.6 MB | 0.24 MB |
| images/blogs/blog_689d941c2dc3e5.71357567.jpg | 10.2 MB | 0.22 MB |
| images/blogs/blog_6950236d7d1cc3.95034831.jpg | 9.5 MB | 0.26 MB |
| images/blogs/blog_68a44ba062f0d2.75331417.jpg | 3.0 MB | 0.22 MB |
| images/futuristic-robot-using-laptop 1.png | 3.9 MB | 1.48 MB |
| images/generative-ai-training.png | 5.2 MB | 3.10 MB |
| images/1389b64364e41d3a8a9d651e0c296d8b9a9149b4.png | 2.8 MB | 1.51 MB |
| images/blogs/blog_69bd92ac6b42c4.80672003.png | 2.7 MB | 1.46 MB |
| images/blogs/blog_69be7d653f9a35.38004795.png | 2.7 MB | 1.48 MB |
| images/blogs/blog_69465f4d99df36.21771916.png | 1.7 MB | 0.52 MB |
| images/e3515dd4eb954197c63d69921c6168ab8ff68a7a.png | 1.4 MB | 0.24 MB |
| images/blogs/blog_68dafb266fc2d3.91272201.png | 1.6 MB | 0.48 MB |
| images/blogs/blog_68a6decd06c6a3.79791731.jpg | 1.1 MB | 0.16 MB |

## Delivery (why the binaries are not in this commit)
The automation connection used for this repo accepts text files only, so
the 449 changed images (57.1 MB) are delivered as a zip
(`madrid-optimized-images.zip`, paths preserved) alongside this work, and
are also kept in the project workspace. To make them permanent in git,
the repo owner can unzip and push once with normal git access — or, at
deployment time, simply unzip the archive over the site's root folder on
the server (every path matches the live path it was measured from).

## Not touched (deliberately)
- **25 files (24.3 MB) whose repo copy DIFFERS from the live file** —
  mostly `hiring-partners/*.png`. Optimising from the live copy and
  overwriting the repo version could destroy a newer repo-side edit;
  direction cannot be proven from hashes. List:
- `hiring-partners/0a5fda303b423ee1e24faf42229481ccdfdb2340.png`
- `hiring-partners/11df042afb66dcb65a79e959b6de58397ee12c2f.png`
- `hiring-partners/20677a0bbe67ad57bcea1a72b8112c6860e905b4.png`
- `hiring-partners/233b1ca548ac5a2dcb231169a2e34c2693991b23.png`
- `hiring-partners/420323c053dbff22ba6d675b4cd6d0ca424c40c5.png`
- `hiring-partners/4afae514fa57073a3de77ced1210433a02a2fb5b.png`
- `hiring-partners/58c0b426632bc60b524a94ea7d3e7cdbadc6a70f.png`
- `hiring-partners/69be230fcabb2cf02bc27fe00fb501830c87b8a5.png`
- `hiring-partners/7063c9988c6d540df70a4c18e5a91ea465029b6a.png`
- `hiring-partners/85be64dc88434ef2d2fa3567f262151ffea6f771.png`
- `hiring-partners/9b82ea100057089077d397b076a5d3d4ff10d9b4.png`
- `hiring-partners/a275a8de4dad768fb7e5173cc5e38014b8731e28.png`
- `hiring-partners/a9eeaf1539bbcf750a96d316313a0637d3816593.png`
- `hiring-partners/dc5baeb49239b454be816bb4bffcf4bae5804078.png`
- `hiring-partners/f23f06b5da79c757979abc87d01ba558f1f683a2.png`
- `images/author-avatar.jpg`
- `images/blogs/blog_6896deb2e56a15.09009655.jpg`
- `images/blogs/blog_6a351d7abff958.09714426.png`
- `images/blogs/blog_6ace066ed15c59.26070519.png`
- `images/blogs/blog_6ae176c6883ba5.25092198.png`
- `images/c36467d891562fcf75860b2873333157f6849939.png`
- `images/powerbi-icon.png`
- `testimonials/icons/Group 37514.png`
- `testimonials/images/icons8-linkedin-48.png`
- `testimonials/images/whatsapp.png`
- The ~350 images in the repo that no live page references (incl. both
  Archive.zip files, left untouched per the owner's delete-nothing rule)
  have zero effect on page speed and were not processed.

## Honest limits
- /blogs still loads ~43 MB of images after optimisation: the index page
  pulls every post's featured image. The structural fix is thumbnails +
  lazy-loading in the blog template, a separate change.
- WebP versions would roughly halve photo weight again, but they need
  markup changes (<picture>) — most course pages exist only on the
  server, so that step belongs to deployment.
- Scores will only move once these files are actually on the server.
