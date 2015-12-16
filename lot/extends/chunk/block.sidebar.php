<aside class="blog-sidebar widgets">
<?php

if($manager && Widget::exist('manager')) {
    Shield::chunk('block.widget', array(
        'title' => $speak->widget->manager_menus,
        'content' => Widget::manager()
    ));
}

$ws = array(
    array(
        'title' => $speak->widget->search_form,
        'content' => Widget::search()
    ),
    array(
        'title' => $speak->widget->tags,
        'content' => Widget::tag()
    ),
    array(
        'title' => $speak->widget->archives,
        'content' => Widget::archive()
    )
);

foreach($ws as $w) {
    Shield::chunk('block.widget', $w);
}

?>
</aside>