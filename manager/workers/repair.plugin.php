<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><i class="fa fa-cog"></i> <?php echo $speak->config; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-user"></i> <?php echo $speak->about; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <div class="tab-content" id="tab-content-1">
  <?php if($file->configurator): ?>
  <?php include $file->configurator; ?>
  <?php else: ?>
  <p><?php echo Config::speak('notify_not_available', array($speak->config)); ?></p>
  <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <div class="plugin-about">
      <p class="plugin-author"><strong><?php echo $speak->author; ?>:</strong> <?php echo Text::parse($file->author)->to_encoded_html; ?><?php if(isset($file->url) && $file->url != '#'): ?> <a class="help" href="<?php echo $file->url; ?>" title="<?php echo $speak->link; ?>" rel="nofollow" target="_blank"><i class="fa fa-external-link-square"></i></a><?php endif; ?></p>
      <h3 class="plugin-title"><?php echo $file->title; if(isset($file->version)) echo ' ' . $file->version; ?></h3>
      <div class="plugin-description">
        <?php echo $file->content; ?>
        <p><a href="<?php echo $config->url . '/' . $config->manager->slug . '/plugin/' . $the_plugin_path; ?>/backup" title="<?php echo $speak->download . ' ' . strtolower($speak->as) . ' `' . $the_plugin_path . '.zip`'; ?>"><i class="fa fa-cloud-download"></i> <?php echo $the_plugin_path; ?>.zip</a></p>
      </div>
    </div>
  </div>
</div>