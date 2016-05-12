<?php


/**
 * Backup Manager
 * --------------
 */

Route::accept($config->manager->slug . '/backup', function() use($config, $speak, $segment) {
    // Remove backup file(s) that is failed to delete
    if($backup = glob(ROOT . DS . Text::parse($config->title, '->slug') . '_*.zip', GLOB_NOSORT)) {
        foreach($backup as $back) {
            unlink($back);
        }
    }
    if(isset($_FILES) && ! empty($_FILES)) {
        Guardian::checkToken(Request::post('token'));
        $destination = Request::post('destination', ROOT, false);
        $title = Request::post('title', $speak->files, false);
        include PLUGIN . DS . 'manager' . DS . 'workers' . DS . 'task.package.ignite.php';
        if( ! Notify::errors()) {
            File::upload($_FILES['file'], $destination, function() use($title) {
                Notify::clear();
                Notify::success(Config::speak('notify_success_uploaded', $title));
            });
            if($destination === STATE) {
                Config::load(); // refresh ...
            }
            if($destination === PLUGIN) {
                Plugin::reload(); // refresh ...
            }
            $P = array('data' => $_FILES);
            Weapon::fire('on_restore_construct', array($P, $P));
            include PLUGIN . DS . 'manager' . DS . 'workers' . DS . 'task.package.php';
        } else {
            $tab_id = 'tab-content-2';
            include PLUGIN . DS . 'manager' . DS . 'workers' . DS . 'task.js.tab.php';
        }
    }
    Config::set(array(
        'page_title' => $speak->backup . '/' . $speak->restore . $config->title_separator . $config->manager->title,
        'cargo' => __DIR__ . DS . 'cargo.backup.php'
    ));
    Shield::lot(array('segment' => 'backup'))->attach('manager');
});


/**
 * Backup Action(s)
 * ----------------
 */

Route::accept($config->manager->slug . '/backup/origin:(:all)', function($origin = "") use($config, $speak) {
    $time = Date::slug(time());
    $site = Text::parse($config->title, '->slug');
    if(trim($origin, '.') === "") {
        $name = $site . '_' . $time . '.zip';
        Package::take(ROOT)->pack(ROOT . DS . $name);
    } else {
        $name = $site . '.' . File::B(CARGO) . '.' . str_replace('/', '.', $origin) . '_' . $time . '.zip';
        Package::take(CARGO . DS . $origin)->pack(ROOT . DS . $name);
        if($origin === 'shields') {
            Package::take(ROOT . DS . $name)->deleteFolder('normal'); // delete `normal` shield
            Package::take(ROOT . DS . $name)->deleteFiles(array( // delete `json.php`, `rss.php`, `sitemap.php`, `widgets.css` and `widgets.js` file(s)
                'json.php',
                'rss.php',
                'sitemap.php',
                'widgets.css',
                'widgets.js'
            ));
        }
        if($origin === 'extends') {
            Package::take(ROOT . DS . $name)->deleteFolder(File::B(CHUNK)); // delete `chunk` folder
        }
        /*
        if($origin === 'plugins') {
            Package::take(ROOT . DS . $name)->deleteFolders(array( // delete built-in plugin(s)
                '__editor',
                '__editor-button',
                '__preview',
                'asset-version',
                'backup',
                'cache',
                'comment-location',
                'comment-notify',
                'empty',
                'facebook-open-graph__',
                'manager',
                'markdown',
                'minify',
                'shortcode-php'
            ));
        }
        */
    }
    Guardian::kick($config->manager->slug . '/backup/send:' . $name);
});


/**
 * Downloading Backup File(s)
 * --------------------------
 */

Route::accept($config->manager->slug . '/backup/send:(:any)', function($file = "") use($config, $speak) {
    if($backup = File::exist(ROOT . DS . $file)) {
        $G = array('data' => array('path' => $backup, 'file' => $backup));
        Weapon::fire('on_backup_construct', array($G, $G));
        header('Content-Type: application/zip');
        // header('Content-Length: ' . filesize($backup));
        header('Content-Disposition: attachment; filename=' . $file);
        @ob_clean();
        readfile($backup);
        ignore_user_abort(true);
        File::open($backup)->delete();
        Weapon::fire('on_backup_destruct', array($G, $G));
    } else {
        Shield::abort();
    }
    exit;
});