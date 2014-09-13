<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->content; ?></span>
  <span class="grid span-5">
    <textarea name="content" class="textarea-block code MTE" placeholder="<?php echo $speak->manager->placeholder_content; ?>" data-mte-languages='<?php echo Text::parse($speak->MTE)->to_encoded_json; ?>'><?php echo Text::parse(Guardian::wayback('content', $default->content_raw))->to_encoded_html; ?></textarea>
  </span>
</label>