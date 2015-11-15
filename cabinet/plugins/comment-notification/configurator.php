<form class="form-plugin" action="<?php echo $config->url_current; ?>/update" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <?php $cn_config = File::open(__DIR__ . DS . 'states' . DS . 'config.txt')->unserialize(); ?>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->subject; ?></span>
    <span class="grid span-5"><?php echo Form::text('subject', $cn_config['subject'], null, array('class' => 'input-block')); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->message; ?></span>
    <span class="grid span-5"><?php echo Form::textarea('message', $cn_config['message'], null, array('class' => 'textarea-block')); ?></span>
  </label>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <span class="grid span-5"><?php echo Jot::button('action', $speak->update); ?></span>
  </div>
</form>