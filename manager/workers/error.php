<?php echo $messages; ?>
<?php if($the_content): ?>
<p>
<?php echo Form::textarea('content', $the_content, null, array(
    'class' => array(
        'textarea-block',
        'textarea-expand',
        'code'
    )
)); ?>
</p>
<p><?php echo Jot::btn('destruct', $speak->delete, $config->url_current . '/kill'); ?></p>
<?php endif; ?>