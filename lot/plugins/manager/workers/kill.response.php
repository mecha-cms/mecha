<?php echo $messages; ?>
<?php $post = call_user_func('Get::' . $segment[1] . 'Anchor', $page->post); ?>
<h3><?php echo $page->name; ?><?php echo $post ? ' ' . strtolower($speak->to) . ' <a href="' . $page->permalink . '" target="_blank">' . $post->title . '</a>' : ""; ?></h3>
<p><strong><?php echo $speak->date; ?>:</strong> <?php echo $page->date->FORMAT_3; ?></p>
<?php echo $page->message; ?>
<form class="form-kill form-<?php echo $segment[0]; ?>" id="form-kill" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
<?php echo Jot::button('action', $speak->yes); ?> <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/' . $segment[0] . '/repair/id:' . $page->id); ?>
<?php echo Form::hidden('token', $token); ?>
</form>