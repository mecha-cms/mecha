<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->hint; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('placeholder', Converter::toText(Request::get('placeholder', Guardian::wayback('placeholder', $page->placeholder))), null, array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>