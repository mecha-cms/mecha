<?php $hooks = array($page, $segment); echo $messages; ?>
<h3><?php echo $speak->shield . ': ' . $page->title; ?></h3>
<?php if(strpos($config->url_path, '/kill/file:') !== false): ?>
<p><?php echo Cell::strong($folder) . ' ' . Jot::icon('arrow-right') . ' ' . str_replace(DS, ' ' . Jot::icon('arrow-right') . ' ', $path); ?></p>
<pre><code><?php echo Text::parse(File::open(SHIELD . DS . $folder . DS . $path)->read(), '->encoded_html'); ?></code></pre>
<?php else: ?>
<?php if($files): ?>
<ul>
  <?php foreach($files as $file): ?>
  <li><?php echo $file->path; ?></li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
<?php endif; ?>
<form class="form-kill form-shield" id="form-kill" action="<?php echo $config->url_current . str_replace('&', '&amp;', $config->url_query); ?>" method="post">
  <?php Weapon::fire('action_before', $hooks); ?>
  <?php echo Jot::button('action', $speak->yes); ?>
  <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/shield/' . $folder); ?>
  <?php Weapon::fire('action_after', $hooks); ?>
  <?php echo Form::hidden('token', $token); ?>
</form>