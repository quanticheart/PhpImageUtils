<?php

class Thumbnail
{

    /**
     * @var array $sizes Optional. An array of sizes (each size is an array with a width and height) that the source image should be rendered at in the generated ICO file. If sizes are not supplied, the size of the source image will be used.
     */
    public array $sizes = array(200, 256, 512);
    public array $smallSizes = array(80);

    public string $filename = "thumb.png"; // default init
    public string $filenameSmall = "thumb.png"; // default init
    public string $path = __DIR__ . "/thumb";

    public bool $aspectRatio = false;

    function getFileName($path)
    {
        $a = explode("/", $path);
        $b = end($a);
        $c = explode(".", $b);
        return $c[0] . ".png";
    }

    function getSmallFileName($path)
    {
        $a = explode("/", $path);
        $b = end($a);
        $c = explode(".", $b);
        return $c[0] . "-thumb-small.png";
    }

    function getDir($size)
    {
        $dirPng = $this->path . "/$size";
        $this->createDir($dirPng);
        return $dirPng . "/$this->filename";
    }

    function getDirSmall($size)
    {
        $dirPng = $this->path . "/small/$size";
        $this->createDir($dirPng);
        return $dirPng . "/$this->filename";
    }

    function createDir($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    /**
     * Constructor - Create a new ICO generator.
     *
     * If the constructor is not passed a file, a file will need to be supplied using the {@link PHP_ICO::add_image}
     * function in order to generate an ICO file.
     *
     * @param bool $file Optional. Path to the source image file.
     */
    function __construct($file = false)
    {
        $required_functions = array(
            'getimagesize',
            'imagecreatefromstring',
            'imagecreatetruecolor',
            'imagecolortransparent',
            'imagecolorallocatealpha',
            'imagealphablending',
            'imagesavealpha',
            'imagesx',
            'imagesy',
            'imagecopyresampled',
        );

        foreach ($required_functions as $function) {
            if (!function_exists($function)) {
                trigger_error("The PHP_ICO class was unable to find the $function function, which is part of the GD library. Ensure that the system has the GD library installed and that PHP has access to it through a PHP interface, such as PHP's GD module. Since this function was not found, the library will be unable to create ICO files.");
                return;
            }
        }

        if (false != $file) {
            $this->path = dirname(__FILE__, 3) . "/output/images/thumbnail/";
            $this->createDir($this->path);
            $this->filename = $this->getFileName($file);
            $this->filenameSmall = $this->getSmallFileName($file);
            $this->generateThumb($file);
            $this->generateSmallThumb($file);
            $this->printHtmlInsert();
        }
    }

    /**
     * Add an image to the generator.
     *
     * This function adds a source image to the generator. It serves two main purposes: add a source image if one was
     * not supplied to the constructor and to add additional source images so that different images can be supplied for
     * different sized images in the resulting ICO file. For instance, a small source image can be used for the small
     * resolutions while a larger source image can be used for large resolutions.
     *
     * @param string $file Path to the source image file.
     */
    function generateThumb($file)
    {
        $im = $this->createIm($file);
        foreach ($this->sizes as $size) {
            $width = $size;
            $height = $size;

            if ($this->aspectRatio) {
                $new_im = $this->createAspectRatioSize($file, $im, $width, $height);
            } else {
                $new_im = $this->createFixedSize($im, $width, $height);
            }

            $this->savePng($new_im, $this->getDir($size));
            imagedestroy($new_im);
        }
        imagedestroy($im);
    }

    /**
     * Add an image to the generator.
     *
     * This function adds a source image to the generator. It serves two main purposes: add a source image if one was
     * not supplied to the constructor and to add additional source images so that different images can be supplied for
     * different sized images in the resulting ICO file. For instance, a small source image can be used for the small
     * resolutions while a larger source image can be used for large resolutions.
     *
     * @param string $file Path to the source image file.
     */
    function generateSmallThumb($file)
    {
        $im = $this->createIm($file);
        foreach ($this->smallSizes as $size) {
            $width = $size;
            $height = $size;

            if ($this->aspectRatio) {
                $new_im = $this->createAspectRatioSize($file, $im, $width, $height, true);
            } else {
                $new_im = $this->createFixedSize($im, $width, $height, true);
            }

            $this->savePng($new_im, $this->getDirSmall($size));
            imagedestroy($new_im);
        }
        imagedestroy($im);
    }

    function createFixedSize($im, $width, $height, $blur = false)
    {
        $new_im = imagecreatetruecolor($width, $height);

        $color = imagecolorallocatealpha($new_im, 0, 0, 0, 127); //fill transparent background

        imagecolortransparent($new_im, $color);
        imagealphablending($new_im, false);
        imagesavealpha($new_im, true);

        $src_w = imagesx($im);
        $src_h = imagesy($im);

        if ($src_w > $src_h) {
            $dst_w = $width;
            $dst_h = ($dst_w * $src_h) / $src_w;

            if ($dst_h > $height) {
                $dst_h = $height;
                $dst_w = ($dst_h * $src_w) / $src_h;
            }
        } else {
            $dst_h = $height;
            $dst_w = ($dst_h * $src_w) / $src_h;

            if ($dst_w > $width) {
                $dst_w = $width;
                $dst_h = ($dst_w * $src_h) / $src_w;
            }
        }

        imagefill($new_im, 0, 0, $color);
        imagesavealpha($new_im, true);

        $dst_x = abs($width - $dst_w) / 2;
        $dst_y = abs($height - $dst_h) / 2;

        imagecopyresampled($new_im, $im, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        if ($blur) {
            imagefilter($new_im, IMG_FILTER_PIXELATE, 3);
            imagetruecolortopalette($new_im, false, 255);

        }
        return $new_im;
    }

    function createAspectRatioSize($file, $im, $w, $h, $blur = false)
    {
        list($width, $height) = getimagesize($file);
        $r = $width / $height;

        if ($w / $h > $r) {
            $newwidth = $h * $r;
            $newheight = $h;
        } else {
            $newheight = $w / $r;
            $newwidth = $w;
        }
        $new_im = imagecreatetruecolor($newwidth, $newheight);
        $color = imagecolorallocatealpha($new_im, 0, 0, 0, 127); //fill transparent background

        imagecolortransparent($new_im, $color);
        imagealphablending($new_im, false);
        imagesavealpha($new_im, true);

        imagecopyresampled($new_im, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        if ($blur) {
            imagefilter($new_im, IMG_FILTER_PIXELATE, 3);
            imagetruecolortopalette($new_im, false, 255);

        }
        return $new_im;
    }

    function savePng($image, $filePath, $quality = 9, $filters = PNG_ALL_FILTERS)
    {
        imagepng($image, $filePath, $quality, $filters);
    }

    function verifyColor($im, $x, $y)
    {
        $rgba = imagecolorat($im, $x, $y);
        $colorInfo = imagecolorsforindex($im, $rgba);
        return $colorInfo["alpha"] >= 127 ? false : true;
    }

    /**
     * Read in the source image file and convert it into a GD image resource.
     *
     * @access private
     * @param $file
     * @return bool|false|resource
     */
    function createIm($file)
    {
        // Run a cheap check to verify that it is an image file.
        if (false === ($size = getimagesize($file)))
            return false;

        if (false === ($file_data = file_get_contents($file)))
            return false;

        if (false === ($im = imagecreatefromstring($file_data)))
            return false;

        return $im;
    }

    function printHtmlInsert()
    {
        echo '---- FINISH ----------------------------------------------------------' . "</br>";
    }
}