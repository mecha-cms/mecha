<?php

return array(
    '{{url.home}}' => $config->url,
    '{{url.index}}' => $config->url . '/' . $config->index->slug,
    '{{url}}' => $config->url . '/',
    '{{url.article}}' => $config->url . '/' . $config->index->slug . '/',
    '{{url.tag}}' => $config->url . '/' . $config->tag->slug . '/',
    '{{url.archive}}' => $config->url . '/' . $config->archive->slug . '/',
    '{{url.search}}' => $config->url . '/' . $config->search->slug . '/',
    '{{url.manager}}' => $config->url . '/' . $config->manager->slug . '/',
    '{{asset}}' => $config->url . '/cabinet/assets/'
);