<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->email; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('email', Request::get('email', Guardian::wayback('email', $page->email)), null, array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>