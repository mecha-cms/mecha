<?php


/**
 * Fields Manager
 * --------------
 */

Route::accept($config->manager->slug . '/field', function() use($config, $speak) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    if($file = File::exist(STATE . DS . 'fields.txt')) {
        $fields = File::open($file)->unserialize();
        ksort($fields);
    }
    Config::set(array(
        'page_title' => $speak->fields . $config->title_separator . $config->manager->title,
        'files' => isset($fields) && ! empty($fields) ? $fields : false,
        'cargo' => DECK . DS . 'workers' . DS . 'field.php'
    ));
    Shield::attach('manager', false);
});


/**
 * Field Repair/Igniter
 * --------------------
 */

Route::accept(array($config->manager->slug . '/field/ignite', $config->manager->slug . '/field/repair/key:(:any)'), function($key = false) use($config, $speak) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    if($file = File::exist(STATE . DS . 'fields.txt')) {
        $fields = File::open($file)->unserialize();
    } else {
        $fields = array();
    }
    if($key === false) {
        $data = array(
            'title' => "",
            'type' => "",
            'value' => "",
            'scope' => 'article'
        );
        Config::set('page_title', Config::speak('manager.title_new_', array($speak->field)) . $config->title_separator . $config->manager->title);
    } else {
        if( ! isset($fields[$key])) {
            Shield::abort();
        }
        $data = $fields[$key];
        Config::set('page_title', $speak->editing . ': ' . $data['title'] . $config->title_separator . $config->manager->title);
    }
    $G = array('data' => $data);
    $G['data']['key'] = $key;
    Config::set(array(
        'page_type' => 'manager',
        'file' => $data,
        'cargo' => DECK . DS . 'workers' . DS . 'repair.field.php'
    ));
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
        echo '<script>
(function($) {
    $.slugger($(\'input[name="title"]\'), $(\'input[name="key"]\'), \'_\');
})(Zepto);
</script>';
    });
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Empty title field
        if(trim($request['title']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', array($speak->title)));
        }
        // Empty key field
        if(trim($request['key']) === "") {
            $request['key'] = $request['title'];
        }
        $r_key = Text::parse(strtolower($request['key']))->to_array_key;
        $fields[$r_key] = array(
            'title' => $request['title'],
            'type' => $request['type'],
            'value' => $request['value'],
            'scope' => $request['scope']
        );
        $P = array('data' => $request);
        if( ! Notify::errors()) {
            File::serialize($fields)->saveTo(STATE . DS . 'fields.txt', 0600);
            Notify::success(Config::speak('notify_success_' . ($key === false ? 'created' : 'updated'), array($request['title'])));
            Weapon::fire('on_field_update', array($G, $P));
            Weapon::fire('on_field_' . ($key === false ? 'construct' : 'repair'), array($G, $P));
            Guardian::kick($config->manager->slug . '/field');
        }
    }
    Shield::define('the_key', $key)->attach('manager', false);
});


/**
 * Field Killer
 * ------------
 */

Route::accept($config->manager->slug . '/field/kill/key:(:any)', function($key = "") use($config, $speak) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    if($file = File::exist(STATE . DS . 'fields.txt')) {
        $fields = File::open($file)->unserialize();
    } else {
        $fields = array();
    }
    if( ! isset($fields[$key])) {
        Shield::abort();
    } else {
        $data = $fields[$key];
    }
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . $data['title'] . $config->title_separator . $config->manager->title,
        'file' => $data,
        'cargo' => DECK . DS . 'workers' . DS . 'kill.field.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $P = array('data' => $request);
        $P['data']['key'] = $key;
        $deleted_field = $fields[$key]['title'];
        unset($fields[$key]); // delete ...
        File::serialize($fields)->saveTo(STATE . DS . 'fields.txt', 0600);
        Notify::success(Config::speak('notify_success_deleted', array($deleted_field)));
        Weapon::fire('on_field_update', array($P, $P));
        Weapon::fire('on_field_destruct', array($P, $P));
        Guardian::kick($config->manager->slug . '/field');
    } else {
        Notify::warning($speak->notify_confirm_delete);
    }
    Shield::define('the_key', $key)->attach('manager', false);
});