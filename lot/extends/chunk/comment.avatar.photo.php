<?php if($avatar = File::exist(ASSET . DS . '__avatar' . DS . '60x60' . DS . md5($comment->email) . '.png')): ?>
<?php echo Asset::image($avatar, ' alt="" width="60" height="60"'); ?>
<?php else: ?>
<?php echo Asset::image($config->protocol . 'www.gravatar.com/avatar/' . md5($comment->email) . '?s=60&amp;d=monsterid', ' alt="" width="60" height="60"'); ?>
<?php endif; ?>