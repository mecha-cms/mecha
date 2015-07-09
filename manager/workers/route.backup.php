<?php


/**
 * Backup/Restore Manager
 * ----------------------
 */

Route::accept($config->manager->slug . '/backup', function() use($config, $speak) {
    if(Guardian::get('status') !== 'pilot') {
        Shield::abort();
    }
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
        $accepted_mimes = array(
            'application/download',
            'application/octet-stream',
            'application/x-compressed',
            'application/x-zip-compressed',
            'application/zip',
            'multipart/x-zip',
        );
        $accepted_extensions = array(
            'zip'
        );
        $name = $_FILES['file']['name'];
        $type = $_FILES['file']['type'];
        $extension = File::E($name);
        if( ! empty($name)) {
            if( ! in_array($type, $accepted_mimes) || ! in_array($extension, $accepted_extensions)) {
                Notify::error(Config::speak('notify_invalid_file_extension', 'ZIP'));
            }
        } else {
            Notify::error($speak->notify_error_no_file_selected);
        }
        if( ! file_exists($destination)) {
            Notify::error(Config::speak('notify_folder_not_exist', '<code>' . $destination . '</code>'));
        }
        if( ! Notify::errors()) {
            File::upload($_FILES['file'], $destination, function() use($title) {
                Notify::clear();
                Notify::success(Config::speak('notify_success_uploaded', $title));
            });
            $P = array('data' => $_FILES);
            Weapon::fire('on_restore_construct', array($P, $P));
            if($uploaded = File::exist($destination . DS . $name)) {
                Package::take($uploaded)->extract(); // Extract the ZIP file
                File::open($uploaded)->delete(); // Delete the ZIP file
                Config::load(); // Refresh the configuration data ...
                Guardian::kick(Config::get('manager')->slug . '/backup');
            }
        } else {
            Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
                echo '<script>
(function($) {
    $(\'.tab-area .tab[href$="#tab-content-2"]\').trigger("click");
})(window.Zepto || window.jQuery);
</script>';
            }, 11);
        }
    }
    Config::set(array(
        'page_title' => $speak->backup . '/' . $speak->restore . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'cargo.backup.php'
    ));
    Shield::attach('manager', false);
});


/**
 * Backup Action(s)
 * ----------------
 */

Route::accept($config->manager->slug . '/backup/origin:(:all)', function($origin = "") use($config, $speak) {
    if(Guardian::get('status') !== 'pilot') {
        Shield::abort();
    }
    $time = date('Y-m-d-H-i-s');
    $site = Text::parse($config->title, '->slug');
    if(trim($origin, '.') === "") {
        $name = $site . '_' . $time . '.zip';
        Package::take(ROOT)->pack(ROOT . DS . $name);
    } else {
        $name = $site . '.cabinet.' . str_replace('/', '.', $origin) . '_' . $time . '.zip';
        Package::take(ROOT . DS . 'cabinet' . DS . $origin)->pack(ROOT . DS . $name);
        if($origin === 'shields') {
            Package::take(ROOT . DS . $name)->deleteFiles(array(
                'json.php',
                'rss.php',
                'sitemap.php',
                'widgets.css',
                'widgets.js'
            ));
        }
    }
    Guardian::kick($config->manager->slug . '/backup/send:' . $name);
});


/**
 * Downloading Backup File(s)
 * --------------------------
 */

Route::accept($config->manager->slug . '/backup/send:(:any)', function($file = "") use($config, $speak) {
    if(Guardian::get('status') !== 'pilot') {
        Shield::abort();
    }
    if($backup = File::exist(ROOT . DS . $file)) {
        $G = array('data' => array('path' => $backup, 'file' => $backup));
        Weapon::fire('on_backup_construct', array($G, $G));
        header('Content-Type: application/zip');
        // header('Content-Length: ' . filesize($backup));
        header('Content-Disposition: attachment; filename=' . $file);
        ob_clean();
        readfile($backup);
        ignore_user_abort(true);
        File::open($backup)->delete();
        Weapon::fire('on_backup_destruct', array($G, $G));
    } else {
        Shield::abort();
    }
    exit;
});