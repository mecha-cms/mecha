<?php


/**
 * Shields Manager
 * ---------------
 */

Route::accept($config->manager->slug . '/shield', function() use($config, $speak) {
    if(isset($_FILES) && ! empty($_FILES)) {
        Guardian::checkToken(Request::post('token'));
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
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $path = basename($name, '.' . $extension);
        if( ! empty($name)) {
            if(File::exist(SHIELD . DS . $path)) {
                Notify::error(Config::speak('notify_file_exist', array('<code>' . $path . '/&hellip;</code>')));
            } else {
                if( ! in_array($type, $accepted_mimes) || ! in_array($extension, $accepted_extensions)) {
                    Notify::error(Config::speak('notify_invalid_file_extension', array('ZIP')));
                }
            }
        } else {
            Notify::error($speak->notify_error_no_file_selected);
        }
        if( ! Notify::errors()) {
            File::upload($_FILES['file'], SHIELD, Config::speak('notify_success_uploaded', array($speak->shield)));
            $P = array('data' => $_FILES);
            Weapon::fire('on_shield_update', array($P, $P));
            Weapon::fire('on_shield_construct', array($P, $P));
            if($uploaded = File::exist(SHIELD . DS . $name)) {
                Package::take($uploaded)->extract(); // Extract the ZIP file
                File::open($uploaded)->delete(); // Delete the ZIP file
                Guardian::kick($config->manager->slug . '/shield');
            }
        } else {
            Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
                echo '<script>
(function($) {
    $(\'.tab-area .tab[href$="#tab-content-2"]\').trigger("click");
})(Zepto);
</script>';
            }, 11);
        }
    }
    Config::set(array(
        'page_title' => $speak->shields . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'shield.php'
    ));
    Shield::attach('manager', false);
});


/**
 * Shield Repair
 * -------------
 */

Route::accept($config->manager->slug . '/shield/repair/file:(:all)', function($name = "") use($config, $speak) {
    $name = str_replace(array('\\', '/'), DS, $name);
    $shield = Request::get('shield') ? Request::get('shield') : $config->shield;
    $qs = $shield != $config->shield ? '?shield=' . $shield : "";
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    if( ! $file = File::exist(SHIELD . DS . $shield . DS . $name)) {
        Shield::abort(); // file not found!
    }
    $G = array('data' => array('path' => $file, 'content' => File::open($file)->read()));
    Config::set(array(
        'page_title' => $speak->editing . ': ' . $speak->shield . $config->title_separator . $config->manager->title,
        'cargo' => DECK . DS . 'workers' . DS . 'repair.shield.php'
    ));
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() use($file) {
        echo '<script>
(function($) {
    new MTE($(\'textarea[name="content"]\')[0], {
        tabSize: \'' . (strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'js' ? '    ' : '  ') . '\',
        toolbar: false
    });
})(Zepto);
</script>';
    }, 11);
    if(Request::post()) {
        Guardian::checkToken(Request::post('token'));
        $P = array('data' => array('path' => $file, 'content' => Request::post('content')));
        if( ! Notify::errors()) {
            File::open($file)->write($P['data']['content'])->save(0600);
            Notify::success(Config::speak('notify_file_updated', array('<code>' . basename($name) . '</code>')));
            Weapon::fire('on_shield_update', array($G, $P));
            Weapon::fire('on_shield_repair', array($G, $P));
            Guardian::kick($config->url_current . $qs);
        }
    } else {
        Guardian::memorize($G['data']);
    }
    Shield::attach('manager', false);
});


/**
 * Shield Killer
 * -------------
 */

Route::accept($config->manager->slug . '/shield/kill/(file:|shield:)(:all)', function($prefix = "", $name = "") use($config, $speak) {
    $name = str_replace(array('\\', '/'), DS, $name);
    $shield = Request::get('shield') ? Request::get('shield') : $config->shield;
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    if($prefix == 'file:') {
        if( ! $file = File::exist(SHIELD . DS . $shield . DS . $name)) {
            Shield::abort(); // file not found!
        }
    } else {
        if( ! $file = File::exist(SHIELD . DS . $name)) {
            Shield::abort(); // folder not found!
        }
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . basename($name) . $config->title_separator . $config->manager->title,
        'name' => $name,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.shield.php'
    ));
    if(Request::post()) {
        Guardian::checkToken(Request::post('token'));
        $P = array('data' => array('path' => $file));
        File::open($file)->delete();
        Notify::success(Config::speak('notify_file_deleted', array('<code>' . basename($file) . '</code>')));
        Weapon::fire('on_shield_update', array($P, $P));
        Weapon::fire('on_shield_destruct', array($P, $P));
        Guardian::kick($config->manager->slug . '/shield');
    } else {
        Notify::warning($speak->notify_confirm_delete);
    }
    Shield::attach('manager', false);
});