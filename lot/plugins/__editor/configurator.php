<?php $c = $config->states->{'plugin_' . md5(File::B(__DIR__))};; ?>
<div class="p">
  <?php foreach($speak->MTE->buttons as $k => $v): ?>
  <?php if(Text::check($k)->in(array('yes', 'no', 'ok', 'cancel', 'open', 'close'))) continue; ?>
  <div><?php echo Form::checkbox('buttons[' . $k . ']', 1, isset($c->buttons->{$k}), sprintf($speak->manager->title_show__button, $v)); ?></div>
  <?php endforeach; ?>
</div>
<p><?php echo Jot::button('action', $speak->update); ?></p>