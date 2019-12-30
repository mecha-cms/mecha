<?php namespace _\lot\x;

function asset($content) {
    $content = \str_replace('</head>', \Hook::fire('asset:head', [""], null, \Asset::class) . '</head>', $content);
    $content = \str_replace('</body>', \Hook::fire('asset:body', [""], null, \Asset::class) . '</body>', $content);
    return $content;
}

\Hook::set('asset:head', function($content) {
    $css = \Hook::fire('asset.css', [\Asset::join('.css')], null, \Asset::class);
    $style = "";
    $lot = \Asset::get();
    if (!empty($lot['style'])) {
        foreach ((new \Anemon($lot['style']))->sort([1, 'stack'], true) as $k => $v) {
            if (!empty($v[1])) {
                unset($v['path'], $v['stack'], $v['url']);
                $style .= new \HTML($v);
            }
        }
    }
    $style = \Hook::fire('asset:style', [$style], null, \Asset::class);
    return $content . $css . $style; // Put local CSS after remote CSS
});

\Hook::set('asset:body', function($content) {
    $js = \Hook::fire('asset.js', [\Asset::join('.js')], null, \Asset::class);
    $script = $template = "";
    $lot = \Asset::get();
    if (!empty($lot['script'])) {
        foreach ((new \Anemon($lot['script']))->sort([1, 'stack'], true) as $k => $v) {
            if (!empty($v[1])) {
                unset($v['path'], $v['stack'], $v['url']);
                $script .= new \HTML($v);
            }
        }
    }
    if (!empty($lot['template'])) {
        foreach ((new \Anemon($lot['template']))->sort([1, 'stack'], true) as $k => $v) {
            if (!empty($v[1])) {
                unset($v['path'], $v['stack'], $v['url']);
                $template .= new \HTML($v);
            }
        }
    }
    $script = \Hook::fire('asset:script', [$script], null, \Asset::class);
    $template = \Hook::fire('asset:template', [$template], null, \Asset::class);
    return $content . $template . $js . $script; // Put local JS after remote JS
});

\Hook::set('content', __NAMESPACE__ . "\\asset", 0);