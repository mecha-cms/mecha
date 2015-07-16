<div class="grid-group">
  <div class="grid span-1"></div>
  <div class="grid span-5">
    <div><?php echo Form::checkbox('css_live_check', null, false, $speak->manager->title_live_preview_css); ?></div>
    <!-- div><?php echo Form::checkbox('js_live_check', null, false, $speak->manager->title_live_preview_js); ?></div -->
  </div>
</div>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->manager->title_custom_css; ?></span>
  <span class="grid span-5">
  <?php echo Form::textarea('css', Guardian::wayback('css', $default->css_raw), $speak->manager->placeholder_css, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'code'
      )
  )); ?>
  </span>
</label>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->manager->title_custom_js; ?></span>
  <span class="grid span-5">
  <?php echo Form::textarea('js', Guardian::wayback('js', $default->js_raw), $speak->manager->placeholder_js, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'code'
      )
  )); ?>
  </span>
</label>