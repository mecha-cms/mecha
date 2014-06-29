<?php if($config->action_state != 'ignite' && $config->file_type != 'page'): ?>
<?php if($cache['status'] == 'published'): ?>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->date; ?></span>
  <span class="grid span-5"><input name="date" type="text" class="input-block" value="<?php echo $cache['date']; ?>" placeholder="0000-00-00T00:00:00+00:00"></span>
</label>
<?php endif; ?>
<?php endif; ?>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->title; ?></span>
  <span class="grid span-5"><input name="title" type="text" class="input-block" value="<?php echo Text::parse($cache['title'])->to_encoded_html; ?>" placeholder="<?php echo $speak->manager->placeholder_title; ?>"></span>
</label>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->slug; ?></span>
  <span class="grid span-5"><input name="slug" type="text" class="input-block" value="<?php echo $cache['slug']; ?>" placeholder="<?php echo Text::parse($speak->manager->placeholder_title)->to_slug; ?>"></span>
</label>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->content; ?></span>
  <span class="grid span-5"><textarea name="content" class="textarea-block code" placeholder="<?php echo $speak->manager->placeholder_content; ?>" data-mte-languages='<?php echo Text::parse($speak->MTE)->to_encoded_json; ?>'><?php echo Text::parse($cache['content'])->to_encoded_html; ?></textarea></span>
</label>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->description; ?></span>
  <span class="grid span-5"><textarea name="description" class="textarea-block" placeholder="<?php echo $speak->manager->placeholder_description; ?>"><?php echo Text::parse($cache['description'])->to_encoded_html; ?></textarea></span>
</label>
<?php

$tags = array();

foreach(Get::tags() as $tag) {
    if($tag && $tag->id !== 0) {
        $tags[] = '<div><label><input type="checkbox" name="tags[]" value="' . $tag->id . '"' . (in_array($tag->id, $cache['tags']) ? ' checked' : "") . '> <span>' . $tag->name . '</span></label></div>';
    }
}

?>
<?php if($config->file_type == 'article' && count($tags) > 1): ?>
<div class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->tags; ?></span>
  <span class="grid span-5"><?php echo implode("", $tags); ?></span>
</div>
<?php endif; ?>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->author; ?></span>
  <span class="grid span-5"><input name="author" type="text" value="<?php echo $cache['author']; ?>"></span>
</label>
<?php if($config->file_type == 'page'): ?>
<input name="date" type="hidden" value="<?php echo $cache['date']; ?>">
<?php endif; ?>