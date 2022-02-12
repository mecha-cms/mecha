<?php namespace x;

function asset($content) {
    return \strtr($content ?? "", [
        '</body>' => \Hook::fire('asset:body', [""], null, \Asset::class) . '</body>',
        '</head>' => \Hook::fire('asset:head', [""], null, \Asset::class) . '</head>'
    ]);
}

\Hook::set('asset:head', function($content) {
    $css = \Hook::fire('asset.css', [\Asset::join('.css')], null, \Asset::class);
    $style = "";
    $lot = \Asset::get();
    if (!empty($lot['style'])) {
        foreach ((new \Anemone($lot['style']))->sort([1, 'stack'], true) as $k => $v) {
            if (!empty($v[1])) {
                unset($v['link'], $v['path'], $v['stack']);
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
        foreach ((new \Anemone($lot['script']))->sort([1, 'stack'], true) as $k => $v) {
            if (!empty($v[1])) {
                unset($v['link'], $v['path'], $v['stack']);
                $script .= new \HTML($v);
            }
        }
    }
    if (!empty($lot['template'])) {
        foreach ((new \Anemone($lot['template']))->sort([1, 'stack'], true) as $k => $v) {
            if (!empty($v[1])) {
                unset($v['link'], $v['path'], $v['stack']);
                $template .= new \HTML($v);
            }
        }
    }
    $script = \Hook::fire('asset:script', [$script], null, \Asset::class);
    $template = \Hook::fire('asset:template', [$template], null, \Asset::class);
    return $content . $template . $js . $script; // Put template and local JS after remote JS
});

\Hook::set('content', __NAMESPACE__ . "\\asset", 0);