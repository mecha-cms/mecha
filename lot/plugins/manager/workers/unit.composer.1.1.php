<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->date; ?></span>
  <span class="grid span-5">
  <?php echo Form::text('date', Guardian::wayback('date', $page->date->W3C), date('c'), array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>