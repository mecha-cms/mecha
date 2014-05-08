<?php

class Navigator {

    protected static $bucket = array();

    /**
     * ============================================================================
     *  PAGINATION EXTRACTOR FOR LIST OF FILES
     * ============================================================================
     *
     * -- CODE: -------------------------------------------------------------------
     *
     *    $pager = Navigator::extract(glob('some/files/*.txt'), 1, 5);
     *    echo $pager->prev->link;
     *
     * ----------------------------------------------------------------------------
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter  | Type    | Description
     *  ---------- | ------- | ----------------------------------------------------
     *  $pages     | array   | Array of files to be paginated
     *  $current   | integer | The current page offset
     *  $current   | string  | The current page slug
     *  $perpage   | integer | Number of files to show per page request
     *  $connector | string  | Extra path to be inserted into URL
     *  ---------- | ------- | ----------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function extract($pages = array(), $current = 1, $perpage = 10, $connector = '/') {

        // Set default next and previous data
        self::$bucket = array('prev' => false, 'next' => false);

        $config = Config::get();
        $speak = Config::speak();
        $base = $config->url;
        $total = count($pages);

        if($connector != '/' && ! empty($connector)) $connector = '/' . trim($connector, '/') . '/';

        if(is_numeric($current)) {

            $current = (int) $current;

            // Generate next/previous link for index page
            self::$bucket['prev']['url'] = $current > 1 ? $base . $connector . ($current - 1) : $base;
            self::$bucket['next']['url'] = $current < ceil($total / $perpage) ? $base . $connector . ($current + 1) : $base;

            // Generate next/previous text for index page
            self::$bucket['prev']['text'] = $current > 1 ? $speak->newer : $speak->home;
            self::$bucket['next']['text'] = $current < ceil($total / $perpage) ? $speak->older : $speak->home;

            // Generate next/previous link for index page
            self::$bucket['prev']['link'] = (self::$bucket['prev']['url'] != $base) ? '<a href="' . self::$bucket['prev']['url'] . '" rel="prev">' . self::$bucket['prev']['text'] . '</a>' : "";
            self::$bucket['next']['link'] = (self::$bucket['next']['url'] != $base) ? '<a href="' . self::$bucket['next']['url'] . '" rel="next">' . self::$bucket['next']['text'] . '</a>' : "";

        }

        if(is_string($current)) {

            for($i = 0; $i < $total; ++$i) {

                $slug = isset($pages[$i]['slug']) ? $pages[$i]['slug'] : preg_replace('#\.[a-z0-9]{3,4}$#', basename($pages[$i]));

                if($current == $slug) {

                    // Generate next/previous URL for single page
                    self::$bucket['prev']['url'] = isset($pages[$i - 1]) ? $base . $connector . $pages[$i - 1]['slug'] : $base;
                    self::$bucket['next']['url'] = isset($pages[$i + 1]) ? $base . $connector . $pages[$i + 1]['slug'] : $base;

                    // Generate next/previous text for single page
                    self::$bucket['prev']['text'] = isset($pages[$i - 1]) ? $speak->newer : $speak->home;
                    self::$bucket['next']['text'] = isset($pages[$i + 1]) ? $speak->older : $speak->home;

                    // Generate next/previous link for single page
                    self::$bucket['prev']['link'] = (self::$bucket['prev']['url'] != $base) ? '<a href="' . self::$bucket['prev']['url'] . '" rel="prev">' . self::$bucket['prev']['text'] . '</a>' : "";
                    self::$bucket['next']['link'] = (self::$bucket['next']['url'] != $base) ? '<a href="' . self::$bucket['next']['url'] . '" rel="next">' . self::$bucket['next']['text'] . '</a>' : "";

                }
            }

        }

        return Mecha::O(self::$bucket);

    }

}