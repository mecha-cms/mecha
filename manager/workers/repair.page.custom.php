<div class="grid-group">
  <div class="grid span-1"></div>
  <div class="grid span-5">
    <div><label><input name="css_live_check" type="checkbox"> <span><?php echo $speak->manager->title_live_preview_css; ?></span></label></div>
    <!-- div><label><input name="js_live_check" type="checkbox"> <span><?php echo $speak->manager->title_live_preview_js; ?></span></label></div -->
  </div>
</div>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->manager->title_custom_css; ?></span>
  <span class="grid span-5"><textarea name="css" class="input-block code"><?php echo $cache['css']; ?></textarea></span>
</label>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->manager->title_custom_js; ?></span>
  <span class="grid span-5"><textarea name="js" class="input-block code"><?php echo $cache['js']; ?></textarea></span>
</label>