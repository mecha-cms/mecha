<?php

// Check for duplicate slug
if(
    $slug === $config->index->slug ||
    $slug === $config->tag->slug ||
    $slug === $config->archive->slug ||
    $slug === $config->search->slug ||
    $slug === $config->manager->slug
) {
    Notify::error(Config::speak('notify_error_slug_exist', $slug));
    Guardian::memorize($request);
}