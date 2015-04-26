<div class="main-action-group">
  <?php echo Jot::btn('begin:plus-square', Config::speak('manager.title_new_', $speak->shield), $config->manager->slug . '/shield/' . $the_shield_path . '/ignite'); ?>
</div>
<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><?php echo Jot::icon('shield', 'fw') . ' ' . $speak->shield; ?></a>
  <a class="tab" href="#tab-content-2"><?php echo Jot::icon('file-archive-o', 'fw') . ' ' . $speak->upload; ?></a>
  <?php if(count($the_shields) > 1): ?>
  <a class="tab" href="#tab-content-3"><?php echo Jot::icon('wrench', 'fw') . ' ' . $speak->manage; ?></a>
  <?php endif; ?>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <div class="tab-content" id="tab-content-1">
    <?php if($the_shield_contents): ?>
    <h3><?php echo $speak->shield . ': ' . $info->title; ?></h3>
    <p><?php echo Cell::strong($speak->author . ':'); ?> <?php echo Text::parse($info->author, '->encoded_html'); ?><?php if(isset($info->url) && $info->url != '#'): ?><br><?php echo Cell::strong($speak->url . ':'); ?> <?php echo Cell::a($info->url, $info->url, '_blank', array(
        'rel' => 'nofollow'
    )); ?><?php endif; ?></p>
    <table class="table-bordered table-full-width">
      <tbody>
        <?php foreach($the_shield_contents as $file): $the_shield_url = File::url(str_replace(SHIELD . DS . $the_shield_path . DS, "", $file['path'])); ?>
        <tr>
          <td><?php echo strpos($the_shield_url, '/') !== false ? Jot::span('fade', dirname($the_shield_url) . '/') . basename($the_shield_url) : $the_shield_url; ?></td>
          <td class="td-icon">
          <?php echo Jot::a('construct', $config->manager->slug . '/shield/' . $the_shield_path . '/repair/file:' . $the_shield_url, Jot::icon('pencil'), array(
              'title' => $speak->edit
          )); ?>
          </td>
          <td class="td-icon">
          <?php echo Jot::a('destruct', $config->manager->slug . '/shield/' . $the_shield_path . '/kill/file:' . $the_shield_url, Jot::icon('times'), array(
              'title' => $speak->delete
          )); ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <?php if(File::exist(SHIELD . DS . $the_shield_path)): ?>
    <p><?php echo Config::speak('notify_empty', strtolower($speak->shields)); ?></p>
    <?php else: ?>
    <p><?php echo $speak->notify_error_not_found; ?></p>
    <?php endif; ?>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <h3><?php echo Config::speak('manager.title__upload_package', $speak->shield); ?></h3>
    <?php echo Jot::uploader($config->manager->slug . '/shield', 'zip'); ?>
    <hr>
    <?php echo Config::speak('file:shield'); ?>
  </div>
  <?php if(count($the_shields) > 1): ?>
  <div class="tab-content hidden" id="tab-content-3">
    <h3><?php echo Config::speak('manager.title_your_', $speak->shields); ?></h3>
    <?php foreach($the_shields as $shield): $shield = basename($shield); ?>
    <?php $c = File::exist(SHIELD . DS . $shield . DS . 'capture.png'); if($config->shield != $shield && strpos($shield, '__') !== 0): $info = Shield::info($shield); ?>
    <div class="media<?php if( ! $c): ?> no-capture<?php endif; ?>" id="shield:<?php echo $shield; ?>">
      <?php if($c): ?>
      <div class="media-capture" style="background-image:url('<?php echo File::url($c); ?>?v=<?php echo filemtime($c); ?>');" role="image"></div>
      <?php endif; ?>
      <h4 class="media-title"><?php echo Jot::icon('shield') . ' ' . $info->title; ?></h4>
      <div class="media-content">
        <p><?php echo Converter::curt($info->content); ?></p>
        <p>
          <?php echo Jot::btn('construct.small:cog', $speak->manage, $config->manager->slug . '/shield/' . $shield); ?> <?php if(File::exist(SHIELD . DS . $shield . DS . 'manager.php')): ?><?php echo Jot::btn('action.small:shield', $speak->attach, $config->manager->slug . '/shield/attach/id:' . $shield); ?> <?php endif; ?><?php echo Jot::btn('destruct.small:times-circle', $speak->delete, $config->manager->slug . '/shield/kill/id:' . $shield); ?>
        </p>
      </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>