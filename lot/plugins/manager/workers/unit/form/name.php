<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->name; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('name', Request::get('name', Guardian::wayback('name', $page->name_raw)), null, array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>