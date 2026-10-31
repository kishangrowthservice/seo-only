<?php
// Real 404 handler: sends a true 404 status (the server previously returned
// HTTP 200 for its "404 Not Found" page — a site-wide soft-404).
// Wire it up in .htaccess with:  ErrorDocument 404 /404.php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>404 Not Found | Madrid Software</title>
    <meta name="robots" content="noindex" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-800">
    <main class="max-w-[800px] mx-auto px-4 py-20 text-center">
        <h1 class="text-4xl md:text-5xl font-semibold mb-4">404 — Page Not Found</h1>
        <p class="text-gray-600 mb-8">The page you are looking for is not available. It may have moved, or the link may be incorrect.</p>
        <nav class="flex flex-wrap justify-center gap-4">
            <a href="/" class="bg-blue-700 text-white px-5 py-2 rounded-full font-medium">Home</a>
            <a href="/data-science-training-institute-in-delhi" class="bg-blue-700 text-white px-5 py-2 rounded-full font-medium">Data Science Course</a>
            <a href="/blogs" class="bg-blue-700 text-white px-5 py-2 rounded-full font-medium">Blog</a>
            <a href="/contact-us" class="bg-blue-700 text-white px-5 py-2 rounded-full font-medium">Contact Us</a>
        </nav>
        <p class="text-gray-500 mt-10">Need help? Call us on <a href="tel:+919560785589" class="text-blue-700 underline">+91 95607 85589</a></p>
    </main>
</body>
</html>
