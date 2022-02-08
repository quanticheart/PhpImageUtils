<?php

class Favicon
{

    /**
     * Images in the BMP format.
     *
     * @var array
     * @access private
     */
    public array $_images = array();
    /**
     * @var array $sizes Optional. An array of sizes (each size is an array with a width and height) that the source image should be rendered at in the generated ICO file. If sizes are not supplied, the size of the source image will be used.
     */
    public array $sizes = array(16, 32, 48, 57, 64, 76, 96, 120, 128, 144, 152, 180, 192, 196, 228);
    public int $maxIcoFileSize = 32;

    public string $filename = "favicon.ico"; // default init
    public string $filenamePng = "favicon.png"; // default init
    public string $path = "../../output";

    function getFileNameIco($path)
    {
        $a = explode("/", $path);
        $b = end($a);
        $c = explode(".", $b);
        return $c[0] . ".ico";
    }

    function getFileNamePng($size)
    {
        $newDir = explode(".", $this->filename);
        return $newDir[0] . "-$size.png";
    }

    function getDirIco()
    {
        return $this->path . "/$this->filename";
    }

    function getDirPng($size)
    {
        $dirPng = $this->path . "/$size";
        $this->filenamePng = $this->getFileNamePng($size);
        $this->createDir($dirPng);
        return $dirPng . "/$this->filenamePng";
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
            $this->path = dirname(__FILE__, 3) . "/output/favicon/";
            $this->createDir($this->path);
            $this->filename = $this->getFileNameIco($file);
            $this->generateFavicon($file);
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
    function generateFavicon($file)
    {
        $im = $this->createIm($file);

        foreach ($this->sizes as $size) {
            $width = $size;
            $height = $size;

            $new_im = imagecreatetruecolor($width, $height);

            imagecolortransparent($new_im, imagecolorallocatealpha($new_im, 0, 0, 0, 127));
            imagealphablending($new_im, false);
            imagesavealpha($new_im, true);

            $src_w = imagesx($im);
            $src_h = imagesy($im);

            if ($src_w > 256 || $src_h > 256) {
                trigger_error('ICO images cannot be larger than 256 pixels wide/tall', E_USER_WARNING);
                die();
            }

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

            $dst_x = abs($width - $dst_w) / 2;
            $dst_y = abs($height - $dst_h) / 2;

            $color = imagecolorallocatealpha($new_im, 0, 0, 0, 127); //fill transparent background

            imagefill($new_im, 0, 0, $color);
            imagesavealpha($new_im, true);
            if (false === imagecopyresampled($new_im, $im, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $src_w, $src_h))
                continue;

            if ($size <= $this->maxIcoFileSize) {
                $this->createDataForFileIco($new_im);
            }

            $this->savePng($new_im, $this->getDirPng($size));
            imagedestroy($new_im);
        }
        imagedestroy($im);
        $this->saveIco();
    }

    function savePng($image, $filePath, $quality = 9, $filters = PNG_NO_FILTER)
    {
        imagepng($image, $filePath, $quality, $filters);
    }

    /**
     * Write the ICO file data to a file path.
     *
     * @return boolean true on success and false on failure.
     */
    function saveIco()
    {
        $file = $this->getDirIco();

        if (false === ($data = $this->getIcoTmpFileData()))
            return false;

        if (false === ($fh = fopen($file, 'w')))
            return false;

        if (false === (fwrite($fh, $data))) {
            fclose($fh);
            return false;
        }

        fclose($fh);
        return true;
    }

    /**
     * Generate the final ICO data by creating a file header and adding the image data.
     *
     * @access private
     */
    function getIcoTmpFileData()
    {
        if (!is_array($this->_images) || empty($this->_images))
            return false;

        $data = pack('vvv', 0, 1, count($this->_images));
        $pixel_data = '';

        $icon_dir_entry_size = 16;

        $offset = 6 + ($icon_dir_entry_size * count($this->_images));

        foreach ($this->_images as $image) {
            $data .= pack('CCCCvvVV', $image['width'], $image['height'], $image['color_palette_colors'], 0, 1, $image['bits_per_pixel'], $image['size'], $offset);
            $pixel_data .= $image['data'];

            $offset += $image['size'];
        }

        $data .= $pixel_data;
        unset($pixel_data);

        return $data;
    }

    /**
     * Take a GD image resource and change it into a raw BMP format.
     *
     * @access private
     * @param $im
     */
    function createDataForFileIco($im)
    {
        $width = imagesx($im);
        $height = imagesy($im);

        $pixel_data = array();

        $opacity_data = array();
        $current_opacity_val = 0;

        for ($y = $height - 1; $y >= 0; $y--) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorat($im, $x, $y);

                $alpha = ($color & 0x7F000000) >> 24;
                $alpha = (1 - ($alpha / 127)) * 255;

                $color &= 0xFFFFFF;
                $color |= 0xFF000000 & ($alpha << 24);

                $pixel_data[] = $color;

                $opacity = ($alpha <= 127) ? 1 : 0;

                $current_opacity_val = ($current_opacity_val << 1) | $opacity;

                if ((($x + 1) % 32) == 0) {
                    $opacity_data[] = $current_opacity_val;
                    $current_opacity_val = 0;
                }
            }

            if (($x % 32) > 0) {
                while (($x++ % 32) > 0)
                    $current_opacity_val = $current_opacity_val << 1;

                $opacity_data[] = $current_opacity_val;
                $current_opacity_val = 0;
            }
        }

        $image_header_size = 40;
        $color_mask_size = $width * $height * 4;
        $opacity_mask_size = (ceil($width / 32) * 4) * $height;

        $data = pack('VVVvvVVVVVV', 40, $width, ($height * 2), 1, 32, 0, 0, 0, 0, 0, 0);

        foreach ($pixel_data as $color)
            $data .= pack('V', $color);

        foreach ($opacity_data as $opacity)
            $data .= pack('N', $opacity);

        $image = array(
            'width' => $width,
            'height' => $height,
            'color_palette_colors' => 0,
            'bits_per_pixel' => 32,
            'size' => $image_header_size + $color_mask_size + $opacity_mask_size,
            'data' => $data,
        );
        $this->_images[] = $image;
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
        $generics = array();
        $android = array();
        $ios = array();
        $windows = array();

        foreach ($this->sizes as $size) {
            switch ($size) {
                case 196:
                    $android[] = $size;
                    break;
                case 120:
                case 152:
                case 180:
                    $ios[] = $size;
                    break;
                case 144:
                    $windows[] = $size;
                    break;
                default:
                    $generics[] = $size;
                    break;
            }
        }

        echo htmlspecialchars("<!-- default -->") . "</br>";
        echo htmlspecialchars('<link rel="icon" href="/favicon/' . $this->filename . '" sizes="' . $this->maxIcoFileSize . 'x' . $this->maxIcoFileSize . '">') . "</br>";
        echo "</br>";

        echo htmlspecialchars("<!-- generics -->") . "</br>";
        foreach ($generics as $size) {
            echo htmlspecialchars('<link rel="icon" href="/favicon/' . $size . '/' . $this->getFileNamePng($size) . '" sizes="' . $size . 'x' . $size . '">') . "</br>";
        }
        echo "</br>";

        echo htmlspecialchars("<!-- Android -->") . "</br>";
        foreach ($android as $size) {
            echo htmlspecialchars('<link rel="shortcut icon" sizes="' . $size . 'x' . $size . '" href="/favicon/' . $size . '/' . $this->getFileNamePng($size) . '">') . "</br>";
        }
        echo "</br>";

        echo htmlspecialchars("<!-- iOS -->") . "</br>";
        foreach ($ios as $size) {
            echo htmlspecialchars('<link rel="apple-touch-icon" href="/favicon/' . $size . '/' . $this->getFileNamePng($size) . '" sizes="' . $size . 'x' . $size . '">') . "</br>";
        }
        echo "</br>";

        echo htmlspecialchars("<!-- Windows 8 IE 10 -->") . "</br>";
        echo htmlspecialchars('<meta name="msapplication-TileColor" content="#FFFFFF">') . "</br>";
        foreach ($windows as $size) {
            echo htmlspecialchars('<meta name="msapplication-TileImage" content="/favicon/' . $size . '/' . $this->getFileNamePng($size) . '">') . "</br>";
        }
        echo "</br>";

        echo '---- FINISH ----------------------------------------------------------' . "</br>";
    }
}