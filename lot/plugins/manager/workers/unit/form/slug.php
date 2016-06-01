<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->slug; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('slug', Request::get('slug', Guardian::wayback('slug', $page->slug)), Text::parse($speak->manager->placeholder_title, '->slug'), array(
      'class' => 'input-block',
      'pattern' => '[a-z0-9\\-]+|(https?:)?\\/\\/.+'
  )); ?>
  </span>
</label>