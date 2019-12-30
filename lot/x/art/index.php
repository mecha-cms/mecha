<?php

namespace _\lot\x\art {
    function css($content) {
        $content = \trim($content);
        if ($content && false === \strpos($content, '</style>') && false === \strpos($content, '<link ')) {
            return '<style media="screen">' . $content . '</style>';
        }
        return $content;
    }
    function js($content) {
        $content = \trim($content);
        if ($content && false === \strpos($content, '</script>') && false === \strpos($content, '<script ')) {
            return '<script>' . $content . '</script>';
        }
        return $content;
    }
    function get() {
        global $state, $url;
        $folder = \LOT . \DS . 'page' . ($url->path ?? \State::get('x.page.path'));
        $i = $url['i'] ?? 1;
        if ($path = \File::exist([
            $folder . \DS . $i . '.page',
            $folder . \DS . $i . '.archive',
            $folder . '.page',
            $folder . '.archive'
        ])) {
            $page = new \Page($path);
            $css = $page['css'];
            $js = $page['js'];
            \State::set([
                'has' => [
                    'css' => !!$css,
                    'js' => !!$js
                ],
                'is' => ['art' => $css || $js],
                'not' => ['art' => !$css && !$js]
            ]);
        }
    }
    if (!\Request::is('Get', 'art') || \Get::get('art')) {
        \Hook::set('content', __NAMESPACE__, 1);
        \Hook::set('get', __NAMESPACE__ . "\\get", 0);
        \Hook::set('page.css', __NAMESPACE__ . "\\css", 2);
        \Hook::set('page.js', __NAMESPACE__ . "\\js", 2);
    }
}

namespace _\lot\x {
    function art($content) {
        if (empty($GLOBALS['page'])) {
            return $content;
        }
        $page = $GLOBALS['page'];
        // Append custom CSS before `</head>`…
        $content = \str_replace('</head>', $page->css . '</head>', $content);
        // Append custom JS before `</body>`…
        $content = \str_replace('</body>', $page->js . '</body>', $content);
        return $content;
    }
}