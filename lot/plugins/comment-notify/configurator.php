<?php $c_comment_notify = $config->states->{'plugin_' . md5(File::B(__DIR__))}; ?>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->subject; ?></span>
  <span class="grid span-5"><?php echo Form::text('subject', $c_comment_notify->subject, null, array('class' => 'input-block')); ?></span>
</label>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->message; ?></span>
  <span class="grid span-5"><?php echo Form::textarea('message', $c_comment_notify->message, null, array('class' => 'textarea-block')); ?></span>
</label>