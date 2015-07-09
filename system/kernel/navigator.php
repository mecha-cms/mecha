<?php

class Navigator extends Base {

    protected static $bucket = array();

    public static $config = array(
        'step' => 5,
        'classes' => array(
            'pagination' => 'pagination',
            'current' => 'current'
        )
    );

    /**
     * ============================================================================
     *  PAGINATION EXTRACTOR FOR LIST OF FILE(S)
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
     *  $pages     | array   | Array of file(s) to be paginated
     *  $current   | integer | The current page offset
     *  $current   | string  | The current page path
     *  $per_page  | integer | Number of file(s) to show per page request
     *  $connector | string  | Extra path to be inserted into URL
     *  ---------- | ------- | ----------------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function extract($pages = array(), $current = 1, $per_page = 10, $connector = '/') {

        // Set default next, previous and step data
        self::$bucket = array('prev' => false, 'next' => false, 'step' => false);

        $pages = (array) $pages;
        $config = Config::get();
        $speak = Config::speak();
        $base = $config->url;
        $q = str_replace('&', '&amp;', $config->url_query);
        $qq = strpos($connector, '?') !== false ? str_replace('?', '&amp;', $q) : $q;
        $total = count($pages);
        $sn = self::$config;

        if(strpos($connector, '%s') === false) {
            if(trim($connector, '/') !== "") {
                $connector = '/' . trim($connector, '/') . '/%s';
            } else {
                $connector = '/%s';
            }
        }

        if(is_int($current)) {

            $current = (int) $current;

            $prev = $current > 1 ? $current - 1 : false;
            $next = $current < ceil($total / $per_page) ? $current + 1 : false;

            // Generate next/previous URL for index page
            self::$bucket['prev']['url'] = Filter::apply('pager:prev.url', Filter::apply('pager:url', $prev ? $base . sprintf($connector, $prev) . $qq : $base . $q, $prev, $connector), $prev, $connector);
            self::$bucket['next']['url'] = Filter::apply('pager:next.url', Filter::apply('pager:url', $next ? $base . sprintf($connector, $next) . $qq : $base . $q, $next, $connector), $next, $connector);

            // Generate next/previous text for index page
            self::$bucket['prev']['text'] = $prev ? $speak->newer : $speak->home;
            self::$bucket['next']['text'] = $next ? $speak->older : $speak->home;

            // Generate next/previous link for index page
            self::$bucket['prev']['link'] = Filter::apply('pager:prev.link', Filter::apply('pager:link', $prev ? '<a href="' . self::$bucket['prev']['url'] . '" rel="prev">' . self::$bucket['prev']['text'] . '</a>' : "", $prev, $connector), $prev, $connector);
            self::$bucket['next']['link'] = Filter::apply('pager:next.link', Filter::apply('pager:link', $next ? '<a href="' . self::$bucket['next']['url'] . '" rel="next">' . self::$bucket['next']['text'] . '</a>' : "", $next, $connector), $next, $connector);

            // Generate pagination link(s) for index page
            $html = '<span' . ($sn['classes']['pagination'] !== false ? ' class="' . $sn['classes']['pagination'] . '"' : "") . '>';
            $chunk = (int) ceil($total / $per_page);
            $step = $chunk > self::$config['step'] ? self::$config['step'] : $chunk;
            $left = $current - $step;
            if($left < 1) $left = 1;
            if($chunk > 1) {
                $html .= Filter::apply('pager:step.link', Filter::apply('pager:link', $prev ? '<a href="' . $base . sprintf($connector, '1') . $qq . '">' . $speak->first . '</a>' : '<span>' . $speak->first . '</span>', 1, $connector), 1, $connector);
                $html .= Filter::apply('pager:step.link', Filter::apply('pager:link', $prev ? '<a href="' . $base . sprintf($connector, $prev) . $qq . '">' . $speak->prev . '</a>' : '<span>' . $speak->prev . '</span>', $prev, $connector), $prev, $connector);
                $html .= '<span>';
                for($i = $current - $step + 1; $i < $current + $step; ++$i) {
                    if($chunk > 1) {
                        if($i - 1 < $chunk && ($i > 0 && $i + 1 > $current - $left - round($chunk / 2))) {
                            $html .= Filter::apply('pager:step.link', Filter::apply('pager:link', $i !== $current ? '<a href="' . $base . sprintf($connector, $i) . $qq . '">' . $i . '</a>' : '<strong' . ($sn['classes']['current'] !== false ? ' class="' . $sn['classes']['current'] . '"' : "") . '>' . $i . '</strong>', $i, $connector), $i, $connector);
                            self::$bucket['step']['url'][] = Filter::apply('pager:step.url', Filter::apply('pager:url', $i !== $current ? $base . sprintf($connector, $i) . $qq : false, $i, $connector), $i, $connector);
                        }
                    }
                }
                $html .= '</span>';
                $html .= Filter::apply('pager:step.link', Filter::apply('pager:link', $next ? '<a href="' . $base . sprintf($connector, $next) . $qq . '">' . $speak->next . '</a>' : '<span>' . $speak->next . '</span>', $next, $connector), $next, $connector);
                $html .= Filter::apply('pager:step.link', Filter::apply('pager:link', $next ? '<a href="' . $base . sprintf($connector, $chunk) . $qq . '">' . $speak->last . '</a>' : '<span>' . $speak->last . '</span>', $chunk, $connector), $chunk, $connector);
            }

            self::$bucket['step']['link'] = $html . '</span>';

        }

        if(is_string($current)) {

            for($i = 0; $i < $total; ++$i) {

                if($pages[$i] === $current) {

                    $prev = isset($pages[$i - 1]) ? $pages[$i - 1] : false;
                    $next = isset($pages[$i + 1]) ? $pages[$i + 1] : false;

                    // Generate next/previous URL for single page
                    self::$bucket['prev']['url'] = Filter::apply('pager:prev.url', Filter::apply('pager:url', $prev ? $base . sprintf($connector, $prev) . $qq : $base . $q, $prev, $connector), $prev, $connector);
                    self::$bucket['next']['url'] = Filter::apply('pager:next.url', Filter::apply('pager:url', $next ? $base . sprintf($connector, $next) . $qq : $base . $q, $next, $connector), $next, $connector);

                    // Generate next/previous text for single page
                    self::$bucket['prev']['text'] = $prev ? $speak->newer : $speak->home;
                    self::$bucket['next']['text'] = $next ? $speak->older : $speak->home;

                    // Generate next/previous link for single page
                    self::$bucket['prev']['link'] = Filter::apply('pager:prev.link', Filter::apply('pager:link', (self::$bucket['prev']['url'] !== $base) ? '<a href="' . self::$bucket['prev']['url'] . '" rel="prev">' . self::$bucket['prev']['text'] . '</a>' : "", $prev, $connector), $prev, $connector);
                    self::$bucket['next']['link'] = Filter::apply('pager:next.link', Filter::apply('pager:link', (self::$bucket['next']['url'] !== $base) ? '<a href="' . self::$bucket['next']['url'] . '" rel="next">' . self::$bucket['next']['text'] . '</a>' : "", $next, $connector), $next, $connector);

                    break;

                }

            }

        }

        return Mecha::O(self::$bucket);

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

}