<?php
    
    include __DIR__ . "/classes/TrimPng.php";
    
    trimFolder();

    function trimFolder() {
        $dir_name = __DIR__ . '/artwork/';
        $images = glob($dir_name . "*.png");
        foreach ($images as $image) {
            generateFavIcons($image);
        }
    }
    
    function generateFavIcons($path) {
        new TrimPng($path);
    }