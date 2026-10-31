<?php
// SETUP (server only):
// 1. ROTATE the mailbox password first - the old one is public in this repo
//    and in its git history, and is proven live.
// 2. Copy this file to "mail-credentials.php" in the same folder ON THE
//    SERVER. Never commit mail-credentials.php (it is in .gitignore).
// 3. Fill in the mailbox and its NEW password.
// 4. MAIL_TLS_VERIFY: keep true. The mail server certificate currently has
//    a hostname mismatch; fix the certificate (see seo/SERVER_FIXES.md).
//    Only while that is pending may this be set to false - temporarily.
define('SMTP_USER', 'madrid@madridsoftwaretrainings.com');
define('SMTP_PASS', 'PASTE_NEW_PASSWORD_HERE');
// define('MAIL_TLS_VERIFY', false);
