<?php echo $messages; ?>
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
<p><?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/error/kill'); ?></p>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', strtolower($speak->errors)); ?></p>
<?php endif; ?>