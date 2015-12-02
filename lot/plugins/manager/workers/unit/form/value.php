<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->value; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('value', Converter::str(Request::get('value', Guardian::wayback('value', $page->value))), null, array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>