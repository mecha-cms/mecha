<?php


/**
 * Tag Manager
 * -----------
 */

Route::accept($config->manager->slug . '/tag', function() use($config, $speak) {
    $tags = Get::rawTags('ASC', 'id');
    Config::set(array(
        'page_title' => $speak->tags . $config->title_separator . $config->manager->title,
        'files' => $tags,
        'cargo' => DECK . DS . 'workers' . DS . 'cargo.tag.php'
    ));
    $G = array('data' => $tags);
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        // Check for duplicate ID
        foreach(array_count_values($request['id']) as $id => $count) {
            if(trim($id) !== "" && $count > 1) {
                Notify::error(Config::speak('notify_invalid_duplicate', $speak->id));
                break;
            }
        }
        // Check for duplicate slug
        foreach(array_count_values($request['slug']) as $slug => $count) {
            if(trim($slug) !== "" && $count > 1) {
                Notify::error(Config::speak('notify_invalid_duplicate', strtolower($speak->slug)));
                break;
            }
        }
        if( ! Notify::errors()) {
            $data = array();
            $keys = $request['id'];
            for($i = 0, $count = count($keys); $i < $count; ++$i) {
                if(trim($request['name'][$i]) !== "" && trim($request['id'][$i]) !== "" && is_numeric($request['id'][$i])) {
                    $slug = trim($request['slug'][$i]) !== "" ? $request['slug'][$i] : $request['name'][$i];
                    $data[$i] = array(
                        'id' => (int) $keys[$i],
                        'name' => $request['name'][$i],
                        'slug' => Text::parse($slug, '->slug'),
                        'description' => $request['description'][$i]
                    );
                }
            }
            $P = array('data' => $data);
            File::serialize($data)->saveTo(STATE . DS . 'tag.txt', 0600);
            Notify::success(Config::speak('notify_success_updated', $speak->tags));
            Weapon::fire('on_tag_update', array($G, $P));
        }
        Guardian::kick($config->url_current);
    }
    Weapon::add('SHIPMENT_REGION_BOTTOM', function() {
        echo '<script>
(function($, base) {
    base.add(\'on_row_increase\', function() {
        $(\'input[name="id[]"]\').last().val(parseInt($(\'input[name="id[]"]\').last().closest(\'tr\').prev().find(\'input[name="id[]"]\').val(), 10) + 1 || "");
        $(\'input[name="name[]"]\').each(function() {
            $.slug($(this), $(this).parent().next().find(\'input\'), \'-\');
        });
    });
    base.fire(\'on_row_increase\');
})(window.Zepto || window.jQuery, DASHBOARD);
</script>';
    }, 11);
    Shield::attach('manager', false);
});