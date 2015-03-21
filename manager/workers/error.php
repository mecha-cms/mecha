<?php echo $messages; ?>
<?php if($the_content): ?>
<p><textarea class="textarea-block textarea-expand code"><?php echo Text::parse($the_content, '->encoded_html'); ?></textarea></p>
<p><a class="btn btn-destruct" href="<?php echo $config->url_current; ?>/kill"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a></p>
<?php endif; ?>