<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->manager->title_js_custom; ?></span>
  <span class="grid span-5">
  <?php echo Form::textarea('js', Request::get('js', Guardian::wayback('js', $page->js_raw)), $speak->manager->placeholder_js_custom, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'code'
      )
  )); ?>
  </span>
</label>