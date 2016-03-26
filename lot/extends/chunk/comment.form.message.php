<div class="grid-group">
  <?php $_ = 'unit:' . time(); ?>
  <label class="grid span-1 form-label" for="<?php echo $_; ?>"><?php echo $speak->message; ?></label>
  <div class="grid span-5"><?php echo Form::textarea('message', Guardian::wayback('message'), null, array('class' => 'textarea-block', 'id' => $_)); ?></div>
</div>