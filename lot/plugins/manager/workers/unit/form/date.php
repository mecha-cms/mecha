<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->date; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('date', Request::get('date', Guardian::wayback('date', $page->date->W3C)), date('c'), array(
      'class' => 'input-block',
      'pattern' => '\\d{4,}-\\d{2}-\\d{2}T\\d{2}:\\d{2}:\\d{2}\\+\\d{2}:\\d{2}'
  )); ?>
  </span>
</label>