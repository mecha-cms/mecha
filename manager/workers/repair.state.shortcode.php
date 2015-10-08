<?php

return array(
    '{{url}}' => $config->url . '/',
    '{{url.home}}' => $config->url,
    '{{url.index}}' => $config->url . '/' . $config->index->slug,
    '{{url.article}}' => $config->url . '/' . $config->index->slug . '/',
    '{{url.tag}}' => $config->url . '/' . $config->tag->slug . '/',
    '{{url.archive}}' => $config->url . '/' . $config->archive->slug . '/',
    '{{url.search}}' => $config->url . '/' . $config->search->slug . '/',
    '{{url.manager}}' => $config->url . '/' . $config->manager->slug . '/',
    '{{url.current}}' => '{{php}}echo $config->url_current;{{/php}}',
    '{{asset}}' => $config->url . '/cabinet/assets/'
);