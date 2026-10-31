<?php
require_once 'Parsedown.php';
require_once 'helpers.php';
$Parsedown = new Parsedown();
// Render post bodies safely: no raw HTML / script execution from stored
// content (posts are pure markdown - verified - so output is unchanged).
if (method_exists($Parsedown, 'setSafeMode')) {
    $Parsedown->setSafeMode(true);
}
if (method_exists($Parsedown, 'setMarkupEscaped')) {
    $Parsedown->setMarkupEscaped(true);
}
$blogs = [];
if (file_exists('blogs.json')) {
    $json = file_get_contents('blogs.json');
    $blogs = json_decode($json, true);
    if (!is_array($blogs)) {
        $blogs = [];
    }
}

// ---------------------------------------------------------------------------
// Single blog post page (restored to the repository).
// The blog listing (blog.php) links here as single-blog.php?slug=<slug>.
// SEO behaviour: each post renders its own seoTitle / seoDescription stored
// in blogs.json, a self-referencing canonical at the clean production URL
// /blogs/<slug>, Open Graph article tags, and Article JSON-LD built only
// from the post's own fields.
// ---------------------------------------------------------------------------
$siteBase = 'https://www.madridsoftwaretrainings.com';
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$post = null;
foreach ($blogs as $b) {
    if (isset($b['slug']) && $b['slug'] === $slug) {
        $post = $b;
        break;
    }
}
$notFound = ($post === null);
if ($notFound) {
    http_response_code(404);
}

$seoTitle = $notFound
    ? 'Post Not Found | Madrid Software'
    : (!empty($post['seoTitle']) ? $post['seoTitle'] : $post['title']);
$seoDescription = $notFound
    ? ''
    : (!empty($post['seoDescription']) ? $post['seoDescription'] : mb_substr(strip_tags($post['content']), 0, 155));
$canonicalUrl = $notFound ? $siteBase . '/blogs' : $siteBase . '/blogs/' . $post['slug'];
$postImage = '';
if (!$notFound && !empty($post['image'])) {
    $postImage = preg_match('#^https?://#', $post['image']) ? $post['image'] : $siteBase . '/' . ltrim($post['image'], '/');
}
$articleSchema = null;
if (!$notFound) {
    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post['title'],
        'description' => $seoDescription,
        'image' => $postImage ? [$postImage] : [],
        'datePublished' => date('c', strtotime($post['date'])),
        'author' => ['@type' => 'Person', 'name' => $post['author']],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Madrid Software',
            'logo' => ['@type' => 'ImageObject', 'url' => $siteBase . '/icons/logo.png'],
        ],
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonicalUrl],
    ];
}
// Related posts: prefer posts that share keywords with this one (topical
// internal linking), most recent first within the same score.
$related = [];
if (!$notFound) {
    $kwOf = function ($b) {
        return array_filter(array_map('trim', explode(',', strtolower($b['seoKeywords'] ?? ''))));
    };
    $myKw = $kwOf($post);
    $scored = [];
    foreach ($blogs as $b) {
        if ($b['slug'] === $post['slug']) {
            continue;
        }
        $scored[] = [
            'post' => $b,
            'score' => count(array_intersect($myKw, $kwOf($b))),
            'ts' => strtotime($b['date']) ?: 0,
        ];
    }
    usort($scored, function ($a, $b) {
        return ($b['score'] <=> $a['score']) ?: ($b['ts'] <=> $a['ts']);
    });
    foreach (array_slice($scored, 0, 3) as $s) {
        $related[] = $s['post'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($seoTitle) ?></title>
    <link rel="icon" href="/icons/logo.png" type="image/png" />


    <meta name="description"
        content="<?= htmlspecialchars($seoDescription) ?>">


    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-10860218040"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'AW-10860218040');
    </script>

    <!-- Facebook
      Pixel Code -->
    <script>
        !function (f, b, e, v, n, t, s) {
            if (f.fbq) return; n = f.fbq = function () {
                n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0';
            n.queue = []; t = b.createElement(e); t.async = !0;
            t.src = v; s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '1883058621966713');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=1883058621966713&ev=PageView&noscript=1" /></noscript>
    <!-- End Facebook Pixel Code -->

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />

    <style>
        @keyframes scroll {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .animate-scroll {
            animation: scroll 15s linear infinite;
            animation-play-state: running;

        }

        #scrollTrack:hover {
            animation-play-state: paused;
        }

        .testimonial-card {
            min-width: 300px;
            max-width: 320px;
            background: white;
            border: 1px solid #eee;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }


        .btn-3d {
            box-shadow: 0 6px 0 rgba(0, 0, 0, 0.25);
            border-bottom: 4px solid #001F8B;
            transition: all 0.3s ease-in-out;
            transform: translateY(0);
        }

        .btn-3d:hover {
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.35);
            transform: translateY(-4px);
        }

        @keyframes rocketFly {
            0% {
                transform: translateY(0) rotate(-10deg);
            }

            50% {
                transform: translateY(-10px) rotate(0deg);
            }

            100% {
                transform: translateY(0) rotate(-10deg);
            }
        }

        @keyframes jumpInLeft {
            0% {
                opacity: 0;
                transform: translateX(-100px) scale(0.9);
            }

            60% {
                opacity: 1;
                transform: translateX(20px) scale(1.05);
            }

            100% {
                transform: translateX(0) scale(1);
            }
        }

        .animate-jump-in-left {
            animation: jumpInLeft 1s ease-out both;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }


        .rocket-fly {
            animation: rocketFly 2s ease-in-out infinite;
        }

        /* Add these base responsive styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: outfit;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        /* Custom utility for hiding scrollbars */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            /* IE & Edge */
            scrollbar-width: none;
            /* Firefox */
        }
    </style>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-937335270">
</script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-937335270');
</script>
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>" />
    <?php if ($notFound): ?>
    <meta name="robots" content="noindex" />
    <?php else: ?>
    <meta property="og:type" content="article" />
    <meta property="og:title" content="<?= htmlspecialchars($seoTitle) ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($seoDescription) ?>" />
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>" />
    <?php if ($postImage): ?>
    <meta property="og:image" content="<?= htmlspecialchars($postImage) ?>" />
    <?php endif; ?>
    <meta property="article:published_time" content="<?= date('c', strtotime($post['date'])) ?>" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= htmlspecialchars($seoTitle) ?>" />
    <meta name="twitter:description" content="<?= htmlspecialchars($seoDescription) ?>" />
    <?php if ($articleSchema): ?>
    <script type="application/ld+json"><?= json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <?php endif; ?>
    <?php endif; ?>
</head>

<body data-page-name="Home Page" class="bg-[#ffff]">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Fixed WhatsApp and Phone Buttons at Bottom Right -->
    <div
        class="fixed bottom-4 md:right-4 flex md:flex-col flex-row justify-center w-full md:justify-end  md:w-[170px]  gap-4 z-50 px-4 md:px-0">

        <!-- Phone Button -->
        <a href="tel:+919560785589"
            class="relative inline-block w-full max-w-[300px] rounded-full bg-gradient-to-r from-[#0004FF] to-[#00E5FF] p-[1px]">

            <div class="flex items-center justify-center gap-3 w-full h-full bg-white rounded-full px-4 py-2">
                <img src="./icons/new (2).png" alt="Phone" class="w-[14px] h-[18px]" />
                <span class="text-[#0004FF] font-medium">Call Us</span>
            </div>
        </a>


        <!-- WhatsApp Button -->
        <a href="https://wa.me/919560785589" target="_blank"
            class="w-full max-w-[300px]  flex justify-center items-center gap-2 bg-[#25CC49] text-white px-4 py-2 rounded-full shadow-lg hover:bg-[#20C35D] transition-colors text-center">
            <img src="./icons/new (1).png" alt="WhatsApp" class="w-[20px] h-[19px]" />
            <span>WhatsApp Us</span>
        </a>
    </div>

  <header class="relative w-full shadow-lg md:shadow-none ">
        <div
            class="relative max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between px-4 overflow-visible z-40">
            <!-- Left: Logo -->

            <div class="z-10 flex items-center  md:mb-0 self-start md:self-center mt-3">
                <a href="/">
                    <img src="./images/Madrid logo Final V1.png" alt="Logo"
                        class="w-[180px] md:w-[250px] object-fit-contain" />
                </a>
            </div>


            <!-- Center: Navigation -->
            <div class="w-full hidden xl:block md:w-[750px] md:absolute md:left-1/2 md:-translate-x-1/2 text-white px-4 md:px-[3rem] xl:px-24 py-5 top-0 flex flex-wrap justify-center items-center space-x-4 md:space-x-10"
                style="background-image: url('./images/header.png'); background-size: cover; background-position: center; height: 70px;">
                <div class="flex justify-center items-center w-full space-x-4 md:space-x-10">
                    <a href="/about-us" class="hover:text-gray-300 text-sm md:text-base">About</a>

                    <!-- Our Programs Dropdown (Desktop) -->
                    <div class="relative group">
                        <button class="hover:text-gray-300 text-sm md:text-base focus:outline-none">
                            Our Programs
                        </button>
                        <div
                            class="hidden group-hover:flex absolute top-full left-0 w-56 bg-white text-black shadow-lg rounded-md py-2 flex-col z-50">

                            <!-- Tech Courses with nested dropdown -->
                            <div class="relative group/tech px-4 py-2 hover:bg-gray-100">
                                <a href="" class="w-full block">Tech Courses</a>
                                <div
                                    class="hidden group-hover/tech:block absolute top-0 left-full transform translate-x-0 w-56 bg-white text-black shadow-lg rounded-md py-2 z-[999]">

                                    <!-- Tech subcategories -->
                                    <div class="max-h-80 overflow-auto">
                                        <a href="/data-analytics-course-training-in-delhi"
                                            class="px-4 py-2 hover:bg-gray-100 block">Data Analytics</a>
                                        <a href="/data-science-training-institute-in-delhi"
                                            class="px-4 py-2 hover:bg-gray-100 block">Data Science</a>
                                        <a href="/artificial-intelligence-course-training-in-delhi"
                                            class="px-4 py-2 hover:bg-gray-100 block">Artificial Intelligence</a>
                                        <a href="/full-stack-developer-course-training-in-delhi"
                                            class="px-4 py-2 hover:bg-gray-100 block">Fullstack Development</a>
                                        <a href="/generative-ai-course-in-delhi"
                                            class="px-4 py-2 hover:bg-gray-100 block">Generative AI</a>
                                        <a href="/agentic-ai-training-institute-in-delhi"
                                            class="px-4 py-2 hover:bg-gray-100 block">Agentic AI</a>
                                        <a href="/devops-course-training-in-delhi"
                                            class="px-4 py-2 hover:bg-gray-100 block">DevOps</a>
 <a href="/aws-course-training-in-delhi" class="px-4 py-2 hover:bg-gray-100 block">AWS</a>
                                        
<a href="/mern-stack-training-institute-in-delhi" class="px-4 py-2 hover:bg-gray-100 block">Mern Stack</a>
                                        <a href="/power-bi-training-institute-in-delhi" class="px-4 py-2 hover:bg-gray-100 block">Power BI</a>
                                        <a href="/business-analytics-course-training-in-delhi"
                                            class="px-4 py-2 hover:bg-gray-100 block">Business Analytics</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Design Courses with nested dropdown -->
                            <div class="relative group/design px-4 py-2 hover:bg-gray-100">
                                <a href="" class="w-full block">Design Courses</a>
                                <div
                                    class="hidden group-hover/design:block absolute top-0 left-full transform translate-x-0 w-56 bg-white text-black shadow-lg rounded-md py-2 z-50">
                                    <a href="/ui-ux-design-course-training-in-delhi"
                                        class="px-4 py-2 hover:bg-gray-100 block">UI/UX Design Course</a>
                                    <a href="/graphic-designing-training-institute-in-delhi"
                                        class="px-4 py-2 hover:bg-gray-100 block">Graphic Design Course</a>
                                </div>
                            </div>

                            <!-- Management & Marketing Courses with nested dropdown -->
                            <div class="relative group/mgmt px-4 py-2 hover:bg-gray-100">
                                <a href="" class="w-full block">Management & Marketing Courses</a>
                                <div
                                    class="hidden group-hover/mgmt:block absolute top-0 left-full transform translate-x-0 w-56 bg-white text-black shadow-lg rounded-md py-2 z-50">
                                    <a href="/product-management-training-institute-in-delhi"
                                        class="px-4 py-2 hover:bg-gray-100 block">Product Management</a>
                                    <a href="/digital-marketing-training-institute-in-delhi"
                                        class="px-4 py-2 hover:bg-gray-100 block">Digital Marketing</a>
                                </div>
                            </div>
                            <!-- Trending Courses -->
                            <div class="relative group/mgmt px-4 py-2 hover:bg-gray-100">
                                <a href="" class="w-full block">Trending Courses</a>
                                <div
                                    class="hidden group-hover/mgmt:block absolute top-0 left-full transform translate-x-0 w-56 bg-white text-black shadow-lg rounded-md py-2 z-50">
                                    <a href="/data-analytics-course-with-genai-training-in-delhi"
                                        class="px-4 py-2 hover:bg-gray-100 block">Data Analytics with Generative AI</a>
                                    <a href="/data-science-course-with-genai-training-in-delhi"
                                        class="px-4 py-2 hover:bg-gray-100 block">Data Science with Generative AI</a>
                                </div>
                            </div>

                        </div>
                    </div>

                    <a href="/testimonials" class="hover:text-gray-300 text-sm md:text-base">Testimonials</a>
                    <a href="/blogs" class="hover:text-gray-300 text-sm md:text-base">Blog</a>
                    <a href="/contact-us" class="hover:text-gray-300 text-sm md:text-base">Contact</a>
                </div>
            </div>

            <!-- Right: Social Icons -->
            <div class="z-10 hidden xl:flex items-center space-x-4 py-4">
        <a href="https://www.instagram.com/madrid_software_trainings?igsh=eTJ1c3lsaGUxMXl6&utm_source=qr" target="_blank" rel="noopener noreferrer">
          <img src="https://cdn-icons-png.flaticon.com/512/1384/1384063.png" alt="Instagram" class="w-5 h-5 md:w-6 md:h-6" />
        </a>
        <a href="https://www.linkedin.com/company/madrid-software/" target="_blank" rel="noopener noreferrer">
          <img src="./images/icons8-linkedin-48.png" alt="LinkedIn" class="w-5 h-5 md:w-7 md:h-7" />
        </a>
        <a href="https://www.youtube.com/@MadridSoftware" target="_blank" rel="noopener noreferrer">
          <img src="https://cdn-icons-png.flaticon.com/512/1384/1384060.png" alt="YouTube" class="w-5 h-5 md:w-7 md:h-7" />
        </a>
      </div>

            <!-- Hamburger Icon -->
            <div class="absolute right-4 top-4 xl:hidden z-20">
                <button id="menuToggle">
                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="xl:hidden hidden bg-white w-full shadow-md absolute top-full left-0 z-50">
            <div class="flex flex-col items-center py-4 space-y-4 text-black">
                <a href="/about-us" class="hover:text-gray-500">About</a>

                <!-- Our Programs with Toggle (Mobile) -->
                <div class="w-full text-center">
                    <button class="text-black hover:text-gray-500 font-medium" onclick="toggleSubMenu()">
                        Our Programs
                    </button>
                    <div id="subMenu"
                        class="hidden flex flex-col space-y-2 mt-2 bg-gray-100 rounded-2xl p-2 text-left px-4">

                        <!-- Tech Courses -->
                        <div class="flex flex-col items-center">
                            <button class="font-semibold hover:text-gray-700 " onclick="toggleSubCategory('tech')">Tech
                                Courses</button>
                            <div id="tech" class="hidden ml-4 mt-2 space-y-1 text-center">
                                <a href="/data-analytics-course-training-in-delhi"
                                    class="block hover:text-gray-500">Data Analytics</a>
                                <a href="/data-science-training-institute-in-delhi"
                                    class="block hover:text-gray-500">Data Science</a>
                                <a href="/artificial-intelligence-course-training-in-delhi"
                                    class="block hover:text-gray-500">Artificial
                                    Intelligence</a>
                                <a href="/full-stack-developer-course-training-in-delhi"
                                    class="block hover:text-gray-500">Fullstack Development</a>
                                <a href="/generative-ai-course-in-delhi" class="block hover:text-gray-500">Generative
                                    AI</a>
                                <a href="/agentic-ai-training-institute-in-delhi"
                                    class="block hover:text-gray-500">Agentic AI</a>
                                <a href="/devops-course-training-in-delhi" class="block hover:text-gray-500">DevOps</a>
                                                                <a href="/aws-course-training-in-delhi" class="block hover:text-gray-500">AWS</a>

                                <a href="/mern-stack-training-institute-in-delhi" class="block hover:text-gray-500">Mern Stack</a>
                                <a href="/power-bi-training-institute-in-delhi" class="block hover:text-gray-500">Power BI</a>
                                <a href="/business-analytics-course-training-in-delhi"
                                    class="block hover:text-gray-500">Business Analytics</a>
                            </div>
                        </div>

                        <!-- Design Courses -->
                        <div class="flex flex-col items-center">
                            <button class="font-semibold hover:text-gray-700"
                                onclick="toggleSubCategory('design')">Design Courses</button>
                            <div id="design" class="hidden ml-4 mt-2 space-y-1 text-center">
                                <a href="/ui-ux-design-course-training-in-delhi" class="block hover:text-gray-500">UI/UX
                                    Design Course</a>
                                <a href="/graphic-designing-training-institute-in-delhi"
                                    class="block hover:text-gray-500">Graphic Design Course</a>
                            </div>
                        </div>

                        <!-- Management & Marketing -->
                        <div class="flex flex-col items-center">
                            <button class="font-semibold hover:text-gray-700"
                                onclick="toggleSubCategory('mgmt')">Management &
                                Marketing</button>
                            <div id="mgmt" class="hidden ml-4 mt-2 space-y-1 text-center  ">
                                <a href="/product-management-training-institute-in-delhi"
                                    class="block hover:text-gray-500">Product Management</a>
                                <a href="/digital-marketing-training-institute-in-delhi"
                                    class="block hover:text-gray-500">Digital Marketing</a>

                            </div>
                        </div>

                        <!-- trending course -->
                        <div class="flex flex-col items-center">
                            <button class="font-semibold hover:text-gray-700" onclick="toggleSubCategory('trend')">
                                Trending Courses</button>
                            <div id="trend" class="hidden ml-4 mt-2 space-y-1 text-center  ">
                                <a href="/data-analytics-course-with-genai-training-in-delhi"
                                    class="block hover:text-gray-500">Data Analytics with Generative AI</a>
                                <a href="/data-science-course-with-genai-training-in-delhi"
                                    class="block hover:text-gray-500">Data Science with Generative AI</a>
                            </div>
                        </div>
                    </div>
                </div>



                <a href="/testimonials" class="hover:text-gray-300 text-sm md:text-base">Testimonials</a>
                <a href="/blogs" class="hover:text-gray-300 text-sm md:text-base">Blog</a>
                <a href="/contact-us" class="hover:text-gray-300 text-sm md:text-base">Contact</a>

                 <div class="flex space-x-4 pt-2">
            <a href="https://www.instagram.com/madrid_software_trainings?igsh=eTJ1c3lsaGUxMXl6&utm_source=qr" target="_blank" rel="noopener noreferrer">
              <img src="https://cdn-icons-png.flaticon.com/512/1384/1384063.png" alt="Instagram" class="w-5 h-5" />
            </a>
            <a href="https://www.linkedin.com/company/madrid-software/" target="_blank" rel="noopener noreferrer">
              <img src="./images/icons8-linkedin-48.png" alt="LinkedIn" class="w-5 h-5" />
            </a>
            <a href="https://www.youtube.com/@MadridSoftware" target="_blank" rel="noopener noreferrer">
              <img src="https://cdn-icons-png.flaticon.com/512/1384/1384060.png" alt="YouTube" class="w-5 h-5" />
            </a>
          </div>
            </div>
        </div>
    </header>

<!-- Single Blog Post -->
<section class="max-w-[900px] mx-auto px-4 py-10">
    <?php if ($notFound): ?>
        <h1 class="text-3xl md:text-4xl font-semibold mb-4">Post not found</h1>
        <p class="text-gray-700 mb-6">The article you are looking for is not available. Browse all articles on the <a class="text-blue-700 underline" href="/blogs">Madrid Software blog</a>.</p>
    <?php else: ?>
        <nav class="text-xs text-gray-500 mb-4" aria-label="Breadcrumb">
            <a href="/" class="hover:underline">Home</a> &raquo;
            <a href="/blogs" class="hover:underline">Blogs</a> &raquo;
            <span><?= htmlspecialchars($post['title']) ?></span>
        </nav>
        <h1 class="text-3xl md:text-[40px] font-semibold leading-snug mb-4"><?= htmlspecialchars($post['title']) ?></h1>
        <div class="flex flex-wrap gap-4 text-sm text-gray-500 font-medium mb-6">
            <p>Author : <?= htmlspecialchars($post['author']) ?></p>
            <p><?= date('j M Y', strtotime($post['date'])) ?></p>
            <p><?= calculateReadTime(strip_tags($post['content'])) ?></p>
        </div>
        <?php if (!empty($post['image'])): ?>
        <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="w-full rounded-xl mb-8" />
        <?php endif; ?>
        <article class="blog-content text-gray-800 leading-8">
            <?= $Parsedown->text($post['content']) ?>
        </article>
    <?php endif; ?>
</section>

<?php if (!$notFound && !empty($related)): ?>
<!-- Related Articles -->
<section class="max-w-[1300px] mx-auto px-4 pb-12">
    <h2 class="text-2xl font-bold mb-6">Related Articles</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
        <?php foreach ($related as $blog): ?>
        <div>
            <div class="relative mb-4">
                <a href="/blogs/<?= urlencode($blog['slug']) ?>">
                    <img src="<?= htmlspecialchars($blog['image']) ?>" class="w-full h-60 object-cover rounded-xl" alt="<?= htmlspecialchars($blog['title']) ?>" loading="lazy" />
                </a>
                <div class="absolute top-2 right-2 bg-blue-700 text-white text-xs px-3 py-1 rounded-full text-center leading-tight font-semibold">
                    <?= date('d', strtotime($blog['date'])) ?><br><?= date('M', strtotime($blog['date'])) ?>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs text-gray-500 font-medium">
                    <p>BLOG</p>
                    <p>Author : <?= htmlspecialchars($blog['author']) ?></p>
                </div>
                <div class="flex justify-between items-start">
                    <h3 class="text-sm font-semibold text-black"><?= htmlspecialchars($blog['title']) ?></h3>
                    <span class="text-sm text-gray-500"><?= date('j M Y', strtotime($blog['date'])) ?></span>
                </div>
                <p class="text-xs text-gray-600">
                    <?= htmlspecialchars(mb_substr(strip_tags($blog['content']), 0, 100)) ?>...
                </p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

</body>

</html>
