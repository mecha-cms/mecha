<?php echo $messages; ?>
<form class="form-kill form-shield" id="form-kill" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
  <h3><?php echo $speak->shield . ': ' . $the_info->title; ?></h3>
  <?php if(strpos($config->url_path, '/kill/file:') !== false): ?>
  <p><?php echo Cell::strong($the_shield) . ' ' . Jot::icon('arrow-right') . ' ' . str_replace(DS, ' ' . Jot::icon('arrow-right') . ' ', $the_name); ?></p>
  <pre><code><?php echo Text::parse(File::open(SHIELD . DS . $the_shield . DS . $the_name)->read(), '->encoded_html'); ?></code></pre>
  <?php else: ?>
  <?php if($files): ?>
  <ul>
    <?php foreach($files as $file): ?>
    <li><?php echo $file->path; ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <?php endif; ?>
  <p>
  <?php echo Jot::button('action', $speak->yes); ?>
  <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/shield/' . $the_shield); ?>
  </p>
  <?php echo Form::hidden('token', $token); ?>
</form>