<?php


/**
 * Cache Manager
 * -------------
 */

Route::accept(array($config->manager->slug . '/cache', $config->manager->slug . '/cache/(:num)'), function($offset = 1) use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $filter = Request::get('q', "");
    $filter = $filter ? Text::parse($filter, '->safe_file_name') : "";
    $files = Get::files(CACHE, '*', 'DESC', 'update', $filter);
    $files_chunk = Mecha::eat($files)->chunk($offset, $config->per_page * 2)->vomit();
    Config::set(array(
        'page_title' => $speak->caches . $config->title_separator . $config->manager->title,
        'offset' => $offset,
        'pagination' => Navigator::extract($files, $offset, $config->per_page * 2, $config->manager->slug . '/cache'),
        'cargo' => 'cargo.cache.php'
    ));
    Shield::lot(array(
        'segment' => 'cache',
        'files' => $files_chunk ? Mecha::O($files_chunk) : false
    ))->attach('manager');
});


/**
 * Cache Repairer
 * --------------
 */

Route::accept($config->manager->slug . '/cache/repair/(file|files):(:all)', function($prefix = "", $file = "") use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $path = File::path($file);
    if( ! $file = File::exist(CACHE . DS . $path)) {
        Shield::abort(); // File not found!
    }
    $G = array('data' => array('path' => $file));
    Config::set(array(
        'page_title' => $speak->editing . ': ' . File::B($path) . $config->title_separator . $config->manager->title,
        'cargo' => 'repair.cache.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $P = array('data' => $request);
        if(isset($request['content'])) {
            File::open($file)->write($request['content'])->save(0600);
        }
        Notify::success(Config::speak('notify_file_updated', '<code>' . $path . '</code>'));
        Session::set('recent_item_update', explode(DS, $path));
        $name = File::path($request['name']);
        if($name !== $path) {
            File::open($file)->moveTo(CACHE . DS . $name);
            Guardian::kick($config->manager->slug . '/cache/1');
        }
        Weapon::fire(array('on_cache_update', 'on_cache_repair'), array($G, $P));
        Guardian::kick($config->manager->slug . '/cache/repair/file:' . File::url($request['name']));
    }
    Shield::lot(array(
        'segment' => 'cache',
        'path' => $path,
        'content' => File::open($file)->read()
    ))->attach('manager');
});


/**
 * Cache Killer
 * ------------
 */

Route::accept($config->manager->slug . '/cache/kill/(file|files):(:all)', function($prefix = "", $file = "") use($config, $speak) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    $path = File::path($file);
    if(strpos($path, ';') !== false) {
        $deletes = explode(';', $path);
    } else {
        if( ! File::exist(CACHE . DS . $path)) {
            Shield::abort(); // File not found!
        } else {
            $deletes = array($path);
        }
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . (count($deletes) === 1 ? File::B($path) : $speak->caches) . $config->title_separator . $config->manager->title,
        'cargo' => 'kill.cache.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $info_path = Mecha::walk($deletes, function($v) {
            $_path = CACHE . DS . $v;
            File::open($_path)->delete();
            return $_path;
        });
        $P = array('data' => array('files' => $info_path));
        Notify::success(Config::speak('notify_file_deleted', '<code>' . implode('</code>, <code>', $deletes) . '</code>'));
        Weapon::fire(array('on_cache_update', 'on_cache_destruct'), array($P, $P));
        Guardian::kick($config->manager->slug . '/cache/1');
    } else {
        Notify::warning(count($deletes) === 1 ? Config::speak('notify_confirm_delete_', '<code>' . $path . '</code>') : $speak->notify_confirm_delete);
    }
    Shield::lot(array(
        'segment' => 'cache',
        'files' => Mecha::O($deletes)
    ))->attach('manager');
});


/**
 * Multiple Cache Action
 * ---------------------
 */

Route::accept($config->manager->slug . '/cache/do', function() use($config, $speak) {
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        if( ! isset($request['selected'])) {
            Notify::error($speak->notify_error_no_files_selected);
            Guardian::kick($config->manager->slug . '/cache/1');
        }
        $files = Mecha::walk($request['selected'], function($v) {
            return str_replace('%2F', '/', Text::parse($v, '->encoded_url'));
        });
        Guardian::kick($config->manager->slug . '/cache/' . $request['action'] . '/files:' . implode(';', $files));
    }
});