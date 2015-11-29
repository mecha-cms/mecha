<?php Weapon::fire('unit_composer_1_before', $hooks); ?>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->title; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('title', Guardian::wayback('title', $page->title), $speak->manager->placeholder_title, array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->slug; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('slug', Guardian::wayback('slug', $page->slug), Text::parse($speak->manager->placeholder_title, '->slug'), array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>
<?php if($page->link): ?>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->link; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('link', Guardian::wayback('link', $page->link), $config->protocol, array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>
<?php endif; ?>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->content; ?></span>
  <span class="grid span-5">
  <?php echo Form::textarea('content', Guardian::wayback('content', $page->content_raw), $speak->manager->placeholder_content, array(
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
  <span class="grid span-5"><?php echo Form::checkbox('content_type', $config->html_parser !== false ? $config->html_parser : 'HTML', Guardian::wayback('content_type', $page->content_type) !== (strpos($config->url_path, '/id:') === false ? false : 'HTML'), $speak->manager->title_html_parser); ?></span>
</div>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->description; ?></span>
  <span class="grid span-5">
  <?php echo Form::textarea('description', Guardian::wayback('description', $page->description), Config::speak('manager.placeholder_description_', strtolower($speak->{$segment})), array(
      'class' => 'textarea-block'
  )); ?>
  </span>
</label>
<?php Weapon::fire('unit_composer_1_after', $hooks); ?>