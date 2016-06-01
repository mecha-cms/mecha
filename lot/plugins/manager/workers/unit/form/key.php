<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->key; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('key', Request::get('key', Guardian::wayback('key', $page->key_raw)), Text::parse($speak->manager->placeholder_title, '->array_key', true), array(
      'class' => 'input-block',
      'pattern' => '[a-z_][a-z0-9_]*'
  )); ?>
  </span>
</label>