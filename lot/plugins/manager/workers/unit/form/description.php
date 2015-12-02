<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->description; ?></span>
  <span class="grid span-5">
  <?php echo Form::textarea('description', Request::get('description', Guardian::wayback('description', $page->description_raw)), Config::speak('manager.placeholder_description_', strtolower($speak->{$segment})), array(
      'class' => 'textarea-block'
  )); ?>
  </span>
</label>