<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->description; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('description', Converter::toText(Request::get('description', Guardian::wayback('description', $page->description))), Config::speak('manager.placeholder_description_', strtolower($speak->{$segment})), array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>