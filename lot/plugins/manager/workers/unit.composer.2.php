<?php Weapon::fire('unit_composer_2_before', $hooks); ?>
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
<?php Weapon::fire('unit_composer_2_after', $hooks); ?>