<?php $hooks = array($page, $segment); ?>
<div class="main-action-group">
  <?php Weapon::fire('main_action_before', $hooks); ?>
  <?php echo Jot::btn('begin:plus-square', Config::speak('manager.title_new_', $speak->shield), $config->manager->slug . '/shield/' . $folder . '/ignite'); ?>
  <?php Weapon::fire('main_action_after', $hooks); ?>
</div>
<div class="tab-area">
  <div class="tab-button-area">
    <?php Weapon::fire('tab_button_before', $hooks); ?>
    <a class="tab-button active" href="#tab-content-1"><?php echo Jot::icon('shield', 'fw') . ' ' . $speak->shield; ?></a>
    <a class="tab-button" href="#tab-content-2"><?php echo Jot::icon('file-archive-o', 'fw') . ' ' . $speak->upload; ?></a>
    <?php if(count($folders) > 1): ?>
    <a class="tab-button" href="#tab-content-3"><?php echo Jot::icon('wrench', 'fw') . ' ' . $speak->manage; ?></a>
    <?php endif; ?>
    <?php Weapon::fire('tab_button_after', $hooks); ?>
  </div>
  <div class="tab-content-area">
    <?php echo $messages; ?>
    <?php Weapon::fire('tab_content_before', $hooks); ?>
    <div class="tab-content tab-area" id="tab-content-1">
      <div class="tab-button-area">
        <?php Weapon::fire('tab_button_before', $hooks); ?>
        <a class="tab-button active" href="#tab-content-1-1"><?php echo Jot::icon('file-code-o', 'fw') . ' ' . $speak->file; ?></a>
        <a class="tab-button" href="#tab-content-1-2"><?php echo Jot::icon('user', 'fw') . ' ' . $speak->about; ?></a>
        <?php Weapon::fire('tab_button_after', $hooks); ?>
      </div>
      <div class="tab-content-area">
        <div class="tab-content" id="tab-content-1-1">
          <?php
    
          $shield_url = $config->manager->slug . '/shield/' . $folder;
          $shield_url_kill = $shield_url . '/kill/file:';
          $shield_url_repair = $shield_url . '/repair/file:';
          $shield_path = SHIELD . DS . $config->shield . DS;
    
          ?>
          <table class="table-bordered table-full-width">
            <tbody>
              <?php if($files): ?>
              <?php foreach($files as $file): ?>
              <?php $url = File::url(str_replace($shield_path, "", $file->path)); ?>
              <tr<?php echo Session::get('recent_item_update') === File::B($file->path) ? ' class="active"' : ""; ?>>
                <td><?php echo strpos($url, '/') !== false ? Jot::span('fade', File::D($url) . '/') . File::B($url) : $url; ?></td>
                <td class="td-icon">
                <?php echo Jot::a('construct', $shield_url_repair . $url, Jot::icon('pencil'), array(
                    'title' => $speak->edit
                )); ?>
                </td>
                <td class="td-icon">
                <?php echo Jot::a('destruct', $shield_url_kill . $url, Jot::icon('times'), array(
                    'title' => $speak->delete
                )); ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php else: ?>
              <tr>
                <td class="td-icon"><?php echo $config->offset === 1 ? Jot::icon('home') : Jot::a('action', $shield_url, Jot::icon('home')); ?></td>
                <td><?php echo Config::speak('notify_' . ($config->offset === 1 ? 'empty' : 'error_not_found'), strtolower($speak->files)); ?></td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="tab-content hidden" id="tab-content-1-2">
          <p class="about-author">
          <?php echo Cell::strong($speak->author . ':') . ' ' . $page->author; ?><?php if(isset($page->url) && $page->url !== '#'): ?> <?php echo Cell::a($page->url, Jot::icon('external-link-square'), '_blank', array(
              'class' => array(
                  'about-url',
                  'help'
              ),
              'title' => $speak->link,
              'rel' => 'nofollow'
          )); ?>
          <?php endif; ?>
          </p>
          <h3 class="about-title"><?php echo $page->title; ?><?php if(isset($page->version)): ?> <code class="about-version"><?php echo $page->version; ?></code><?php endif; ?></h3>
          <div class="about-content"><?php echo $page->content; ?></div>
        </div>
      </div>
    </div>
    <div class="tab-content hidden" id="tab-content-2">
      <h3><?php echo Config::speak('manager.title__upload_package', $speak->shield); ?></h3>
      <?php echo Jot::uploader($config->manager->slug . '/shield', 'zip'); ?>
      <hr>
      <?php echo Guardian::wizard($segment); ?>
    </div>
    <?php if(count($folders) > 1): ?>
    <div class="tab-content hidden" id="tab-content-3">
      <h3><?php echo Config::speak('manager.title_your_', $speak->shields); ?></h3>
      <?php foreach($folders as $folder): $folder = File::B($folder); ?>
      <?php if($config->shield !== $folder && strpos($folder, '__') !== 0): ?>
      <?php $r = SHIELD . DS . $folder . DS; $c = File::exist($r . 'capture.png'); ?>
      <?php $page = Shield::info($folder); ?>
      <div class="media<?php if( ! $c): ?> no-capture<?php endif; ?>" id="shield:<?php echo $folder; ?>">
        <?php if($c): ?>
        <div class="media-capture" style="background-image:url('<?php echo File::url($c); ?>?v=<?php echo filemtime($c); ?>');" role="image"></div>
        <?php endif; ?>
        <h4 class="media-title"><?php echo Jot::icon('shield') . ' ' . $page->title; ?></h4>
        <div class="media-content">
          <?php
  
          if(preg_match('#<blockquote(>| .*?>)\s*([\s\S]*?)\s*<\/blockquote>#', $page->content, $matches)) {
              $curt = Text::parse($matches[2], '->text', '<abbr><sub><sup>'); // get first blockquote content as description
          } else {
              $curt = Converter::curt($page->content);
          }
  
          ?>
          <p><?php echo $curt; ?></p>
          <p>
            <?php echo Jot::btn('construct.small:cog', $speak->manage, $config->manager->slug . '/shield/' . $folder); ?> <?php if(File::exist($r . 'manager.php')): ?><?php echo Jot::btn('action.small:shield', $speak->attach, $config->manager->slug . '/shield/attach/id:' . $folder); ?> <?php endif; ?><?php echo Jot::btn('destruct.small:times-circle', $speak->delete, $config->manager->slug . '/shield/kill/id:' . $folder); ?>
          </p>
        </div>
      </div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php Weapon::fire('tab_content_after', $hooks); ?>
  </div>
</div>