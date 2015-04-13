<?php echo $messages; ?>
<form class="form-kill form-shield" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <h3><?php echo $speak->shield . ': ' . $info->title; ?></h3>
  <?php if(strpos($config->url_current, 'file:') !== false): ?>
  <p><?php echo Cell::strong($the_shield) . ' ' . UI::icon('arrow-right') . ' ' . str_replace(DS, ' ' . UI::icon('arrow-right') . ' ', $the_path); ?></p>
  <pre><code><?php echo Text::parse(File::open(SHIELD . DS . $the_shield . DS . $the_path)->read(), '->encoded_html'); ?></code></pre>
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
  <?php echo UI::button('action', $speak->yes); ?>
  <?php echo UI::btn('reject', $speak->no, $config->manager->slug . '/shield/' . $the_shield); ?>
  </p>
</form>