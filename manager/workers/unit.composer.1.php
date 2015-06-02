<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->content; ?></span>
  <span class="grid span-5">
  <?php echo Form::textarea('content', Guardian::wayback('content', $default->content_raw), $speak->manager->placeholder_content, array(
      'class' => array(
          'textarea-block',
          'textarea-expand',
          'code',
          'MTE'
      ),
      'data-MTE-config' => '{"toolbar":true,"shortcut":true}'
  )); ?>
  </span>
</label>
<div class="grid-group">
  <span class="grid span-1 form-label"></span>
  <span class="grid span-5"><?php echo Form::checkbox('content_type', HTML_PARSER, Guardian::wayback('content_type', $default->content_type) === HTML_PARSER, $speak->manager->title_html_parser); ?></span>
</div>