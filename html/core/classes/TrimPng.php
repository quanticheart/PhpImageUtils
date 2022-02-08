<?php

class TrimPng
{

    private string $folder;

    function __construct($path)
    {
        $this->folder = dirname(__FILE__, 3) . "/output/trim/";
        $this->createDir($this->folder);
        $file = $this->trimImage($path);
        echo "---- FINISH ---- $file" . "</br>";
    }

    function createDir($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    function getImage($filename)
    {
        $type = exif_imagetype($filename);

        switch ($type) {
            case IMAGETYPE_GIF:
                return imagecreatefromgif($filename);
                break;
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($filename);
                break;
            case IMAGETYPE_PNG:
                return imagecreatefrompng($filename);
                break;
            case IMAGETYPE_BMP:
                return imagecreatefromwbmp($filename);
                break;
            default:
                print 'UNKNOWN IMAGE TYPE: ' . image_type_to_mime_type($type) . "\n";
                return FALSE;
                break;
        }
    }

    function color($im, $x, $y)
    {
        return imagecolorat($im, $x, $y);
    }

    function getFileName($path)
    {
        $a = explode("/", $path);
        return end($a);
    }


    function verifyColor($im, $x, $y)
    {
        $rgba = imagecolorat($im, $x, $y);
        $colorInfo = imagecolorsforindex($im, $rgba);
        return $colorInfo["alpha"] >= 127 ? false : true;
    }

    function trimImage($path)
    {
        //load the image
        $img = $this->getImage($path);
//        $img = imagecropauto($origR, IMG_CROP_DEFAULT);

//find the size of the borders
        $b_top = 0;
        $b_btm = 0;
        $b_lft = 0;
        $b_rt = 0;

//top
        for (; $b_top < imagesy($img); ++$b_top) {
            for ($x = 0; $x < imagesx($img); ++$x) {
                if ($this->verifyColor($img, $x, $b_top)) {
                    break 2; //out of the 'top' loop
                }
            }
        }

//bottom
        for (; $b_btm < imagesy($img); ++$b_btm) {
            for ($x = 0; $x < imagesx($img); ++$x) {
                if ($this->verifyColor($img, $x, imagesy($img) - $b_btm - 1)) {
                    break 2; //out of the 'bottom' loop
                }
            }
        }

//left
        for (; $b_lft < imagesx($img); ++$b_lft) {
            for ($y = 0; $y < imagesy($img); ++$y) {
                if ($this->verifyColor($img, $b_lft, $y)) {
                    break 2; //out of the 'left' loop
                }
            }
        }

//right
        for (; $b_rt < imagesx($img); ++$b_rt) {
            for ($y = 0; $y < imagesy($img); ++$y) {
                if ($this->verifyColor($img, imagesx($img) - $b_rt - 1, $y)) {
                    break 2; //out of the 'right' loop
                }
            }
        }

//copy the contents, excluding the border
        $newImg = imagecreatetruecolor(imagesx($img) - ($b_lft + $b_rt), imagesy($img) - ($b_top + $b_btm));
        imagealphablending($newImg, true);
        $transparent = imagecolorallocatealpha($newImg, 0, 0, 0, 127);
        imagefill($newImg, 0, 0, $transparent);
        imagecopy($newImg, $img, 0, 0, $b_lft, $b_top, imagesx($newImg), imagesy($newImg));
        imagealphablending($newImg, false);
        imagesavealpha($newImg, true);
        $filename = $this->getFileName($path);
        imagepng($newImg, "$this->folder/$filename");
        imagedestroy($img);
        imagedestroy($newImg);

        return $filename;
    }
}
 