<?php echo $messages; ?>
<h3><?php echo $page->title; ?></h3>
<?php if($page->excerpt): ?>
<?php echo $page->excerpt; ?>
<?php else: ?>
<p><?php echo $page->description; ?></p>
<?php endif; ?>
<p><time datetime="<?php echo $page->date->W3C; ?>"><?php echo $page->date->FORMAT_3; ?></time><?php if(isset($page->total_comments_text)): ?> &middot; <strong><?php echo $page->total_comments_text; ?></strong><?php endif; ?></p>
<?php if($page->css): ?>
<pre><code><?php echo substr(Text::parse($page->css, '->encoded_html'), 0, $config->excerpt->length); ?><?php if(strlen($page->css) > $config->excerpt->length) echo ' &hellip;'; ?></code></pre>
<?php endif; ?>
<?php if($page->js): ?>
<pre><code><?php echo substr(Text::parse($page->js, '->encoded_html'), 0, $config->excerpt->length); ?><?php if(strlen($page->js) > $config->excerpt->length) echo ' &hellip;'; ?></code></pre>
<?php endif; ?>
<form class="form-kill form-<?php echo $segment; ?>" id="form-kill" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
<?php echo Jot::button('action', $speak->yes); ?> <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/' . $segment . '/repair/id:' . $page->id); ?>
<?php echo Form::hidden('token', $token); ?>
</form>