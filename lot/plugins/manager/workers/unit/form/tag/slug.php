<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->slug; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('slug', Request::get('slug', Guardian::wayback('slug', $page->slug_raw)), null, array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>