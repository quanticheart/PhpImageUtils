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
    public array $sizes = array(16, 32, 36, 48, 57, 64, 70, 72, 76, 96, 120, 128, 144, 150, 152, 180, 192, 196, 228, 310);
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

    function getFileName()
    {
        $newDir = explode(".", $this->filename);
        return $newDir[0];
    }

    function getFileNamePng($size)
    {
        $newDir = explode(".", $this->filename);
        return $newDir[0] . "-$size.png";
    }

    function getFileNamePngManifest($size)
    {
        $newDir = explode(".", $this->filename);
        return "\/images\/favicon\/$size\/" . $newDir[0] . "-$size.png";
    }

    function getFileNamePngXml($size)
    {
        $newDir = explode(".", $this->filename);
        return "/images/favicon/$size/" . $newDir[0] . "-$size.png";
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
            $this->path = dirname(__FILE__, 3) . "/output/images/favicon/";
            $this->createDir($this->path);
            $this->filename = $this->getFileNameIco($file);
            $status = $this->generateFavicon($file);
            if ($status) {
                $this->createManifestJson();
                $this->createMSXml();
                $this->printHtmlInsert();
            }
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
    function generateFavicon($file): bool
    {
        $im = $this->createIm($file);
        $src_w = imagesx($im);
        $src_h = imagesy($im);

        if ($src_w > 256 || $src_h > 256) {
            echo "ICO images cannot be larger than 256 pixels wide/tall in " . $this->filename . "<br>";
            return false;
        }

        foreach ($this->sizes as $size) {
            $width = $size;
            $height = $size;

            $new_im = imagecreatetruecolor($width, $height);

            imagecolortransparent($new_im, imagecolorallocatealpha($new_im, 0, 0, 0, 127));
            imagealphablending($new_im, false);
            imagesavealpha($new_im, true);


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

        return true;
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
        echo '----Default ----------------------------------------------------------' . "</br>";
        echo
            htmlspecialchars('<link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">') . "<br>" .
            htmlspecialchars('<link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">') . "<br>" .
            htmlspecialchars('<link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">') . "<br>" .
            htmlspecialchars('<link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">') . "<br>" .
            htmlspecialchars('<link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">') . "<br>" .
            htmlspecialchars('<link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">') . "<br>" .
            htmlspecialchars('<link rel="apple - touch - icon" sizes="144x144" href=" / apple - icon - 144x144 . png">') . "<br>" .
            htmlspecialchars('<link rel="apple - touch - icon" sizes="152x152" href=" / apple - icon - 152x152 . png">') . "<br>" .
            htmlspecialchars('<link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">') . "<br>" .
            htmlspecialchars('<link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">') . "<br>" .
            htmlspecialchars('<link rel="icon" type="image / png" sizes="32x32" href=" / favicon - 32x32 . png">') . "<br>" .
            htmlspecialchars('<link rel="icon" type="image / png" sizes="96x96" href=" / favicon - 96x96 . png">') . "<br>" .
            htmlspecialchars('<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">') . "<br>" .
            htmlspecialchars('<link rel="manifest" href="/manifest.json">') . "<br>" .
            htmlspecialchars('<meta name="msapplication - TileColor" content="#ffffff">') . "<br>" .
            htmlspecialchars('<meta name="msapplication-TileImage" content="/ms-icon-144x144.png">') . "<br>" .
            htmlspecialchars('<meta name="theme - color" content="#ffffff">') . "<br>";

        echo '----FINISH ----------------------------------------------------------' . "</br>";
    }

    function createManifestJson()
    {
        $this->createDir($this->path . "manifest/");
        $fp = fopen($this->path . "manifest/" . $this->getFileName() . "Manifest.json", "a");
        fwrite($fp, '{
    "name": "App",
 "icons": [
  {
      "src": "' . $this->getFileNamePngManifest("36") . '",
   "sizes": "36x36",
   "type": "image\/png",
   "density": "0.75"
  },
  {
      "src": "' . $this->getFileNamePngManifest("48") . '",
   "sizes": "48x48",
   "type": "image\/png",
   "density": "1.0"
  },
  {
      "src": "' . $this->getFileNamePngManifest("72") . '",
   "sizes": "72x72",
   "type": "image\/png",
   "density": "1.5"
  },
  {
      "src": "' . $this->getFileNamePngManifest("96") . '",
   "sizes": "96x96",
   "type": "image\/png",
   "density": "2.0"
  },
  {
      "src": "' . $this->getFileNamePngManifest("144") . '",
   "sizes": "144x144",
   "type": "image\/png",
   "density": "3.0"
  },
  {
      "src": "' . $this->getFileNamePngManifest("192") . '",
   "sizes": "192x192",
   "type": "image\/png",
   "density": "4.0"
  }
 ]
}');
        fclose($fp);
    }

    function createMSXml()
    {
        $this->createDir($this->path . "browserConfig/");
        $fp = fopen($this->path . "browserConfig/" . $this->getFileName() . "-browserconfig.xml", "a");
        fwrite($fp, '<?xml version = "1.0" encoding = "utf-8"?>
<browserconfig>
    <msapplication>
        <tile>
            <square70x70logo src="' . $this->getFileNamePngXml(" 70
            ") . '"/>
            <square150x150logo src="' . $this->getFileNamePngXml(" 150
            ") . '"/>
            <square310x310logo src="' . $this->getFileNamePngXml(" 310
            ") . '"/>
            <TileColor>#ffffff</TileColor>
        </tile>
    </msapplication>
</browserconfig>');
        fclose($fp);
    }
}

