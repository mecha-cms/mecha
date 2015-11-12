<div class="grid-group">
  <div class="grid span-1"></div>
  <div class="grid span-5">
    <div><?php echo Form::checkbox('css_live_check', null, false, $speak->manager->title_live_preview_css); ?></div>
    <!-- div><?php echo Form::checkbox('js_live_check', null, false, $speak->manager->title_live_preview_js); ?></div -->
  </div>
</div>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->manager->title_css_custom; ?></span>
  <span class="grid span-5">
  <?php echo Form::textarea('css', Guardian::wayback('css', $page->css_raw), $speak->manager->placeholder_css_custom, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'code'
      )
  )); ?>
  </span>
</label>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->manager->title_js_custom; ?></span>
  <span class="grid span-5">
  <?php echo Form::textarea('js', Guardian::wayback('js', $page->js_raw), $speak->manager->placeholder_js_custom, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'code'
      )
  )); ?>
  </span>
</label>