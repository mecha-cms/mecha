<?php

class Navigator {

    private static $bucket = array();
    private static $o = array();

    public static $config = array(
        'step' => 5,
        'classes' => array(
            'pagination' => 'pagination',
            'current' => 'current'
        )
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
     *  $per_page  | integer | Number of files to show per page request
     *  $connector | string  | Extra path to be inserted into URL
     *  ---------- | ------- | ----------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function extract($pages = array(), $current = 1, $per_page = 10, $connector = '/') {

        // Set default next, previous and step data
        self::$bucket = array('prev' => false, 'next' => false, 'step' => false);

        $config = Config::get();
        $speak = Config::speak();
        $base = $config->url;
        $q = str_replace('&', '&amp;', $config->url_query);
        $total = count($pages);
        $sn = self::$config;

        if(trim($connector, '/') !== "") $connector = '/' . trim($connector, '/') . '/';

        if(is_numeric($current)) {

            $current = (int) $current;

            $prev = $current > 1 ? $current - 1 : false;
            $next = $current < ceil($total / $per_page) ? $current + 1 : false;

            // Generate next/previous URL for index page
            self::$bucket['prev']['url'] = Filter::apply('pager:prev.url', Filter::apply('pager:url', $prev ? $base . $connector . $prev . $q : $base . $q, $prev, $connector), $prev, $connector);
            self::$bucket['next']['url'] = Filter::apply('pager:next.url', Filter::apply('pager:url', $next ? $base . $connector . $next . $q : $base . $q, $next, $connector), $next, $connector);

            // Generate next/previous text for index page
            self::$bucket['prev']['text'] = $prev ? $speak->newer : $speak->home;
            self::$bucket['next']['text'] = $next ? $speak->older : $speak->home;

            // Generate next/previous link for index page
            self::$bucket['prev']['link'] = Filter::apply('pager:prev.link', Filter::apply('pager:link', $prev ? '<a href="' . self::$bucket['prev']['url'] . '" rel="prev">' . self::$bucket['prev']['text'] . '</a>' : "", $prev, $connector), $prev, $connector);
            self::$bucket['next']['link'] = Filter::apply('pager:next.link', Filter::apply('pager:link', $next ? '<a href="' . self::$bucket['next']['url'] . '" rel="next">' . self::$bucket['next']['text'] . '</a>' : "", $next, $connector), $next, $connector);

            // Generate pagination links for index page
            $html = '<span' . ($sn['classes']['pagination'] !== false ? ' class="' . $sn['classes']['pagination'] . '"' : "") . '>';
            $chunk = (int) ceil($total / $per_page);
            $step = $chunk > self::$config['step'] ? self::$config['step'] : $chunk;
            $left = $current - $step;
            if($left < 1) $left = 1;
            if($chunk > 1) {
                $html .= Filter::apply('pager:step.link', Filter::apply('pager:link', $prev ? '<a href="' . $base . $connector . '1' . $q . '">' . $speak->first . '</a>' : '<span>' . $speak->first . '</span>', 1, $connector), 1, $connector);
                $html .= Filter::apply('pager:step.link', Filter::apply('pager:link', $prev ? '<a href="' . $base . $connector . $prev . $q . '">' . $speak->prev . '</a>' : '<span>' . $speak->prev . '</span>', $prev, $connector), $prev, $connector);
                $html .= '<span>';
                for($i = $current - $step + 1; $i < $current + $step; ++$i) {
                    if($chunk > 1) {
                        if($i - 1 < $chunk && ($i > 0 && $i + 1 > $current - $left - round($chunk / 2))) {
                            $html .= Filter::apply('pager:step.link', Filter::apply('pager:link', $i != $current ? '<a href="' . $base . $connector . $i . $q . '">' . $i . '</a>' : '<strong' . ($sn['classes']['current'] !== false ? ' class="' . $sn['classes']['current'] . '"' : "") . '>' . $i . '</strong>', $i, $connector), $i, $connector);
                            self::$bucket['step']['url'][] = Filter::apply('pager:step.url', Filter::apply('pager:url', $i != $current ? $base . $connector . $i . $q : false, $i, $connector), $i, $connector);
                        }
                    }
                }
                $html .= '</span>';
                $html .= Filter::apply('pager:step.link', Filter::apply('pager:link', $next ? '<a href="' . $base . $connector . $next . $q . '">' . $speak->next . '</a>' : '<span>' . $speak->next . '</span>', $next, $connector), $next, $connector);
                $html .= Filter::apply('pager:step.link', Filter::apply('pager:link', $next ? '<a href="' . $base . $connector . $chunk . $q . '">' . $speak->last . '</a>' : '<span>' . $speak->last . '</span>', $chunk, $connector), $chunk, $connector);
            }

            self::$bucket['step']['link'] = $html . '</span>';

        }

        if(is_string($current)) {

            for($i = 0; $i < $total; ++$i) {

                if($pages[$i] == $current) {

                    $prev = isset($pages[$i - 1]) ? self::slug($pages[$i - 1]) : false;
                    $next = isset($pages[$i + 1]) ? self::slug($pages[$i + 1]) : false;

                    // Generate next/previous URL for single page
                    self::$bucket['prev']['url'] = Filter::apply('pager:prev.url', Filter::apply('pager:url', $prev ? $base . $connector . $prev . $q : $base . $q, $prev, $connector), $prev, $connector);
                    self::$bucket['next']['url'] = Filter::apply('pager:next.url', Filter::apply('pager:url', $next ? $base . $connector . $next . $q : $base . $q, $next, $connector), $next, $connector);

                    // Generate next/previous text for single page
                    self::$bucket['prev']['text'] = $prev ? $speak->newer : $speak->home;
                    self::$bucket['next']['text'] = $next ? $speak->older : $speak->home;

                    // Generate next/previous link for single page
                    self::$bucket['prev']['link'] = Filter::apply('pager:prev.link', Filter::apply('pager:link', (self::$bucket['prev']['url'] != $base) ? '<a href="' . self::$bucket['prev']['url'] . '" rel="prev">' . self::$bucket['prev']['text'] . '</a>' : "", $prev, $connector), $prev, $connector);
                    self::$bucket['next']['link'] = Filter::apply('pager:next.link', Filter::apply('pager:link', (self::$bucket['next']['url'] != $base) ? '<a href="' . self::$bucket['next']['url'] . '" rel="next">' . self::$bucket['next']['text'] . '</a>' : "", $next, $connector), $next, $connector);

                    break;

                }

            }

        }

        return Mecha::O(self::$bucket);

    }

    private static function slug($input) {
        $extension = pathinfo($input, PATHINFO_EXTENSION);
        $base = basename($input, '.' . $extension);
        $parts = explode('_', $base, 3);
        return isset($parts[2]) ? $parts[2] : $base . '.' . $extension;
    }

    // Configure ...
    public static function configure($key, $value = null) {
        if(is_array($key)) {
            Mecha::extend(self::$config, $key);
        } else {
            if(is_array($value)) {
                Mecha::extend(self::$config[$key], $value);
            } else {
                self::$config[$key] = $value;
            }
        }
        return new static;
    }

    // Add new method with `Navigator::plug('foo')`
    public static function plug($kin, $action) {
        self::$o[$kin] = $action;
    }

    // Call the added method with `Navigator::foo()`
    public static function __callStatic($kin, $arguments = array()) {
        if( ! isset(self::$o[$kin])) {
            Guardian::abort('Method <code>Navigator::' . $kin . '()</code> does not exist.');
        }
        return call_user_func_array(self::$o[$kin], $arguments);
    }

}