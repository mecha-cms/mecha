<?php echo $messages; ?>
<h3><?php echo $page->title; ?></h3>
<p><?php echo $page->description; ?></p>
<?php if( ! empty($page->css)): ?>
<pre><code><?php echo substr(Text::parse($page->css, '->encoded_html'), 0, $config->excerpt->length); ?><?php if(strlen($page->css) > $config->excerpt->length) echo ' &hellip;'; ?></code></pre>
<?php endif; ?>
<?php if( ! empty($page->js)): ?>
<pre><code><?php echo substr(Text::parse($page->js, '->encoded_html'), 0, $config->excerpt->length); ?><?php if(strlen($page->js) > $config->excerpt->length) echo ' &hellip;'; ?></code></pre>
<?php endif; ?>
<form class="form-kill form-page" id="form-kill" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
<?php echo Jot::button('action', $speak->yes); ?> <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/page/repair/id:' . $page->id); ?>
<?php echo Form::hidden('token', $token); ?>
</form>