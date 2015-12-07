<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->id; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('id', Request::get('id', Guardian::wayback('id', $page->id_raw))); ?>
  </span>
</label>