<?php $attr = ' alt="" width="60" height="60"'; ?>
<?php if($avatar = File::exist(ASSET . DS . '__avatar' . DS . '60x60' . DS . md5($comment->email) . '.png')): ?>
<?php echo Asset::image($avatar, $attr); ?>
<?php elseif($avatar = File::exist(ASSET . DS . '__avatar' . DS . md5($comment->email) . '.png')): ?>
<?php echo Asset::image($avatar, $attr); ?>
<?php else: ?>
<?php echo Asset::image($config->protocol . 'www.gravatar.com/avatar/' . md5($comment->email) . '?s=60&amp;d=monsterid', $attr); ?>
<?php endif; ?>