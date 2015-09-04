<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->title; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('title', Guardian::wayback('title', $default->title), $speak->manager->placeholder_title, array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->slug; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('slug', Guardian::wayback('slug', $default->slug), Text::parse($speak->manager->placeholder_title, '->slug'), array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>
<?php if($default->link): ?>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->link; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('link', Guardian::wayback('link', $default->link), $config->protocol, array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>
<?php endif; ?>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->content; ?></span>
  <span class="grid span-5">
  <?php echo Form::textarea('content', Guardian::wayback('content', $default->content_raw), $speak->manager->placeholder_content, array(
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
<div class="grid-group">
  <span class="grid span-1 form-label"></span>
  <span class="grid span-5"><?php echo Form::checkbox('content_type', HTML_PARSER, Guardian::wayback('content_type', $default->content_type) === HTML_PARSER, $speak->manager->title_html_parser); ?></span>
</div>