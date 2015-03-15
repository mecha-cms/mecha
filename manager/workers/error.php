<?php echo $messages; ?>
<?php if($errors = File::exist(SYSTEM . DS . 'log' . DS . 'errors.log')): ?>
<pre><code><?php echo File::open($errors)->read(); ?></code></pre>
<p><a class="btn btn-destruct" href="<?php echo $config->url_current; ?>/kill"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a></p>
<?php else: ?>
<p class="empty"><?php echo Config::speak('notify_empty', array(strtolower($speak->errors))); ?></p>
<?php endif; ?>