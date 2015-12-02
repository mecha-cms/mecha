<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->key; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('key', Request::get('key', Guardian::wayback('key', $page->key_raw)), null, array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>