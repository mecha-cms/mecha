<?php $hooks = array($page, $segment); echo $messages; ?>
<?php if($content): ?>
<p>
<?php echo Form::textarea('content', Request::get('content', Guardian::wayback('content', $content)), $speak->manager->placeholder_content, array(
    'class' => array(
        'textarea-block',
        'textarea-expand',
        'code'
    )
)); ?>
</p>
<p>
  <?php Weapon::fire('action_before', $hooks); ?>
  <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/error/do:kill'); ?>
  <?php Weapon::fire('action_after', $hooks); ?>
</p>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', strtolower($speak->errors)); ?></p>
<p>
  <?php Weapon::fire('action_before', $hooks); ?>
  <?php Weapon::fire('action_after', $hooks); ?>
</p>
<?php endif; ?>