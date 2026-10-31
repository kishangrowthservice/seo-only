<?php
/**
 * helpers Methods
 */


function calculateReadTime($content) {
    $words = explode(" ", $content); 
    $wordCount = count($words);
    $per_min_speed = 100; 
    $time = ceil($wordCount / $per_min_speed);
    return $time . ' min read';
}


function asset($path) {
    return "https://" . $_SERVER['SERVER_NAME'] . '/' . ltrim($path, '/');
}