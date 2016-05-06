<?php

HTTP::mime('text/xml', $config->charset);

if($posts = Get::files(POST, 'txt')) {
    $sitemap = array();
    foreach($posts as $post) {
        if($post['is']['folder']) continue;
        $path = $post['path'];
        list($time, $kind, $slug) = explode('_', File::N($path), 3);
        $s = File::B(File::D($path));
        if(Get::kin($s . 'Anchor', false, true) && $s = call_user_func('Get::' . $s . 'Anchor', $path)) {
            $sitemap[] = array(
                'url' => $s->url,
                'date' => Date::format($s->time, 'c'),
                'changefreq' => 'weekly',
                'priority' => '1.0'
            );
        }
    }
    $sitemap = Filter::apply('sitemap', $sitemap);
    echo '<?xml version="1.0" encoding="UTF-8" ?>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    if( ! empty($sitemap)) {
        foreach($sitemap as $k => $v) {
            echo '<url>';
            echo '<loc>' . $v['url'] . '</loc>';
            echo '<lastmod>' . $v['date'] . '</lastmod>';
            echo '<changefreq>' . $v['changefreq'] . '</changefreq>';
            echo '<priority>' . $v['priority'] . '</priority>';
            echo '</url>';
        }
    }
    echo '</urlset>';
}