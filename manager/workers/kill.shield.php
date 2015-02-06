<?php echo $messages; ?>
<form class="form-kill form-shield" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <?php if(strpos($config->url_current, 'file:') !== false): ?>
  <h3><?php echo $speak->shield . ': ' . $info->title; ?></h3>
  <p><strong><?php echo $the_shield; ?></strong> <i class="fa fa-arrow-right"></i> <?php echo str_replace(DS, ' <i class="fa fa-arrow-right"></i> ', $the_path); ?></p>
  <pre><code><?php echo Text::parse(File::open(SHIELD . DS . $the_shield . DS . $the_path)->read(), '->encoded_html'); ?></code></pre>
  <?php else: ?>
  <h3><?php echo $speak->shield . ': ' . $info->title; ?></h3>
  <?php if($files): ?>
  <ul>
    <?php foreach($files as $file): ?>
    <li><?php echo $file->path; ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <?php endif; ?>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->yes; ?></button> <a class="btn btn-reject" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/shield/<?php echo $the_shield; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->no; ?></a></p>
</form>