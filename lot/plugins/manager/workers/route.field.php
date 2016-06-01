<?php


// Get field(s) ...
$fields = Get::state_field(null, array(), false);

/**
 * Field Manager
 * -------------
 */

Route::accept($config->manager->slug . '/field', function() use($config, $speak, $fields) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    Config::set(array(
        'page_title' => $speak->fields . $config->title_separator . $config->manager->title,
        'cargo' => 'cargo.field.php'
    ));
    Shield::lot(array(
        'segment' => 'field',
        'files' => ! empty($fields) ? Mecha::O($fields) : false
    ))->attach('manager');
});


/**
 * Field Repairer/Igniter
 * ----------------------
 */

Route::accept(array($config->manager->slug . '/field/ignite', $config->manager->slug . '/field/repair/key:(:any)'), function($key = false) use($config, $speak, $fields) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    if($key === false) {
        $data = array(
            'key' => false,
            'title' => "",
            'type' => 'text',
            'placeholder' => "",
            'value' => "",
            'description' => "",
            'scope' => 'article',
            'attributes' => array(
                'pattern' => null
            )
        );
        $title = Config::speak('manager.title_new_', $speak->field) . $config->title_separator . $config->manager->title;
    } else {
        if( ! isset($fields[$key])) {
            Shield::abort(); // Field not found!
        }
        $data = $fields[$key];
        $data['key'] = $key;
        $title = $speak->editing . ': ' . $data['title'] . $config->title_separator . $config->manager->title;
    }
    foreach($data as $k => $v) {
        $data[$k . '_raw'] = $v;
    }
    $G = array('data' => $data);
    Config::set(array(
        'page_title' => $title,
        'cargo' => 'repair.field.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Limit HTML tag(s) allowed in the title field
        $request['title'] = Text::parse($request['title'], '->text', str_replace('<a>', "", WISE_CELL_I));
        // Empty title field
        if(trim($request['title']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', $speak->title));
        }
        // Empty key field
        if(trim($request['key']) === "") {
            $request['key'] = $request['title'];
        }
        $k = $request['key'] = Text::parse($request['key'], '->array_key', true);
        if($key === false) {
            if(isset($fields[$k])) {
                Notify::error(Config::speak('notify_error_key_exist', $k));
            }
        } else {
            unset($fields[$key]);
        }
        $P = array('data' => $request);
        $P['data']['key'] = $key;
        unset($request['token']);
        $fields[$k] = $request;
        foreach($fields[$k] as $kk => &$vv) {
            // Translate array to string
            if($kk === 'scope' && is_array($vv)) {
                sort($vv);
                $vv = implode(',', $vv);
            }
            // Remove field(s) with empty value
            if($kk === 'placeholder' || $kk === 'description') {
                if(trim($vv) === "") unset($fields[$k][$kk]);
            }
            // --ibid
            if($kk === 'attributes' && empty($vv)) {
                unset($fields[$k][$kk]);
            }
            // --ibid
            if(isset($vv['pattern']) && trim($vv['pattern']) === "") {
                unset($fields[$k][$kk]['pattern']);
            }
        }
        unset($vv);
        if( ! Notify::errors()) {
            ksort($fields);
            File::serialize($fields)->saveTo(STATE . DS . 'field.txt', 0600);
            Notify::success(Config::speak('notify_success_' . ($key === false ? 'created' : 'updated'), $request['title']));
            Session::set('recent_item_update', $k);
            Weapon::fire(array('on_field_update', 'on_field_' . ($key === false ? 'construct' : 'repair')), array($G, $P));
            Guardian::kick($key !== $k ? $config->manager->slug . '/field' : $config->manager->slug . '/field/repair/key:' . $key);
        }
    }
    Shield::lot(array(
        'segment' => 'field',
        'id' => $key,
        'file' => Mecha::O($data)
    ))->attach('manager');
});


/**
 * Field Killer
 * ------------
 */

Route::accept($config->manager->slug . '/field/kill/key:(:any)', function($key = false) use($config, $speak, $fields) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    if( ! isset($fields[$key])) {
        Shield::abort(); // Field not found!
    }
    $title = $fields[$key]['title'];
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . $title . $config->title_separator . $config->manager->title,
        'cargo' => 'kill.field.php'
    ));
    $G = array('data' => $fields);
    $G['data']['key'] = $key;
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        unset($fields[$key]); // delete ...
        ksort($fields);
        $P = array('data' => $fields);
        $P['data']['key'] = $key;
        File::serialize($fields)->saveTo(STATE . DS . 'field.txt', 0600);
        Notify::success(Config::speak('notify_success_deleted', $title));
        Weapon::fire(array('on_field_update', 'on_field_destruct'), array($G, $P));
        Guardian::kick($config->manager->slug . '/field');
    } else {
        Notify::warning(Config::speak('notify_confirm_delete_', '<strong>' . $title . '</strong>'));
    }
    Shield::lot(array(
        'segment' => 'field',
        'id' => $key,
        'file' => Mecha::O($fields[$key])
    ))->attach('manager');
});