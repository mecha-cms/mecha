<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->content; ?></span>
  <span class="grid span-5">
    <textarea name="content" class="textarea-block code MTE" placeholder="<?php echo $speak->manager->placeholder_content; ?>" data-MTE-config='{"toolbar":true,"shortcut":true}'><?php echo Text::parse(Guardian::wayback('content', $default->content_raw), '->encoded_html'); ?></textarea>
  </span>
</label>
<div class="grid-group">
  <span class="grid span-1 form-label"></span>
  <span class="grid span-5"><label><input name="content_type" type="checkbox" value="<?php echo HTML_PARSER; ?>"<?php echo Guardian::wayback('content_type', $default->content_type) == HTML_PARSER ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_html_parser; ?></span></label></span>
</div>