<?php

class Image {

    private static $o = array();

    private static $open = null;
    private static $original = null;
    private static $placeholder = null;
    private static $GD = false;

    public static function take($files) {
        if( ! extension_loaded('gd')) {
            Guardian::abort('<a href="http://www.php.net/manual/en/book.image.php" title="PHP &ndash; Image Processing and GD" rel="nofollow" target="_blank">PHP GD</a> extension is not installed on your web server.');
        }
        if(is_array($files)) {
            self::$open = array();
            foreach($files as $file) {
                self::$open[] = File::path($file);
            }
        } else {
            self::$open = File::path($files);
        }
        $file = is_array(self::$open) ? self::$open[0] : self::$open;
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        self::$placeholder = dirname($file) . DS . '__p-l-a-c-e-h-o-l-d-e-r--i-m-a-g-e.' . $extension;
        self::$original = basename($file);
        File::open(self::$placeholder)->delete();
        File::open($file)->copyTo(self::$placeholder);
        self::gen($file);
        return new static;
    }

    // Generate a 1 x 1 pixel transparent image
    // or a random image URL output from array
    public static function placeholder($url = null) {
        if(is_array($url)) {
            return Mecha::eat($url)->shake()->get(0);
        }
        return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
    }

    private static function gen($file = null) {
        if(is_null($file)) $file = self::$placeholder;
        switch(strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            case 'gif': self::$GD = imagecreatefromgif($file); break;
            case 'jpg': self::$GD = imagecreatefromjpeg($file); break;
            case 'jpeg': self::$GD = imagecreatefromjpeg($file); break;
            case 'png': self::$GD = imagecreatefrompng($file); break;
        }
    }

    private static function twin($resource = null, $extension = null) {
        $file = self::$placeholder;
        if(is_null($resource)) $resource = self::$GD;
        $old_extension = strtolower(pathinfo(self::$original, PATHINFO_EXTENSION));
        $new_extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if( ! is_null($extension)) {
            $file = preg_replace('#\.([a-z]+)$#i', '.' . $extension, $file);
            File::open(self::$placeholder)->delete();
            self::$placeholder = $file;
            $new_extension = $extension;
        }
        switch($new_extension) {
            case 'gif': imagegif($resource, $file); break;
            case 'jpg': imagejpeg($resource, $file, 100); break;
            case 'jpeg': imagejpeg($resource, $file, 100); break;
            case 'png': imagepng($resource, $file); break;
        }
    }

    /**
     * ====================================================================
     *  SAVE THE IMAGE TO ANOTHER PLACE
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter    | Type   | Description
     *  ------------ | ------ | -------------------------------------------
     *  $destination | string | Path to image file or directory
     *  ------------ | ------ | -------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function saveTo($destination) {
        if(is_dir($destination)) {
            $destination .= DS . basename(self::$original);
        }
        $old_extension = strtolower(pathinfo(self::$original, PATHINFO_EXTENSION));
        $new_extension = strtolower(pathinfo($destination, PATHINFO_EXTENSION));
        if($old_extension != $new_extension) {
            self::gen();
            self::twin(null, $new_extension);
        }
        File::open(self::$placeholder)->moveTo($destination);
        imagedestroy(self::$GD);
    }

    /**
     * ====================================================================
     *  SAVE IMAGE TO THE CURRENT DIRECTORY
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------
     *  $name     | string | New name for the image
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function saveAs($name = 'image.jpg') {
        return self::saveTo(dirname(self::$placeholder) . DS . $name);
    }

    /**
     * ====================================================================
     *  OUTPUT THE MANIPULATED IMAGE INTO BROWSER
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    Image::take('photo.jpg')->draw();
     *
     * --------------------------------------------------------------------
     *
     *    Image::take('photo.jpg')->draw('path/to/saved-image.jpg');
     *
     * --------------------------------------------------------------------
     *
     */

    public static function draw($save = false) {
        $image = file_get_contents(self::$placeholder);
        if($save !== false) {
            $save = File::path($save);
            File::write($image)->saveTo($save);
        }
        header('Content-Type: ' . self::getInfo('mime'));
        File::open(self::$placeholder)->delete();
        imagedestroy(self::$GD);
        echo $image;
        exit;
    }

    /**
     * ====================================================================
     *  GET IMAGE INFO
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    var_dump(Image::take('photo.jpg')->getInfo());
     *
     * --------------------------------------------------------------------
     *
     *    var_dump(Image::take(array('a.jpg', 'b.jpg'))->getInfo());
     *
     * --------------------------------------------------------------------
     *
     *    echo Image::take('photo.jpg')->getInfo('width', 0);
     *
     * --------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type  | Description
     *  --------- | ----- | -----------------------------------------------
     *  $key      | mixed | Key of the resulted array data
     *  $fallback | mixed | Fallback value if data does not exist
     *  --------- | ----- | -----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function getInfo($key = null, $fallback = false) {
        File::open(self::$placeholder)->delete();
        if(is_array(self::$open)) {
            $results = array();
            foreach(self::$open as $file) {
                $data = getimagesize($file);
                $results[] = array(
                    'width' => $data[0],
                    'height' => $data[1],
                    'size_raw' => filesize($file),
                    'size' => File::size($file),
                    'bits' => $data['bits'],
                    'mime' => $data['mime']
                );
            }
            return $results;
        } else {
            $data = getimagesize(self::$open);
            $results = array(
                'width' => $data[0],
                'height' => $data[1],
                'size_raw' => filesize(self::$open),
                'size' => File::size(self::$open),
                'bits' => $data['bits'],
                'mime' => $data['mime']
            );
            if( ! is_null($key)) {
                return isset($results[$key]) ? $results[$key] : $fallback;
            }
            return $results;
        }
        return false;
    }

    /**
     * ====================================================================
     *  RESIZE AN IMAGE PROPORTIONALLY
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->resize(200, 150)
     *         ->saveAs('resized-photo.jpg');
     *
     * --------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter     | Type    | Description
     *  ------------- | ------- | -----------------------------------------
     *  $max_width    | integer | Width of the new image
     *  $max_height   | integer | Height of the new image
     *  $proportional | boolean | Set width and height proportionally?
     *  ------------- | ------- | -----------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function resize($max_width = 100, $max_height = null, $proportional = true, $crop = false) {
        self::gen();
        if(is_null($max_height)) {
            $max_height = $max_width;
        }
        $info = self::getInfo();
        $old_width = $info['width'];
        $old_height = $info['height'];
        $new_width = $max_width;
        $new_height = $max_height;
        $x = 0;
        $y = 0;
        $current_ratio = round($old_width / $old_height, 2);
        $desired_ratio_after = round($max_width / $max_height, 2);
        $desired_ratio_before = round($max_height / $max_width, 2);
        if($proportional) {
            // Don't do anything if the new image size is bigger than the original image size
            if($old_width < $max_width && $old_height < $max_height) {
                self::twin();
                return new static;
            }
            if($crop) {
                // Wider than the thumbnail (in aspect ratio sense)
                if($current_ratio > $desired_ratio_after) {
                    $new_width = $old_width * $max_height / $old_height;
                // Wider than the image
                } else {
                    $new_height = $old_height * $max_width / $old_width;
                }
                // Calculate where to crop based on the center of the image
                $width_ratio = $old_width / $new_width;
                $height_ratio = $old_height / $new_height;
                $x = floor((($new_width - $max_width) / 2) * $width_ratio);
                $y = round((($new_height - $max_height) / 2) * $height_ratio);
                $pallete = imagecreatetruecolor($max_width, $max_height);
            } else {
                if($old_width > $old_height) {
                    $ratio = max($old_width, $old_height) / max($max_width, $max_height);
                } else {
                    $ratio = max($old_width, $old_height) / min($max_width, $max_height);
                }
                $new_width = $old_width / $ratio;
                $new_height = $old_height / $ratio;
                $pallete = imagecreatetruecolor($new_width, $new_height);
            }
        } else {
            $pallete = imagecreatetruecolor($max_width, $max_height);
        }
        // Draw ...
        imagealphablending($pallete, false);
        imagesavealpha($pallete, true);
        imagecopyresampled($pallete, self::$GD, 0, 0, $x, $y, $new_width, $new_height, $old_width, $old_height);
        self::twin($pallete);
        imagedestroy($pallete);
        return new static;
    }

    /**
     * ====================================================================
     *  CROP AN IMAGE
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    // Crop and resize (centered)
     *    Image::take('photo.jpg')
     *         ->crop(200, 200)
     *         ->saveAs('cropped-photo.jpg');
     *
     * --------------------------------------------------------------------
     *
     *    // Crop without resize (need X and Y coordinates)
     *    Image::take('photo.jpg')
     *         ->crop(4, 4, 200, 200)
     *         ->saveAs('cropped-photo.jpg');
     *
     * --------------------------------------------------------------------
     *
     */

    public static function crop($x = 72, $y = null, $width = null, $height = null) {
        if(is_null($width)) {
            if(is_null($y)) $y = $x;
            return self::resize($x, $y, true, true);
        }
        if(is_null($height)) $height = $width;
        self::gen();
        $pallete = imagecreatetruecolor($width, $height);
        imagecopy($pallete, self::$GD, 0, 0, $x, $y, $width, $height);
        self::twin($pallete);
        imagedestroy($pallete);
        return new static;
    }

    /**
     * ====================================================================
     *  BRIGHTEN AN IMAGE
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->brigtness(10)
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     */

    public static function brightness($level = 0) {
        self::gen();
        // -255 = min brightness, 0 = no change, +255 = max brightness
        imagefilter(self::$GD, IMG_FILTER_BRIGHTNESS, $level);
        self::twin();
        return new static;
    }

    /**
     * ====================================================================
     *  CONTRASTING AN IMAGE
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->contrast(10)
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     */

    public static function contrast($level = 0) {
        self::gen();
        // -100 = max contrast, 0 = no change, +100 = min contrast (it's inverted)
        imagefilter(self::$GD, IMG_FILTER_CONTRAST, $level * -1);
        self::twin();
        return new static;
    }

    /**
     * ====================================================================
     *  COLORIZE AN IMAGE
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->colorize(255, 255, 255, .8)
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->colorize(array(255, 255, 255, .8))
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->colorize('rgba(255, 255, 255, .8)')
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->colorize('#FFFFFF', .8)
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     */

    public static function colorize($r = 255, $g = 255, $b = 255, $a = 1) {
        self::gen();
        // For red, green and blue: -255 = min, 0 = no change, +255 = max
        if(is_array($r)) {
            if(count($r) === 3) {
                $r[] = 1; // missing alpha channel
            }
            list($r, $g, $b, $a) = array_values($r);
        } else {
            $bg = (string) $r;
            if($bg[0] === '#' && $color = Converter::HEX2RGB($r)) {
                $a = $g;
                $r = $color['r'];
                $g = $color['g'];
                $b = $color['b'];
            } else if($color = Converter::RGB($r)) {
                $r = $color['r'];
                $g = $color['g'];
                $b = $color['b'];
                $a = $color['a'];
            }
        }
        // For alpha: 127 = transparent, 0 = opaque
        $a = 127 - ($a * 127);
        imagefilter(self::$GD, IMG_FILTER_COLORIZE, $r, $g, $b, $a);
        self::twin();
        return new static;
    }

    /**
     * ====================================================================
     *  GRAYSCALE AN IMAGE
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->grayscale()
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     */

    public static function grayscale() {
        self::gen();
        imagefilter(self::$GD, IMG_FILTER_GRAYSCALE);
        self::twin();
        return new static;
    }

    /**
     * ====================================================================
     *  NEGATIVE IMAGE
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->negate()
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     */

    public static function negate() {
        self::gen();
        imagefilter(self::$GD, IMG_FILTER_NEGATE);
        self::twin();
        return new static;
    }

    /**
     * ====================================================================
     *  EMBOSSING AN IMAGE
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->emboss(5)
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     */

    public static function emboss($level = 1) {
        $level = round($level);
        for($i = 0; $i < $level; ++$i) {
            self::gen();
            imagefilter(self::$GD, IMG_FILTER_EMBOSS);
            self::twin();
        }
        return new static;
    }

    /**
     * ====================================================================
     *  BLURRING AN IMAGE
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->blur(5)
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     */

    public static function blur($level = 1) {
        $level = round($level);
        for($i = 0; $i < $level; ++$i) {
            self::gen();
            imagefilter(self::$GD, IMG_FILTER_GAUSSIAN_BLUR);
            self::twin();
        }
        return new static;
    }

    /**
     * ====================================================================
     *  SHARPEN AN IMAGE
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->sharpen()
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     */

    public static function sharpen($level = 1) {
        $level = round($level);
        $matrix = array(
            array(-1, -1, -1),
            array(-1, 16, -1),
            array(-1, -1, -1),
        );
        $divisor = array_sum(array_map('array_sum', $matrix));
        for($i = 0; $i < $level; ++$i) {
            self::gen();
            imageconvolution(self::$GD, $matrix, $divisor, 0);
            self::twin();
        }
        return new static;
    }

    /**
     * ====================================================================
     *  PIXELATE AN IMAGE
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->pixelate(5)
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->pixelate(5, true)
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     */

    public static function pixelate($level = 1, $advanced_pixelation_effect = false) {
        self::gen();
        imagefilter(self::$GD, IMG_FILTER_PIXELATE, $level, $advanced_pixelation_effect);
        self::twin();
        return new static;
    }

    /**
     * ====================================================================
     *  ROTATE AN IMAGE
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->rotate(90)
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     */

    public static function rotate($angle = 0, $bg = false, $alpha_for_hex = 1) {
        self::gen();
        if( ! $bg) {
            $bg = array(0, 0, 0, 0); // transparent
        }
        if(is_array($bg)) {
            if(count($bg) === 3) {
                $bg[] = 1; // missing alpha channel
            }
            list($r, $g, $b, $a) = array_values($bg);
        } else {
            $bg = (string) $bg;
            if($bg[0] === '#' && $color = Converter::HEX2RGB($bg)) {
                $r = $color['r'];
                $g = $color['g'];
                $b = $color['b'];
                $a = $alpha_for_hex;
            } else if($color = Converter::RGB($bg)) {
                $r = $color['r'];
                $g = $color['g'];
                $b = $color['b'];
                $a = $color['a'];
            }
        }
        $a = 127 - ($a * 127);
        $bg = imagecolorallocatealpha(self::$GD, $r, $g, $b, $a);
        imagealphablending(self::$GD, false);
        imagesavealpha(self::$GD, true);
        // The angle value in `imagerotate` function is also inverted
        $rotated = imagerotate(self::$GD, (floor($angle) * -1), $bg, 0);
        imagealphablending($rotated, false);
        imagesavealpha($rotated, true);
        self::twin($rotated);
        imagedestroy($rotated);
        return new static;
    }

    /**
     * ====================================================================
     *  FLIP AN IMAGE
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    Image::take('photo.jpg')
     *         ->flip('vertical')
     *         ->saveAs('photo-2.jpg');
     *
     * --------------------------------------------------------------------
     *
     */

    public static function flip($direction = 'horizontal') {
        self::gen();
        // Function `imageflip` only available in PHP 5 >= 5.5.0
        // Fallback to a simple horizontal image flipper if `imageflip` is not available
        if(function_exists('imageflip')) {
            switch(strtolower($direction[0])) {
                // `horizontal`, `vertical` or `both` ?
                case 'h': imageflip(self::$GD, IMG_FLIP_HORIZONTAL); break;
                case 'v': imageflip(self::$GD, IMG_FLIP_VERTICAL); break;
                case 'b': imageflip(self::$GD, IMG_FLIP_BOTH); break;
            }
        } else {
            $width = imagesx(self::$GD);
            $height = imagesy(self::$GD);
            $x = 0;
            $y = 0;
            $pallete = imagecreatetruecolor(1, $height);
            $x_2 = $x + $width - 1;
            for($i = (int) floor(($width - 1) / 2); $i >= 0; --$i) {
                imagecopy($pallete, self::$GD, 0, 0, $x_2 - $i, $y, 1, $height);
                imagecopy(self::$GD, self::$GD, $x_2 - $i, $y, $x + $i, $y, 1, $height);
                imagecopy(self::$GD, $pallete, $x + $i, $y, 0, 0, 1, $height);
            }
            imagedestroy($pallete);
        }
        self::twin();
        return new static;
    }

    /**
     * ====================================================================
     *  COMBINE MULTIPLE IMAGE FILES INTO A SINGLE IMAGE (SPRITES)
     * ====================================================================
     *
     * -- CODE: -----------------------------------------------------------
     *
     *    Image::take(array(
     *        'icon-1.png',
     *        'icon-2.png',
     *        'icon-3.png'
     *    ))->merge(0, 'vertical')->saveAs('sprites.png');
     *
     * --------------------------------------------------------------------
     *
     */

    public static function merge($gap = 0, $orientation = 'vertical', $bg = false, $alpha_for_hex = 1) {
        $bucket = array();
        $width = 0;
        $height = 0;
        $max_width = array();
        $max_height = array();
        $orientation = strtolower($orientation);
        if( ! is_array(self::$open)) {
            self::$open = array(self::$open);
        }
        foreach(self::getInfo() as $info) {
            $bucket[] = array(
                'width' => $info['width'],
                'height' => $info['height']
            );
            $max_width[] = $info['width'];
            $max_height[] = $info['height'];
            $width += $info['width'] + $gap;
            $height += $info['height'] + $gap;
        }
        if( ! $bg) {
            $bg = array(0, 0, 0, 0); // transparent
        }
        if(is_array($bg)) {
            if(count($bg) === 3) {
                $bg[] = 1; // missing alpha channel
            }
            list($r, $g, $b, $a) = array_values($bg);
        } else {
            $bg = (string) $bg;
            if($bg[0] === '#' && $color = Converter::HEX2RGB($bg)) {
                $r = $color['r'];
                $g = $color['g'];
                $b = $color['b'];
                $a = $alpha_for_hex;
            } else if($color = Converter::RGB($bg)) {
                $r = $color['r'];
                $g = $color['g'];
                $b = $color['b'];
                $a = $color['a'];
            }
        }
        $a = 127 - ($a * 127);
        if($orientation[0] == 'v') {
            $pallete = imagecreatetruecolor(max($max_width), $height - $gap);
        } else {
            $pallete = imagecreatetruecolor($width - $gap, max($max_height));
        }
        $bg = imagecolorallocatealpha($pallete, $r, $g, $b, $a);
        imagefill($pallete, 0, 0, $bg);
        imagealphablending($pallete, true);
        imagesavealpha($pallete, true);
        $start_width_from = 0;
        $start_height_from = 0;
        for($i = 0, $count = count(self::$open); $i < $count; ++$i) {
            self::gen(self::$open[$i]);
            imagealphablending(self::$GD, false);
            imagesavealpha(self::$GD, true);
            imagecopyresampled($pallete, self::$GD, $start_width_from, $start_height_from, 0, 0, $bucket[$i]['width'], $bucket[$i]['height'], $bucket[$i]['width'], $bucket[$i]['height']);
            $start_width_from += $orientation[0] == 'h' ? $bucket[$i]['width'] + $gap : 0;
            $start_height_from += $orientation[0] == 'v' ? $bucket[$i]['height'] + $gap : 0;
        }
        self::twin($pallete, 'png');
        return new static;
    }

}