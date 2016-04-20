<?php


// URL segment(s)
$segment = explode('/', $config->url_path . '/');
$segment = $config->page_type === 'manager' ? $segment[1] : $segment[0];

// Loading plug(s)
foreach(glob(__DIR__ . DS . 'workers' . DS . 'engine' . DS . 'plug' . DS . '*.php', GLOB_NOSORT) as $plug) {
    require $plug;
}

// Logged in user(s) only
if(Guardian::happy()) require __DIR__ . DS . 'workers' . DS . 'task.manager.php';

// Loading frontend task(s) and route(s)
Weapon::add('routes_before', function() use($config, $speak) {
    require __DIR__ . DS . 'workers' . DS . 'task.comment.ignite.php';
    require __DIR__ . DS . 'workers' . DS . 'route.login.php';
});

// Add log in/out link in shield footer
function do_footer_manager_link($content, $path) {
    global $config, $speak;
    if(File::N($path) === 'block.footer.bar') {
        $s = Guardian::happy() ? '<a href="' . Filter::colon('manager:url', $config->url . '/' . $config->manager->slug . '/logout') . '" rel="nofollow">' . $speak->log_out . '</a>' : '<a href="' . Filter::colon('manager:url', $config->url . '/' . $config->manager->slug . '/login') . '" rel="nofollow">' . $speak->log_in . '</a>';
        return str_replace('<div class="blog-footer-right">', '<div class="blog-footer-right">' . $s, $content);
    }
    return $content;
}

// Apply `do_footer_manager_link` filter
Filter::add('chunk:output', 'do_footer_manager_link');