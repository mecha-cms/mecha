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
        <a class="tab-button" href="#tab-content-1-2"><?php echo Jot::icon('cog', 'fw') . ' ' . $speak->config; ?></a>
        <a class="tab-button" href="#tab-content-1-3"><?php echo Jot::icon('user', 'fw') . ' ' . $speak->about; ?></a>
        <?php Weapon::fire('tab_button_after', $hooks); ?>
      </div>
      <div class="tab-content-area">
        <div class="tab-content" id="tab-content-1-1">
          <?php

          $shield_url = $config->manager->slug . '/shield/' . $folder;
          $shield_url_kill = $shield_url . '/kill/file:';
          $shield_url_repair = $shield_url . '/repair/file:';
          $shield_path = SHIELD . DS . $folder . DS;

          ?>
          <table class="table-bordered table-full-width">
            <thead>
              <tr>
                <th><?php echo $speak->file; ?></th>
                <th class="text-center" colspan="2"><?php echo $speak->action; ?></th>
              </tr>
            </thead>
            <tbody>
              <?php if($files): ?>
              <?php foreach($files as $file): ?>
              <?php if(strpos($file->path, $shield_path . 'states' . DS) === 0 || strpos($file->path, $shield_path . 'workers' . DS) === 0) continue; ?>
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
        <?php

        if($page->configurator) {
            $test = file_get_contents($page->configurator);
            if(strpos($test, '</form>') === false) { // allow plugin configurator without `<form>` tag
                echo '<form class="form-plugin" action="' . $config->url . '/' . $config->manager->slug . '/shield/' . $folder . '/update' . str_replace('&', '&amp;', $config->url_query) . '" method="post">';
                echo Form::hidden('token', $token);
                include $page->configurator;
                if(strpos($test, 'Jot::button(\'action\', $speak->update)') === false && strpos($test, 'Jot::button("action", $speak->update)') === false) {
                    echo '<div class="grid-group">';
                    echo '<span class="grid span-1"></span>';
                    echo '<span class="grid span-5">';
                    Weapon::fire('action_before', $hooks);
                    echo Jot::button('action', $speak->update);
                    Weapon::fire('action_after', $hooks);
                    echo '</span>';
                    echo '</div>';
                }
                echo '</form>';
            } else {
                include $page->configurator;
            }
        } else {
            echo Cell::p(Config::speak('notify_not_available', $speak->config));
        }

        ?>
        </div>
        <div class="tab-content hidden" id="tab-content-1-3">
          <p class="about-author">
          <?php echo Cell::strong($speak->author . ':') . ' ' . $page->author; ?><?php if(isset($page->url) && $page->url !== '#'): ?> <?php echo Cell::a($page->url, Jot::icon('external-link-square'), true, array(
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
      <?php if($config->shield !== $folder && ! File::hidden($folder)): ?>
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
              $curt = Text::parse($matches[2], '->text', WISE_CELL_I); // get first blockquote content as description
          } else {
              $curt = Converter::curt($page->content);
          }

          ?>
          <p><?php echo $curt; ?></p>
          <p>
            <?php Weapon::fire('action_before', array($page, $segment)); ?>
            <?php echo Jot::btn('construct.small:cog', $speak->manage, $config->manager->slug . '/shield/' . $folder); ?> <?php if(File::exist($r . 'manager.php')): ?><?php echo Jot::btn('action.small:shield', $speak->attach, $config->manager->slug . '/shield/attach/id:' . $folder); ?> <?php endif; ?><?php echo Jot::btn('destruct.small:times-circle', $speak->delete, $config->manager->slug . '/shield/kill/id:' . $folder); ?>
            <?php Weapon::fire('action_after', array($page, $segment)); ?>
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