<?php

include __DIR__ . "/classes/Thumbnail.php";

trimFolder();
function trimFolder()
{
    $dir_name = dirname(__FILE__, 2) . '/input/';
    $images = glob($dir_name . "*.png");
    foreach ($images as $image) {
        generateFavIcons($image);
    }
}

function generateFavIcons($path)
{
    new Thumbnail($path);
}