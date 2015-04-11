<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><?php echo UI::icon('plug', 'fw') . ' ' . $speak->plugins; ?></a>
  <a class="tab" href="#tab-content-2"><?php echo UI::icon('file-archive-o', 'fw') . ' ' . $speak->upload; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <div class="tab-content" id="tab-content-1">
    <h3><?php echo Config::speak('manager.title_your_', $speak->plugins); ?></h3>
    <?php if($files): ?>
    <?php foreach($files as $plugin): $c = File::exist(PLUGIN . DS . $plugin->slug . DS . 'capture.png'); ?>
    <div class="media<?php if( ! $c): ?> no-capture<?php endif; ?>" id="plugin:<?php echo $plugin->slug; ?>">
      <?php if($c): ?>
      <div class="media-capture" style="background-image:url('<?php echo File::url($c); ?>?v=<?php echo filemtime($c); ?>');" role="image"></div>
      <?php endif; ?>
      <h4><?php echo UI::icon(File::exist(PLUGIN . DS . $plugin->slug . DS . 'pending.php') ? 'unlock-alt' : 'lock') . ' ' . $plugin->about->title; ?></h4>
      <div class="media-content">
        <p><?php echo Converter::curt($plugin->about->content); ?></p>
        <p>
          <?php if(File::exist(PLUGIN . DS . $plugin->slug . DS . 'launch.php')): ?>
          <?php echo UI::btn('begin.small', UI::icon('cog') . ' ' . $speak->manage, $config->url . '/' . $config->manager->slug . '/plugin/' . $plugin->slug); ?> <?php echo UI::btn('action.small', UI::icon('minus-circle') . ' ' . $speak->uninstall, $config->url . '/' . $config->manager->slug . '/plugin/freeze/id:' . $plugin->slug . '?o=' . $config->offset); ?>
          <?php else: ?>
          <?php if(File::exist(PLUGIN . DS . $plugin->slug . DS . 'pending.php')): ?>
          <?php echo UI::btn('action.small', UI::icon('plus-circle') . ' ' . $speak->install, $config->url . '/' . $config->manager->slug . '/plugin/fire/id:' . $plugin->slug . '?o=' . $config->offset); ?>
          <?php endif; ?>
          <?php endif; ?>
          <?php if( ! File::exist(PLUGIN . DS . $plugin->slug . DS . 'configurator.php') && ! File::exist(PLUGIN . DS . $plugin->slug . DS . 'launch.php') && ! File::exist(PLUGIN . DS . $plugin->slug . DS . 'pending.php')): ?>
          <?php echo UI::btn('destruct.small.disabled', UI::icon('times-circle') . ' ' . $speak->remove, null); ?>
          <?php else: ?>
          <?php echo UI::btn('destruct.small', UI::icon('times-circle') . ' ' . $speak->remove, $config->url . '/' . $config->manager->slug . '/plugin/kill/id:' . $plugin->slug); ?>
          <?php endif; ?>
        </p>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if( ! empty($pager->step->url)): ?>
    <p class="pager cf"><?php echo $pager->step->link; ?></p>
    <?php endif; ?>
    <?php else: ?>
    <p><?php echo Config::speak('notify_' . (Request::get('q_id') || $config->offset !== 1 ? 'error_not_found' : 'empty'), strtolower($speak->plugins)); ?></p>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <h3><?php echo Config::speak('manager.title__upload_package', $speak->plugin); ?></h3>
    <?php echo UI::uploader($config->url . '/' . $config->manager->slug . '/plugin', 'zip'); ?>
    <hr>
    <?php echo Config::speak('file:plugin'); ?>
  </div>
</div>