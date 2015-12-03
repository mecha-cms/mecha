<?php $hooks = array($page, $segment); ?>
<div class="tab-button-area">
  <?php Weapon::fire('tab_button_before', $hooks); ?>
  <a class="tab-button active" href="#tab-content-1"><?php echo Jot::icon('file-o', 'fw') . ' ' . $speak->file; ?></a>
  <a class="tab-button" href="#tab-content-2"><?php echo Jot::icon('folder', 'fw') . ' ' . $speak->folder; ?></a>
  <a class="tab-button" href="#tab-content-3"><?php echo Jot::icon('cloud-upload', 'fw') . ' ' . $speak->upload; ?></a>
  <?php Weapon::fire('tab_button_after', $hooks); ?>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <?php Weapon::fire('tab_content_before', $hooks); ?>
  <div class="tab-content" id="tab-content-1">
    <h3><?php echo Config::speak('manager.title_your_', $speak->assets); ?></h3>
    <form class="form-action form-asset" id="form-action" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/asset/do" method="post">
      <?php echo Form::hidden('token', $token); ?>
      <div class="main-action-group">
        <?php Weapon::fire('main_action_before', $hooks); ?>
        <?php echo Jot::button('destruct', $speak->delete, 'action:kill'); ?>
        <?php Weapon::fire('main_action_after', $hooks); ?>
      </div>
      <?php

      $asset_url = $config->manager->slug . '/asset';
      $asset_url_kill = $asset_url . '/kill/file:';
      $asset_url_repair = $asset_url . '/repair/file:';
      $asset_path = ASSET . DS;
      $q_path = Request::get('path', "");
      $q_path_parent = File::D($q_path);

      ?>
      <table class="table-bordered table-full-width">
        <thead>
          <tr>
            <th class="th-icon">
            <?php echo Form::checkbox(null, null, false, "", array(
                'data-connection' => 'selected[]'
            )); ?>
            </th>
            <th class="th-collapse"><?php echo Config::speak('last_', $speak->updated); ?></th>
            <th><?php echo $speak->file; ?></th>
            <th class="text-center" colspan="2"><?php echo $speak->action; ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if($files): ?>
          <?php foreach($files as $file): ?>
          <?php $url = File::url(str_replace($asset_path, "", $file->path)); ?>
          <tr<?php echo Session::get('recent_file_update') === File::B($file->path) ? ' class="active"' : ""; ?>>
            <td class="td-icon"><?php echo Form::checkbox('selected[]', $url); ?></td>
            <td class="td-collapse"><time datetime="<?php echo Date::format($file->update_raw, 'c'); ?>"><?php echo str_replace('-', '/', $file->update); ?></time></td>
            <?php
  
            $n = Jot::icon($file->is->file ? 'file-' . Mecha::alter($file->extension, array(
                'xls' => 'excel-o',
                'xlsx' => 'excel-o',
                'doc' => 'word-o',
                'docx' => 'word-o',
                'ppt' => 'powerpoint-o',
                'pptx' => 'powerpoint-o',
                'pdf' => 'pdf-o',
                'gz' => 'archive-o',
                'iso' => 'archive-o',
                'rar' => 'archive-o',
                'tar' => 'archive-o',
                'zip' => 'archive-o',
                'zipx' => 'archive-o',
                'bmp' => 'image-o',
                'gif' => 'image-o',
                'ico' => 'image-o',
                'jpeg' => 'image-o',
                'jpg' => 'image-o',
                'png' => 'image-o',
                'txt' => 'text-o',
                'log' => 'text-o',
                'mp3' => 'audio-o',
                'ogg' => 'audio-o',
                'wav' => 'audio-o',
                'mkv' => 'video-o',
                'flv' => 'video-o',
                'avi' => 'video-o',
                'mov' => 'video-o',
                'mp4' => 'video-o',
                '3gp' => 'video-o',
                'css' => 'code-o',
                'js' => 'code-o',
                'json' => 'code-o',
                'jsonp' => 'code-o',
                'htm' => 'code-o',
                'html' => 'code-o',
                'php' => 'code-o',
                'xml' => 'code-o'
            ), 'o') : 'folder') . ' ' . File::B($url);
  
            ?>
            <td>
              <?php if(is_dir($file->path)): ?>
              <a href="<?php echo $config->url . '/' . $asset_url . '/1' . HTTP::query('path', ltrim($q_path . '/' . File::B($url), '/')); ?>" title="<?php echo $speak->enter; ?>&hellip;"><?php echo $n; ?></a>
              <?php else: ?>
              <a href="<?php echo $file->url; ?>" title="<?php echo File::size($file->path); ?>" target="_blank"><?php echo $n; ?></a>
              <?php endif; ?>
            </td>
            <td class="td-icon">
            <?php echo Jot::a('construct', $asset_url_repair . $url . HTTP::query('path', $q_path), Jot::icon('pencil'), array(
                'title' => $speak->edit
            )); ?>
            </td>
            <td class="td-icon">
            <?php echo Jot::a('destruct', $asset_url_kill . $url . HTTP::query('path', $q_path), Jot::icon('times'), array(
                'title' => $speak->delete
            )); ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php else: ?>
          <?php if($q_path): ?>
          <tr>
            <td colspan="3"><?php echo $config->offset !== 1 ? $speak->notify_error_not_found : Cell::strong('..'); ?></td>
            <td class="td-icon"></td>
            <td class="td-icon"></td>
          </tr>
          <?php endif; ?>
          <?php endif; ?>
          <?php if($q_path): ?>
          <tr>
            <td colspan="3"><?php echo File::B($q_path); ?></td>
            <td class="td-icon"><?php echo Jot::a('accept', $config->url_path . HTTP::query('path', $q_path_parent), Jot::icon('folder-open'), array(
                'title' => $speak->exit . '&hellip;'
            )); ?></td>
            <td class="td-icon"><?php echo Jot::a('destruct', $asset_url_kill . $q_path . HTTP::query('path', $q_path_parent), Jot::icon('times'), array(
                'title' => $speak->delete
            )); ?></td>
          </tr>
          <?php else: ?>
          <?php if( ! $files): ?>
          <tr>
            <td colspan="3"><?php echo $config->offset !== 1 ? $speak->notify_error_not_found : Cell::strong('..'); ?></td>
            <td class="td-icon"><?php echo Jot::icon('pencil'); ?></td>
            <td class="td-icon"><?php echo Jot::icon('times'); ?></td>
          </tr>
          <?php endif; ?>
          <?php endif; ?>
        </tbody>
      </table>
      <?php include __DIR__ . DS . 'unit' . DS . 'pager' . DS . 'step.php'; ?>
    </form>
    <?php if( ! empty($pager->step->url) || Request::get('q')): ?>
    <hr>
    <?php echo Jot::finder($asset_url, 'q', array('path' => Text::parse($q_path, '->encoded_url'))); ?>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <form class="form-ignite form-asset" id="form-ignite" action="<?php echo $config->url . '/' . $asset_url . HTTP::query('path', $q_path); ?>" method="post">
      <p><code><?php echo ASSET . DS . ($q_path ? File::path($q_path) . DS : ""); ?> &hellip;</code></p>
      <p><?php echo Form::text('folder', Guardian::wayback('folder'), $speak->manager->placeholder_folder_name) . ' ' . Jot::button('construct', $speak->create); ?></p>
      <p><?php echo Form::checkbox('redirect', 1, Request::method('get') ? true : Guardian::wayback('redirect', false), Config::speak('manager.description_redirect_to_', $speak->folder)); ?></p>
      <?php echo Form::hidden('token', $token); ?>
    </form>
  </div>
  <div class="tab-content hidden" id="tab-content-3">
    <h3><?php echo Config::speak('manager.title__upload_alt', $speak->asset); ?></h3>
    <?php echo Jot::uploader($asset_url . HTTP::query('path', $q_path)); ?>
    <p><strong><?php echo $speak->accepted; ?>:</strong> <code>*.<?php echo implode('</code>, <code>*.', File::$config['file_extension_allow']); ?></code></p>
  </div>
  <?php Weapon::fire('tab_content_after', $hooks); ?>
</div>