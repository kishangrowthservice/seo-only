# Madrid Software — website source (partial)

Source for madridsoftwaretrainings.com: static course pages (HTML) plus the
PHP blog system (`blogs.json` + templates). This repository is a PARTIAL
snapshot — production also has server-only pages, rewrite rules and the real
Parsedown library, so deploy file-by-file, never as a wholesale copy.

SEO and security documentation lives in `seo/` (audit, implementation log,
validation report, rescan, server-fix pack). Credentials are never stored in
this repo: see `admin-credentials.example.php` and
`mail-credentials.example.php`.
