<?php $hooks = array($page, $segment); echo $messages; ?>
<h3><?php echo $page->title; ?></h3>
<?php if($page->excerpt): ?>
<?php echo $page->excerpt; ?>
<?php else: ?>
<p><?php echo $page->description; ?></p>
<?php endif; ?>
<p><time class="text-fade" datetime="<?php echo $page->date->W3C; ?>"><?php echo Jot::icon('clock-o') . ' ' . $page->date->FORMAT_3; ?></time><?php if(isset($page->total_comments)): $t = Jot::icon('comments') . ' ' . $page->total_comments; ?> &middot; <?php echo $page->total_comments === 0 ? Cell::span($t) : Cell::a($config->manager->slug . '/comment?filter=post%3A' . $page->id, $t); ?><?php endif; ?></p>
<?php if($page->css): ?>
<pre><code><?php echo substr(Text::parse($page->css, '->encoded_html'), 0, $config->excerpt->length); ?><?php if(strlen($page->css) > $config->excerpt->length) echo $config->excerpt->suffix; ?></code></pre>
<?php endif; ?>
<?php if($page->js): ?>
<pre><code><?php echo substr(Text::parse($page->js, '->encoded_html'), 0, $config->excerpt->length); ?><?php if(strlen($page->js) > $config->excerpt->length) echo $config->excerpt->suffix; ?></code></pre>
<?php endif; ?>
<form class="form-kill form-<?php echo $segment; ?>" id="form-kill" action="<?php echo $config->url_current . str_replace('&', '&amp;', $config->url_query); ?>" method="post">
  <?php Weapon::fire('action_before', $hooks); ?>
  <?php echo Jot::button('action', $speak->yes); ?>
  <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/' . $segment . '/repair/id:' . $page->id); ?>
  <?php Weapon::fire('action_after', $hooks); ?>
  <?php echo Form::hidden('token', $token); ?>
</form>