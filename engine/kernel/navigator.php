<?php

class Navigator extends __ {

    public static $config = array(
        'step' => 5,
        'classes' => array(
            'navigation' => 'pagination',
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
     *    echo $pager->prev->anchor;
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

    public static function extract($pages = array(), $current = 1, $per_page = 10, $connector = false) {

        // Set default next, previous and step data
        $bucket = array('prev' => false, 'next' => false, 'step' => false);
        $pages = (array) $pages;
        $config = Config::get();
        $speak = Config::speak();
        $base = $config->url;

        if($connector === false) {
            $connector = '/%s';
        } else if(strpos($connector, '%s') === false) {
            $connector = '/' . $connector . '/%s';
        }

        $q = str_replace('&', '&amp;', $config->url_query);
        $qq = strpos($connector, '?') !== false ? str_replace('?', '&amp;', $q) : $q;
        $total = count($pages);
        $c = self::$config;

        if(is_numeric($current)) {

            $current = (int) $current;

            $prev = $current > 1 ? $current - 1 : false;
            $next = $current < ceil($total / $per_page) ? $current + 1 : false;

            // Generate next/previous URL for index page
            $bucket['prev']['url'] = Filter::apply(array('pager:prev.url', 'pager:url', 'url'), $prev ? $base . sprintf($connector, $prev) . $qq : $base . $q, $prev, $connector);
            $bucket['next']['url'] = Filter::apply(array('pager:next.url', 'pager:url', 'url'), $next ? $base . sprintf($connector, $next) . $qq : $base . $q, $next, $connector);

            // Generate next/previous anchor for index page
            $bucket['prev']['anchor'] = Filter::apply(array('pager:prev.anchor', 'pager:anchor', 'anchor'), $prev ? '<a href="' . $bucket['prev']['url'] . '" rel="prev">' . $speak->newer . '</a>' : "", $prev, $connector);
            $bucket['next']['anchor'] = Filter::apply(array('pager:next.anchor', 'pager:anchor', 'anchor'), $next ? '<a href="' . $bucket['next']['url'] . '" rel="next">' . $speak->older . '</a>' : "", $next, $connector);

            // Generate pagination anchor(s) for index page
            $html = '<span' . ($c['classes']['navigation'] !== false ? ' class="' . $c['classes']['navigation'] . '"' : "") . '>';
            $chunk = (int) ceil($total / $per_page);
            $step = $chunk > self::$config['step'] ? self::$config['step'] : $chunk;
            $left = $current - $step;
            if($left < 1) $left = 1;
            if($chunk > 1) {
                $bucket['step']['url']['first'] = Filter::apply(array('pager:step.url', 'pager:url', 'url'), $prev ? $base . sprintf($connector, 1) . $qq : false, 1, $connector);
                $bucket['step']['url']['prev'] = Filter::apply(array('pager:step.url', 'pager:url', 'url'), $prev ? $base . sprintf($connector, $prev) . $qq : false, $prev, $connector);
                $bucket['step']['anchor']['first'] = Filter::apply(array('pager:step.anchor', 'pager:anchor', 'anchor'), $prev ? '<a href="' . $bucket['step']['url']['first'] . '">' . $speak->first . '</a>' : '<span>' . $speak->first . '</span>', 1, $connector);
                $bucket['step']['anchor']['prev'] = Filter::apply(array('pager:step.anchor', 'pager:anchor', 'anchor'), $prev ? '<a href="' . $bucket['step']['url']['prev'] . '" rel="prev">' . $speak->prev . '</a>' : '<span>' . $speak->prev . '</span>', $prev, $connector);
                $html .= $bucket['step']['anchor']['first'] . $bucket['step']['anchor']['prev'];
                $html .= '<span>';
                for($i = $current - $step + 1; $i < $current + $step; ++$i) {
                    if($chunk > 1) {
                        if($i - 1 < $chunk && ($i > 0 && $i + 1 > $current - $left - round($chunk / 2))) {
                            $bucket['step']['url'][$i] = Filter::apply(array('pager:step.url', 'pager:url'), $i !== $current ? $base . sprintf($connector, $i) . $qq : false, $i, $connector);
                            $bucket['step']['anchor'][$i] = Filter::apply(array('pager:step.anchor', 'pager:anchor', 'anchor'), $i !== $current ? '<a href="' . $bucket['step']['url'][$i] . '">' . $i . '</a>' : '<strong' . ($c['classes']['current'] !== false ? ' class="' . $c['classes']['current'] . '"' : "") . '>' . $i . '</strong>', $i, $connector);
                            $html .= $bucket['step']['anchor'][$i];
                        }
                    }
                }
                $html .= '</span>';
                $bucket['step']['url']['next'] = Filter::apply(array('pager:step.url', 'pager:url', 'url'), $next ? $base . sprintf($connector, $next) . $qq : false, $next, $connector);
                $bucket['step']['url']['last'] = Filter::apply(array('pager:step.url', 'pager:url', 'url'), $next ? $base . sprintf($connector, $chunk) . $qq : false, $chunk, $connector);
                $bucket['step']['anchor']['next'] = Filter::apply(array('pager:step.anchor', 'pager:anchor'), $next ? '<a href="' . $bucket['step']['url']['next'] . '" rel="next">' . $speak->next . '</a>' : '<span>' . $speak->next . '</span>', $next, $connector);
                $bucket['step']['anchor']['last'] = Filter::apply(array('pager:step.anchor', 'pager:anchor'), $next ? '<a href="' . $bucket['step']['url']['last'] . '">' . $speak->last . '</a>' : '<span>' . $speak->last . '</span>', $chunk, $connector);
                $html .= $bucket['step']['anchor']['next'] . $bucket['step']['anchor']['last'];
            }

            $bucket['step']['html'] = Filter::apply(array('pager:step.html', 'pager:html'), $html . '</span>');

        } else if(is_string($current)) {

            for($i = 0; $i < $total; ++$i) {

                if($pages[$i] === $current) {

                    $prev = isset($pages[$i - 1]) ? $pages[$i - 1] : false;
                    $next = isset($pages[$i + 1]) ? $pages[$i + 1] : false;

                    // Generate next/previous URL for single page
                    $bucket['prev']['url'] = Filter::apply(array('pager:prev.url', 'pager:url', 'url'), $prev ? $base . sprintf($connector, $prev) . $qq : $base . $q, $prev, $connector);
                    $bucket['next']['url'] = Filter::apply(array('pager:next.url', 'pager:url', 'url'), $next ? $base . sprintf($connector, $next) . $qq : $base . $q, $next, $connector);

                    // Generate next/previous anchor for single page
                    $bucket['prev']['anchor'] = Filter::apply(array('pager:prev.anchor', 'pager:anchor', 'anchor'), ($bucket['prev']['url'] !== $base) ? '<a href="' . $bucket['prev']['url'] . '" rel="prev">' . $speak->newer . '</a>' : "", $prev, $connector);
                    $bucket['next']['anchor'] = Filter::apply(array('pager:next.anchor', 'pager:anchor', 'anchor'), ($bucket['next']['url'] !== $base) ? '<a href="' . $bucket['next']['url'] . '" rel="next">' . $speak->older . '</a>' : "", $next, $connector);

                    break;

                }

            }

        }

        return Mecha::O($bucket);

    }

}