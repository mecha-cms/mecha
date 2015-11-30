<?php

return array(
    '{{url}}' => $config->url . '/',
    '{{url:%s}}' => $config->url . '/$1',
    '{{url.home}}' => $config->url,
    '{{url.index}}' => $config->url . '/' . $config->index->slug,
    '{{url.article}}' => $config->url . '/' . $config->index->slug . '/',
    '{{url.article:%s}}' => $config->url . '/' . $config->index->slug . '/$1',
    '{{url.page}}' => $config->url . '/', // alias for `{{url}}`
    '{{url.page:%s}}' => $config->url . '/$1', // alias for `{{url:%s}}`
    '{{url.tag:%s}}' => $config->url . '/' . $config->tag->slug . '/$1',
    '{{url.archive:%s}}' => $config->url . '/' . $config->archive->slug . '/$1',
    '{{url.search:%s}}' => $config->url . '/' . $config->search->slug . '/$1',
    '{{url.manager:%s}}' => $config->url . '/' . $config->manager->slug . '/$1',
    '{{asset}}' => File::url(ASSET) . '/',
    '{{asset:%s}}' => File::url(ASSET) . '/$1'
);