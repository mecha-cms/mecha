<?php

class Navigator {

    protected static $bucket = array();

    protected static $navigator = array(
        'step' => 5
    );

    /**
     * ============================================================================
     *  PAGINATION EXTRACTOR FOR LIST OF FILES
     * ============================================================================
     *
     * -- CODE: -------------------------------------------------------------------
     *
     *    $pager = Navigator::extract(glob('some/files/*.txt'), 1, 5, 'foo/bar');
     *    echo $pager->prev->link;
     *
     * ----------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type    | Description
     *  ---------- | ------- | ----------------------------------------------------
     *  $pages     | array   | Array of files to be paginated
     *  $current   | integer | The current page offset
     *  $current   | string  | The current page path
     *  $perpage   | integer | Number of files to show per page request
     *  $connector | string  | Extra path to be inserted into URL
     *  ---------- | ------- | ----------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function extract($pages = array(), $current = 1, $perpage = 10, $connector = '/') {

        // Set default next, previous and step data
        self::$bucket = array('prev' => false, 'next' => false, 'step' => false);

        $config = Config::get();
        $speak = Config::speak();
        $base = $config->url;
        $total = count($pages);

        if($connector != '/' && ! empty($connector)) $connector = '/' . trim($connector, '/') . '/';

        if(is_numeric($current)) {

            $current = (int) $current;

            // Generate next/previous URL for index page
            self::$bucket['prev']['url'] = $current > 1 ? $base . $connector . ($current - 1) : $base;
            self::$bucket['next']['url'] = $current < ceil($total / $perpage) ? $base . $connector . ($current + 1) : $base;

            // Generate next/previous text for index page
            self::$bucket['prev']['text'] = $current > 1 ? $speak->newer : $speak->home;
            self::$bucket['next']['text'] = $current < ceil($total / $perpage) ? $speak->older : $speak->home;

            // Generate next/previous link for index page
            self::$bucket['prev']['link'] = (self::$bucket['prev']['url'] != $base) ? '<a href="' . self::$bucket['prev']['url'] . '" rel="prev">' . self::$bucket['prev']['text'] . '</a>' : "";
            self::$bucket['next']['link'] = (self::$bucket['next']['url'] != $base) ? '<a href="' . self::$bucket['next']['url'] . '" rel="next">' . self::$bucket['next']['text'] . '</a>' : "";

            // Generate pagination links for index page
            $html = '<span class="pagination">';
            $chunk = ceil($total / $perpage);
            $step = $chunk > self::$navigator['step'] ? self::$navigator['step'] : $chunk;
            $left = $current - $step;
            if($left < 1) $left = 1;
            if($current > 1) {
                $html .= '<a href="' . $config->url . $connector . '1">' . $speak->first . '</a>';
                $html .= '<a href="' . $config->url . $connector . ($current - 1) . '">' . $speak->prev . '</a>';
            }
            for($i = $current - $step + 1; $i < $current + $step; ++$i) {
                if($chunk > 1) {
                    if($i - 1 < $chunk && ($i > 0 && $i + 1 > $current - $left - round($chunk / 2))) {
                        $html .= $i != $current ? '<a href="' . $config->url . $connector . $i . '">' . $i . '</a>' : '<strong class="current">' . $i . '</strong>';
                        self::$bucket['step']['url'][] = $i != $current ? $config->url . $connector . $i : false;
                    }
                }
            }
            if($current < $chunk) {
                $html .= '<a href="' . $config->url . $connector . ($current + 1) . '">' . $speak->next . '</a>';
                $html .= '<a href="' . $config->url . $connector . $chunk . '">' . $speak->last . '</a>';
            }

            self::$bucket['step']['link'] = $html . '</span>';

        }

        if(is_string($current)) {

            for($i = 0; $i < $total; ++$i) {

                if($pages[$i] == $current) {

                    // Generate next/previous URL for single page
                    self::$bucket['prev']['url'] = isset($pages[$i - 1]) ? $base . $connector . self::slug($pages[$i - 1]) : $base;
                    self::$bucket['next']['url'] = isset($pages[$i + 1]) ? $base . $connector . self::slug($pages[$i + 1]) : $base;

                    // Generate next/previous text for single page
                    self::$bucket['prev']['text'] = isset($pages[$i - 1]) ? $speak->newer : $speak->home;
                    self::$bucket['next']['text'] = isset($pages[$i + 1]) ? $speak->older : $speak->home;

                    // Generate next/previous link for single page
                    self::$bucket['prev']['link'] = (self::$bucket['prev']['url'] != $base) ? '<a href="' . self::$bucket['prev']['url'] . '" rel="prev">' . self::$bucket['prev']['text'] . '</a>' : "";
                    self::$bucket['next']['link'] = (self::$bucket['next']['url'] != $base) ? '<a href="' . self::$bucket['next']['url'] . '" rel="next">' . self::$bucket['next']['text'] . '</a>' : "";

                    break;

                }

            }

        }

        return Mecha::O(self::$bucket);

    }

    private static function slug($input) {
        $extension = pathinfo($input, PATHINFO_EXTENSION);
        $base = basename($input, '.' . $extension);
        $parts = explode('_', $base);
        return isset($parts[2]) ? $parts[2] : $base . '.' . $extension;
    }

    public static function configure($key, $value = "") {
        if(is_array($key)) {
            self::$navigator = array_merge(self::$navigator, $key);
        } else {
            self::$navigator[$key] = $value;
        }
        return new static;
    }

}