<?php


// Get tag(s) ...
$tags = Get::state_tag(null, array(), false);


/**
 * Tag Manager
 * -----------
 */

Route::accept($config->manager->slug . '/tag', function() use($config, $speak, $tags) {
    Config::set(array(
        'page_title' => $speak->tags . $config->title_separator . $config->manager->title,
        'cargo' => 'cargo.tag.php'
    ));
    Shield::lot(array(
        'segment' => 'tag',
        'files' => ! empty($tags) ? Mecha::O($tags) : false
    ))->attach('manager');
});


/**
 * Tag Repairer/Igniter
 * --------------------
 */

Route::accept(array($config->manager->slug . '/tag/ignite', $config->manager->slug . '/tag/repair/id:(:any)'), function($id = false) use($config, $speak, $tags) {
    if($id === false) {
        $_ = array_keys($tags);
        $data = array(
            'id' => $_ ? max($_) + 1 : 0,
            'name' => "",
            'slug' => "",
            'description' => "",
            'scope' => "" // no scope
        );
        $title = Config::speak('manager.title_new_', $speak->tag) . $config->title_separator . $config->manager->title;
    } else {
        if( ! isset($tags[$id])) {
            Shield::abort(); // Field not found!
        }
        $data = $tags[$id];
        $data['id'] = $id;
        $title = $speak->editing . ': ' . $data['name'] . $config->title_separator . $config->manager->title;
    }
    foreach($data as $k => $v) {
        $data[$k . '_raw'] = $v;
    }
    $G = array('data' => $data);
    Config::set(array(
        'page_title' => $title,
        'cargo' => 'repair.tag.php'
    ));
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Limit HTML tag(s) allowed in the name field
        $request['name'] = Text::parse($request['name'], '->text', str_replace('<a>', "", WISE_CELL_I));
        // Empty name field
        if(trim($request['name']) === "") {
            Notify::error(Config::speak('notify_error_empty_field', $speak->name));
        }
        // Empty slug field
        if(trim($request['slug']) === "") {
            $request['slug'] = $request['name'];
        }
        $s = $request['slug'] = Text::parse($request['slug'], '->slug');
        $rid = $request['id'];
        if($id === false) {
            $slugs = array();
            foreach($tags as $k => $v) {
                $slugs[$v['slug']] = 1;
            }
            // Duplicate slug
            if(isset($slugs[$s])) {
                Notify::error(Config::speak('notify_error_slug_exist', $s));
            }
            unset($slugs);
            // Duplicate ID
            if(isset($tags[$rid])) {
                Notify::error(Config::speak('notify_invalid_duplicate', $speak->id));
            }
        } else {
            unset($tags[$id]);
        }
        $tags[$rid] = array(
            'name' => $request['name'],
            'slug' => $s,
            'description' => $request['description']
        );
        if(isset($request['scope']) && is_array($request['scope'])) {
            sort($request['scope']);
            $tags[$rid]['scope'] = implode(',', $request['scope']);
        }
        $P = array('data' => $request);
        $P['data']['id'] = $rid;
        if( ! Notify::errors()) {
            ksort($tags);
            File::serialize($tags)->saveTo(STATE . DS . 'tag.txt', 0600);
            Notify::success(Config::speak('notify_success_' . ($id === false ? 'created' : 'updated'), $request['name']));
            Session::set('recent_item_update', $rid);
            Weapon::fire(array('on_tag_update', 'on_tag_' . ($id === false ? 'construct' : 'repair')), array($G, $P));
            Guardian::kick($id !== $rid ? $config->manager->slug . '/tag' : $config->manager->slug . '/tag/repair/id:' . $id);
        }
    }
    Shield::lot(array(
        'segment' => 'tag',
        'id' => $id,
        'file' => Mecha::O($data)
    ))->attach('manager');
});


/**
 * Tag Killer
 * ----------
 */

Route::accept($config->manager->slug . '/tag/kill/id:(:any)', function($id = false) use($config, $speak, $tags) {
    if( ! Guardian::happy(1)) {
        Shield::abort();
    }
    if( ! isset($tags[$id])) {
        Shield::abort(); // Tag not found!
    }
    $title = $tags[$id]['name'];
    Config::set(array(
        'page_title' => $speak->deleting . ': ' . $title . $config->title_separator . $config->manager->title,
        'cargo' => 'kill.tag.php'
    ));
    $G = array('data' => $tags);
    $G['data']['id'] = $id;
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        unset($tags[$id]); // delete ...
        ksort($tags);
        $P = array('data' => $tags);
        $P['data']['id'] = $id;
        File::serialize($tags)->saveTo(STATE . DS . 'tag.txt', 0600);
        Notify::success(Config::speak('notify_success_deleted', $title));
        Weapon::fire(array('on_tag_update', 'on_tag_destruct'), array($G, $P));
        Guardian::kick($config->manager->slug . '/tag');
    } else {
        Notify::warning(Config::speak('notify_confirm_delete_', '<strong>' . $title . '</strong>'));
    }
    Shield::lot(array(
        'segment' => 'tag',
        'id' => $id,
        'file' => Mecha::O($tags[$id])
    ))->attach('manager');
});