<?php

$tags = array();
$tags_wayback = ',' . Request::get('kind', implode(',', Guardian::wayback('kind', Mecha::A($page->kind)))) . ',';
foreach(call_user_func('Get::' . $segment . 'Tags') as $tag) {
    if($tag && $tag->id !== 0) {
        $tags[] = '<div>' . Form::checkbox('kind[]', $tag->id, strpos($tags_wayback, ',' . $tag->id . ',') !== false, $tag->name) . '</div>';
    }
}

?>
<?php if(count($tags) > 0): ?>
<div class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->tags; ?></span>
  <div class="grid span-5"><?php echo implode("", $tags); ?></div>
</div>
<?php endif; ?>