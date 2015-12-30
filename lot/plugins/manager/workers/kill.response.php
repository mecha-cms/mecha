<?php echo $messages; ?>
<?php $post = call_user_func('Get::' . $segment[1] . 'Anchor', $page->post); ?>
<h3><?php echo $page->name; ?><?php echo $post ? ' &raquo; <a href="' . $page->permalink . '" target="_blank">' . $post->title . '</a>' : ""; ?></h3>
<?php echo $page->message; ?>
<p><time class="text-fade" datetime="<?php echo $page->date->W3C; ?>"><?php echo Jot::icon('clock-o') . ' ' . $page->date->FORMAT_3; ?></time></p>
<form class="form-kill form-<?php echo $segment[0]; ?>" id="form-kill" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
<?php echo Jot::button('action', $speak->yes); ?> <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/' . $segment[0] . '/repair/id:' . $page->id); ?>
<?php echo Form::hidden('token', $token); ?>
</form>