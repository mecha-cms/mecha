<?php echo Notify::read(); ?>
<?php $qs = Request::get('shield') ? '?shield=' . Request::get('shield') : ""; $shield = Request::get('shield') ? Request::get('shield') : $config->shield; ?>
<form class="form-kill form-shield" action="<?php echo $config->url_current . $qs; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <?php if(strpos($config->url_current, 'file:') !== false): ?>
  <h3><?php echo $speak->shield . ': ' . Shield::info($shield)->name; ?></h3>
  <p><strong><?php echo $shield; ?></strong> <i class="fa fa-arrow-right"></i> <?php echo str_replace(DS, ' <i class="fa fa-arrow-right"></i> ', $config->name); ?></p>
  <pre><code class="html"><?php echo Text::parse(File::open(SHIELD . DS . $shield . DS . $config->name)->read())->to_encoded_html; ?></code></pre>
  <?php else: ?>
  <h3><?php $info = Shield::info($config->name); echo $speak->shield . ': ' . $info->name; ?></h3>
  <ul>
    <?php foreach(Get::files(SHIELD . DS . $shield, '*') as $file): ?>
    <li><?php echo $file['path']; ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->yes; ?></button> <a class="btn btn-reject" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/shield<?php echo $qs; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->no; ?></a></p>
</form>