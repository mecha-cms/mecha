<?php

function fn_asset($content) {
    $content = str_ireplace('</head>', Hook::NS('asset.top', [""]) . '</head>', $content);
    $content = str_ireplace('</body>', Hook::NS('asset.bottom', [""]) . '</body>', $content);
    return $content;
}

Hook::set('asset.top', function($content) {
    return $content . Hook::NS('asset.css', [Asset::css()]);
});

Hook::set('asset.bottom', function($content) {
    return $content . Hook::NS('asset.js', [Asset::js()]);
});

Hook::set('shield.input', 'fn_asset', 0);