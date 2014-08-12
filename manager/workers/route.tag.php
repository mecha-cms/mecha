<?php


/**
 * Tags Manager
 * ------------
 */

Route::accept($config->manager->slug . '/tag', function() use($config, $speak) {
    if(Guardian::get('status') != 'pilot') {
        Shield::abort();
    }
    Config::set(array(
        'page_title' => $speak->tags . $config->title_separator . $config->manager->title,
        'files' => Get::rawTags('ASC', 'id'),
        'cargo' => DECK . DS . 'workers' . DS . 'tag.php'
    ));
    $G = array('data' => Config::get('files'));
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
        echo Asset::javascript('manager/sword/row-tag.js');
        echo '<script>
(function($, base) {
    base.add(\'on_row_increase\', function() {
        $(\'input[name="name[]"]\').each(function() {
            $.slugger($(this), $(this).parent().next().find(\'input\'), \'-\');
        });
    });
    base.fire(\'on_row_increase\');
})(Zepto, DASHBOARD);
</script>';
    });
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        $data = array();
        for($i = 0, $keys = $request['id'], $count = count($keys); $i < $count; ++$i) {
            if(trim($request['name'][$i]) !== "") {
                $slug = trim($request['slug'][$i]) !== "" ? $request['slug'][$i] : $request['name'][$i];
                $data[] = array(
                    'id' => (int) $keys[$i],
                    'name' => $request['name'][$i],
                    'slug' => Text::parse($slug)->to_slug,
                    'description' => $request['description'][$i]
                );
            }
        }
        $P = array('data' => $request);
        File::serialize($data)->saveTo(STATE . DS . 'tags.txt', 0600);
        Notify::success(Config::speak('notify_success_updated', array($speak->tags)));
        Weapon::fire('on_tag_update', array($G, $P));
        Guardian::kick($config->url_current);
    }
    Shield::attach('manager', false);
});