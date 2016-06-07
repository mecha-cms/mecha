<?php

class Image extends __ {

    public $open = null;
    public $origin = null;
    public $placeholder = null;

    public $GD = false;

    public static $config = array(
        'placeholder' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
    );

    public function gen($file = null) {
        if(is_null($file)) {
            if( ! file_exists($this->placeholder)) {
                File::open($this->origin)->copyTo($this->placeholder);
            }
            $file = $this->placeholder;
        }
        switch(File::E($file)) {
            case 'gif': $this->GD = imagecreatefromgif($file); break;
            case 'jpeg': $this->GD = imagecreatefromjpeg($file); break;
            case 'jpg': $this->GD = imagecreatefromjpeg($file); break;
            case 'png': $this->GD = imagecreatefrompng($file); break;
        }
        return $this;
    }

    public function twin($resource = null, $e = null) {
        $file = $this->placeholder;
        if(is_null($resource)) $resource = $this->GD;
        $o_e = File::E($this->origin);
        $n_e = File::E($file);
        if( ! is_null($e)) {
            $file = preg_replace('#\.([a-z]+)$#i', '.' . $e, $file);
            File::open($this->placeholder)->delete();
            $this->placeholder = $file;
            $n_e = $e;
        }
        switch($n_e) {
            case 'gif': imagegif($resource, $file); break;
            case 'jpeg': imagejpeg($resource, $file, 100); break;
            case 'jpg': imagejpeg($resource, $file, 100); break;
            case 'png': imagepng($resource, $file); break;
        }
        return $this;
    }

    public function __construct($files, $fallback = false) {
        if( ! extension_loaded('gd')) {
            if(is_null($fallback)) {
                Guardian::abort('<a href="http://www.php.net/manual/en/book.image.php" title="PHP &ndash; Image Processing and GD" rel="nofollow" target="_blank">PHP GD</a> extension is not installed on your web server.');
            }
            return $fallback;
        }
        if(is_array($files)) {
            $this->open = array();
            foreach($files as $file) {
                $this->open[] = File::path($file);
            }
        } else {
            $this->open = File::path($files);
        }
        $file = is_array($this->open) ? $this->open[0] : $this->open;
        $this->origin = $file;
        $this->placeholder = File::D($file) . DS . '__' . File::B($file);
        return $this;
    }

    public static function take($files) {
        return new Image($files);
    }

    // Generate a 1 x 1 pixel transparent image or a random image URL output from array
    public static function placeholder($url = null) {
        if(is_array($url)) {
            return Mecha::eat($url)->shake()->get(0);
        }
        return self::$config['placeholder'];
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

    public function saveTo($destination) {
        if(is_dir($destination)) {
            $destination .= DS . File::B($this->origin);
        }
        $o_e = File::E($this->origin);
        $n_e = File::E($destination);
        if($o_e !== $n_e || ! file_exists($this->placeholder)) {
            $this->gen()->twin(null, $n_e);
        }
        File::open($this->placeholder)->moveTo($destination);
        imagedestroy($this->GD);
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

    public function saveAs($name = 'image-%d.png') {
        return $this->saveTo(File::D($this->origin) . DS . sprintf($name, time()));
    }

    // Save anyway ...
    public function save() {
        return $this->saveTo($this->origin);
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

    public function draw($save = false) {
        $this->gen();
        $image = file_get_contents($this->placeholder);
        if($save !== false) {
            $save = File::path($save);
            File::write($image)->saveTo($save);
        }
        header('Content-Type: ' . $this->inspect('mime'));
        File::open($this->placeholder)->delete();
        imagedestroy($this->GD);
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
     *    var_dump(Image::take('photo.jpg')->inspect());
     *
     * --------------------------------------------------------------------
     *
     *    var_dump(Image::take(array('a.jpg', 'b.jpg'))->inspect());
     *
     * --------------------------------------------------------------------
     *
     *    echo Image::take('photo.jpg')->inspect('width', 0);
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

    public function inspect($key = null, $fallback = false) {
        if(is_array($this->open)) {
            $results = array();
            foreach($this->open as $file) {
                $data = getimagesize($file);
                $results[] = array_merge(File::inspect($file), array(
                    'width' => $data[0],
                    'height' => $data[1],
                    'bits' => $data['bits'],
                    'mime' => $data['mime']
                ));
            }
            return $results;
        } else {
            $data = getimagesize($this->open);
            $results = array_merge(File::inspect($this->open), array(
                'width' => $data[0],
                'height' => $data[1],
                'bits' => $data['bits'],
                'mime' => $data['mime']
            ));
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

    public function resize($max_width = 100, $max_height = null, $proportional = true, $crop = false) {
        $this->gen();
        if(is_null($max_height)) {
            $max_height = $max_width;
        }
        $info = $this->inspect();
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
                return $this->twin();
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
        imagecopyresampled($pallete, $this->GD, 0, 0, $x, $y, $new_width, $new_height, $old_width, $old_height);
        $this->twin($pallete);
        imagedestroy($pallete);
        return $this;
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
     *    // Crop without resize (need X and Y coordinate)
     *    Image::take('photo.jpg')
     *         ->crop(4, 4, 200, 200)
     *         ->saveAs('cropped-photo.jpg');
     *
     * --------------------------------------------------------------------
     *
     */

    public function crop($x = 72, $y = null, $width = null, $height = null) {
        if(is_null($width)) {
            if(is_null($y)) $y = $x;
            return $this->resize($x, $y, true, true);
        }
        if(is_null($height)) $height = $width;
        $this->gen();
        $pallete = imagecreatetruecolor($width, $height);
        imagecopy($pallete, $this->GD, 0, 0, $x, $y, $width, $height);
        $this->twin($pallete);
        imagedestroy($pallete);
        return $this;
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

    public function brightness($level = 0) {
        $this->gen();
        // -255 = min brightness, 0 = no change, +255 = max brightness
        imagefilter($this->GD, IMG_FILTER_BRIGHTNESS, $level);
        return $this->twin();
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

    public function contrast($level = 0) {
        $this->gen();
        // -100 = max contrast, 0 = no change, +100 = min contrast (it's inverted)
        imagefilter($this->GD, IMG_FILTER_CONTRAST, $level * -1);
        return $this->twin();
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

    public function colorize($r = 255, $g = 255, $b = 255, $a = 1) {
        $this->gen();
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
        imagefilter($this->GD, IMG_FILTER_COLORIZE, $r, $g, $b, $a);
        return $this->twin();
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

    public function grayscale() {
        $this->gen();
        imagefilter($this->GD, IMG_FILTER_GRAYSCALE);
        return $this->twin();
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

    public function negate() {
        $this->gen();
        imagefilter($this->GD, IMG_FILTER_NEGATE);
        return $this->twin();
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

    public function emboss($level = 1) {
        $level = round($level);
        for($i = 0; $i < $level; ++$i) {
            $this->gen();
            imagefilter($this->GD, IMG_FILTER_EMBOSS);
            $this->twin();
        }
        return $this;
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

    public function blur($level = 1) {
        $level = round($level);
        for($i = 0; $i < $level; ++$i) {
            $this->gen();
            imagefilter($this->GD, IMG_FILTER_GAUSSIAN_BLUR);
            $this->twin();
        }
        return $this;
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

    public function sharpen($level = 1) {
        $level = round($level);
        $matrix = array(
            array(-1, -1, -1),
            array(-1, 16, -1),
            array(-1, -1, -1),
        );
        $divisor = array_sum(array_map('array_sum', $matrix));
        for($i = 0; $i < $level; ++$i) {
            $this->gen();
            imageconvolution($this->GD, $matrix, $divisor, 0);
            $this->twin();
        }
        return $this;
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

    public function pixelate($level = 1, $advanced_pixelation_effect = false) {
        $this->gen();
        imagefilter($this->GD, IMG_FILTER_PIXELATE, $level, $advanced_pixelation_effect);
        return $this->twin();
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

    public function rotate($angle = 0, $bg = false, $alpha_for_hex = 1) {
        $this->gen();
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
        $bg = imagecolorallocatealpha($this->GD, $r, $g, $b, $a);
        imagealphablending($this->GD, false);
        imagesavealpha($this->GD, true);
        // The angle value in `imagerotate` function is also inverted
        $rotated = imagerotate($this->GD, (floor($angle) * -1), $bg, 0);
        imagealphablending($rotated, false);
        imagesavealpha($rotated, true);
        $this->twin($rotated);
        imagedestroy($rotated);
        return $this;
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

    public function flip($direction = 'horizontal') {
        $this->gen();
        // Function `imageflip` only available in PHP 5 >= 5.5.0
        // Fallback to a simple horizontal image flipper if `imageflip` is not available
        if(function_exists('imageflip')) {
            switch(strtolower($direction[0])) {
                // `horizontal`, `vertical` or `both` ?
                case 'h': imageflip($this->GD, IMG_FLIP_HORIZONTAL); break;
                case 'v': imageflip($this->GD, IMG_FLIP_VERTICAL); break;
                case 'b': imageflip($this->GD, IMG_FLIP_BOTH); break;
            }
        } else {
            $width = imagesx($this->GD);
            $height = imagesy($this->GD);
            $x = 0;
            $y = 0;
            $pallete = imagecreatetruecolor(1, $height);
            $x_2 = $x + $width - 1;
            for($i = (int) floor(($width - 1) / 2); $i >= 0; --$i) {
                imagecopy($pallete, $this->GD, 0, 0, $x_2 - $i, $y, 1, $height);
                imagecopy($this->GD, $this->GD, $x_2 - $i, $y, $x + $i, $y, 1, $height);
                imagecopy($this->GD, $pallete, $x + $i, $y, 0, 0, 1, $height);
            }
            imagedestroy($pallete);
        }
        return $this->twin();
    }

    /**
     * ====================================================================
     *  COMBINE MULTIPLE IMAGE FILE(S) INTO A SINGLE IMAGE (SPRITE)
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

    public function merge($gap = 0, $orientation = 'vertical', $bg = false, $alpha_for_hex = 1) {
        $bucket = array();
        $width = 0;
        $height = 0;
        $max_width = array();
        $max_height = array();
        $orientation = strtolower($orientation);
        $this->open = (array) $this->open;
        foreach($this->inspect() as $info) {
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
        if($orientation[0] === 'v') {
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
        for($i = 0, $count = count($this->open); $i < $count; ++$i) {
            $this->gen($this->open[$i]);
            imagealphablending($this->GD, false);
            imagesavealpha($this->GD, true);
            imagecopyresampled($pallete, $this->GD, $start_width_from, $start_height_from, 0, 0, $bucket[$i]['width'], $bucket[$i]['height'], $bucket[$i]['width'], $bucket[$i]['height']);
            $start_width_from += $orientation[0] === 'h' ? $bucket[$i]['width'] + $gap : 0;
            $start_height_from += $orientation[0] === 'v' ? $bucket[$i]['height'] + $gap : 0;
        }
        return $this->twin($pallete, 'png');
    }

}