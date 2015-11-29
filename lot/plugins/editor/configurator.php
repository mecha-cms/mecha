<form class="form-plugin" action="<?php echo $config->url_current; ?>/update" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <?php $c = File::open(__DIR__ . DS . 'states' . DS . 'config.txt')->unserialize(); ?>
  <div class="p">
    <?php foreach($speak->MTE->buttons as $k => $v): ?>
    <?php if(Text::check($k)->in(array('yes', 'no', 'ok', 'cancel', 'open', 'close'))) continue; ?>
    <div><?php echo Form::checkbox('buttons[' . $k . ']', 1, isset($c['buttons'][$k]), sprintf($speak->manager->title_show__button, $v)); ?></div>
    <?php endforeach; ?>
  </div>
  <p><?php echo Jot::button('action', $speak->update); ?></p>
</form>