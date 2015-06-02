<div class="main-action-group">
  <?php echo Jot::btn('begin:plus-square', Config::speak('manager.title_new_', $speak->shield), $config->manager->slug . '/shield/' . $the_shield_folder . '/ignite'); ?>
</div>
<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><?php echo Jot::icon('shield', 'fw') . ' ' . $speak->shield; ?></a>
  <a class="tab" href="#tab-content-2"><?php echo Jot::icon('file-archive-o', 'fw') . ' ' . $speak->upload; ?></a>
  <?php if(count($the_shield_folders) > 1): ?>
  <a class="tab" href="#tab-content-3"><?php echo Jot::icon('wrench', 'fw') . ' ' . $speak->manage; ?></a>
  <?php endif; ?>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <div class="tab-content" id="tab-content-1">
    <?php if($files): ?>
    <h3><?php echo $speak->shield . ': ' . $the_shield_info->title; ?></h3>
    <p><?php echo Cell::strong($speak->author . ':'); ?> <?php echo Text::parse($the_shield_info->author, '->encoded_html'); ?><?php if(isset($the_shield_info->url) && $the_shield_info->url != '#'): ?><br><?php echo Cell::strong($speak->url . ':'); ?> <?php echo Cell::a($the_shield_info->url, $the_shield_info->url, '_blank', array(
        'rel' => 'nofollow'
    )); ?><?php endif; ?></p>
    <?php endif; ?>
    <?php

    $b_path = SHIELD . DS . $the_shield_folder . DS;

    $b_url = $config->manager->slug . '/shield/' . $the_shield_folder;
    $b_url_kill = $b_url . '/kill/file:';
    $b_url_repair = $b_url . '/repair/file:';

    include DECK . DS . 'workers' . DS . 'unit.explorer.1.php';

    ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <h3><?php echo Config::speak('manager.title__upload_package', $speak->shield); ?></h3>
    <?php echo Jot::uploader($config->manager->slug . '/shield', 'zip'); ?>
    <hr>
    <?php echo Config::speak('file:shield'); ?>
  </div>
  <?php if(count($the_shield_folders) > 1): ?>
  <div class="tab-content hidden" id="tab-content-3">
    <h3><?php echo Config::speak('manager.title_your_', $speak->shields); ?></h3>
    <?php foreach($the_shield_folders as $folder): $folder = basename($folder); ?>
    <?php $c = File::exist(SHIELD . DS . $folder . DS . 'capture.png'); if($config->shield !== $folder && strpos($folder, '__') !== 0): $info = Shield::info($folder); ?>
    <div class="media<?php if( ! $c): ?> no-capture<?php endif; ?>" id="shield:<?php echo $folder; ?>">
      <?php if($c): ?>
      <div class="media-capture" style="background-image:url('<?php echo File::url($c); ?>?v=<?php echo filemtime($c); ?>');" role="image"></div>
      <?php endif; ?>
      <h4 class="media-title"><?php echo Jot::icon('shield') . ' ' . $info->title; ?></h4>
      <div class="media-content">
        <p><?php echo Converter::curt($info->content); ?></p>
        <p>
          <?php echo Jot::btn('construct.small:cog', $speak->manage, $config->manager->slug . '/shield/' . $folder); ?> <?php if(File::exist(SHIELD . DS . $folder . DS . 'manager.php')): ?><?php echo Jot::btn('action.small:shield', $speak->attach, $config->manager->slug . '/shield/attach/id:' . $folder); ?> <?php endif; ?><?php echo Jot::btn('destruct.small:times-circle', $speak->delete, $config->manager->slug . '/shield/kill/id:' . $folder); ?>
        </p>
      </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>