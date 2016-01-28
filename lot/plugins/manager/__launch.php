<?php


// Title
if( ! $config->manager->title) {
    $config->manager->title = $speak->manager->title_manager;
    Config::set('manager.title', $speak->manager->title_manager);
}


/**
 * Footer Link(s)
 * --------------
 */

// Add default article footer link(s)
Weapon::add('article_footer', function($article) use($config, $speak) {
    $e = File::E($article->path);
    $comments = count(glob(COMMENT . DS . Date::slug($article->id) . '_*_*.{txt,hold}', GLOB_NOSORT | GLOB_BRACE));
    $t = Jot::icon('comments') . ' ' . $comments;
    $tt = array('title' => $comments . ' ' . ($comments === 1 ? $speak->comment : $speak->comments));
    $comments = ($e === 'draft' || $comments === 0 ? Cell::span($t, $tt) : Cell::a($config->manager->slug . '/comment?filter=post%3A' . $article->id, $t, null, $tt)) . ' &middot; ';
    $status = Mecha::alter($e, array(
        'draft' => Jot::span('info', Jot::icon('clock-o') . ' ' . $speak->draft) . ' &middot; ',
        'archive' => Jot::span('info', Jot::icon('history') . ' ' . $speak->archive) . ' &middot; '
    ), "");
    echo $comments . $status . Cell::a($config->manager->slug . '/article/repair/id:' . $article->id, $speak->edit) . ' / ' . Cell::a($config->manager->slug . '/article/kill/id:' . $article->id, $speak->delete);
}, 20);

// Add default page footer link(s)
Weapon::add('page_footer', function($page) use($config, $speak) {
    $status = Mecha::alter(File::E($page->path), array(
        'draft' => Jot::span('info', Jot::icon('clock-o') . ' ' . $speak->draft) . ' &middot; ',
        'archive' => Jot::span('info', Jot::icon('history') . ' ' . $speak->archive) . ' &middot; '
    ), "");
    echo $status . Cell::a($config->manager->slug . '/page/repair/id:' . $page->id, $speak->edit) . ' / ' . Cell::a($config->manager->slug . '/page/kill/id:' . $page->id, $speak->delete);
}, 20);


/**
 * Backend Route(s)
 * ----------------
 *
 * Load the routes.
 *
 */

Weapon::add('plugins_after', function() use($config, $speak, $segment) {
    // loading cargo ...
    require __DIR__ . DS . 'workers' . DS . 'cargo.php';
    if($detour = File::exist(__DIR__ . DS . 'workers' . DS . 'route.' . $segment . '.php')) {
        require $detour;
    }
}, 1);