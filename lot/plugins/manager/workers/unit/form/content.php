<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->content; ?></span>
  <span class="grid span-5">
  <?php echo Form::textarea('content', Request::get('content', Guardian::wayback('content', $page->content_raw)), $speak->manager->placeholder_content, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'MTE',
          'code'
      ),
      'data-MTE-config' => '{"toolbar":true,"shortcut":true}'
  )); ?>
  </span>
</label>