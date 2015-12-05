<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->message; ?></span>
  <span class="grid span-5"><?php echo Form::textarea('message', Guardian::wayback('message'), null, array('class' => 'textarea-block')); ?></span>
</label>