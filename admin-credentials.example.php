<?php
// SETUP (server only):
// 1. Copy this file to "admin-credentials.php" in the same folder on the server.
//    NEVER commit admin-credentials.php to git — it is listed in .gitignore.
// 2. Choose a NEW admin password (the old one was public in this repository).
// 3. Generate its hash on the server:
//      php -r "echo password_hash('YOUR_NEW_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
// 4. Paste the hash below in place of PASTE_HASH_HERE.
define('ADMIN_USER', 'admin');
define('ADMIN_PASS_HASH', 'PASTE_HASH_HERE');
