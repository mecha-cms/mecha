<?php

$path = File::url($path);
$path_dir = rtrim(dirname($path), '.');
$q = $path !== "" ? '?path=' . Text::parse($path, '->encoded_url') : "";

?>
<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><?php echo Jot::icon('file-o', 'fw') . ' ' . $speak->file; ?></a>
  <a class="tab" href="#tab-content-2"><?php echo Jot::icon('folder', 'fw') . ' ' . $speak->folder; ?></a>
  <a class="tab" href="#tab-content-3"><?php echo Jot::icon('cloud-upload', 'fw') . ' ' . $speak->upload; ?></a>
</div>
<div class="tab-content-area">
  <div class="tab-content" id="tab-content-1">
    <h3><?php echo Config::speak('manager.title_your_', $speak->assets); ?></h3>
    <form class="form-asset" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/asset/kill" method="post">
      <?php echo Form::hidden('token', $token); ?>
      <div class="main-action-group">
        <?php echo Jot::button('destruct', $speak->delete); ?>
      </div>
      <?php echo Notify::errors() ? "" : $messages; ?>
      <table class="table-bordered table-full-width">
        <thead>
          <tr>
            <th class="th-icon">
            <?php echo Form::checkbox(null, null, false, "", array(
                'data-connection' => 'selected[]'
            )); ?>
            </th>
            <th><?php echo Config::speak('last_', $speak->updated); ?></th>
            <th><?php echo $speak->file; ?></th>
            <th class="text-center" colspan="2"><?php echo $speak->action; ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if($files): ?>
          <?php foreach($files as $file): $name = File::url(str_replace(ASSET . DS, "", $file->path)); ?>
          <tr<?php echo Session::get('recent_asset_updated') === basename($file->path) ? ' class="active"' : ""; ?>>
            <td class="td-icon">
            <?php echo Form::checkbox('selected[]', $name); ?>
            </td>
            <td class="td-collapse"><time datetime="<?php echo Date::format($file->update, 'c'); ?>"><?php echo Date::format($file->update, 'Y/m/d H:i:s'); ?></time></td>
            <td><a href="<?php echo is_dir($file->path) ? $config->url_current . '?path=' . Text::parse(ltrim($path . '/' . basename($name), '/'), '->encoded_url') : $file->url; ?>"<?php echo is_file($file->path) ? ' title="' . $file->size . '" target="_blank"' : ' title="' . $speak->enter . '&hellip;"'; ?>><?php echo Jot::icon(is_file($file->path) ? 'file-' . Mecha::alter(strtolower(pathinfo($file->path, PATHINFO_EXTENSION)), array(
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
            ), 'o') : 'folder') . ' ' . basename($name); ?></a></td>
            <td class="td-icon">
            <?php echo Jot::a('construct', $config->manager->slug . '/asset/repair/file:' . $name . $q, Jot::icon('pencil'), array(
                'title' => $speak->edit
            )); ?>
            </td>
            <td class="td-icon">
            <?php echo Jot::a('destruct', $config->manager->slug . '/asset/kill/file:' . $name . $q, Jot::icon('times'), array(
                'title' => $speak->delete
            )); ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
          <?php if(Request::get('path')): ?>
          <tr>
            <td colspan="3"><?php echo basename($path); ?></td>
            <td class="td-icon"><?php echo Jot::a('accept', $config->url_path . ($path_dir !== "" ? '?path=' . Text::parse($path_dir, '->encoded_url') : ""), Jot::icon('folder-open'), array(
                'title' => $speak->exit . '&hellip;'
            )); ?></td>
            <td class="td-icon"><?php echo Jot::a('destruct', $config->manager->slug . '/asset/kill/file:' . $path . ($path_dir !== "" ? '?path=' . Text::parse($path_dir, '->encoded_url') : ""), Jot::icon('times'), array(
                'title' => $speak->delete
            )); ?></td>
          </tr>
          <?php elseif( ! $files): ?>
          <tr>
            <td colspan="3"><?php echo Config::speak('notify_' . ($config->offset === 1 ? 'empty' : 'error_not_found'), strtolower($speak->assets)); ?></td>
            <td class="td-icon"><?php echo Jot::a('action', $config->manager->slug . '/asset', Jot::icon('home')); ?></td>
            <td class="td-icon"><?php echo Jot::icon('times'); ?></td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
      <?php if( ! empty($pager->step->url)): ?>
      <p class="pager cf"><?php echo $pager->step->link; ?></p>
      <?php endif; ?>
    </form>
    <?php if( ! empty($pager->step->url) || Request::get('q')): ?>
    <hr>
    <?php echo Jot::finder($config->manager->slug . '/asset', 'q', array('path' => Text::parse($path, '->encoded_url'))); ?>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <form class="form-ignite form-asset" action="<?php echo $config->url . '/' . $config->manager->slug . '/asset' . $q; ?>" method="post">
      <p><code><?php echo ASSET . DS . ($path !== "" ? File::path($path) . DS : ""); ?> &hellip;</code></p>
      <p><?php echo Form::text('folder', "", $speak->manager->placeholder_folder_name) . ' ' . Jot::button('construct', $speak->create); ?></p>
    </form>
  </div>
  <div class="tab-content hidden" id="tab-content-3">
    <?php echo Notify::errors() ? $messages : ""; ?>
    <h3><?php echo Config::speak('manager.title__upload_alt', $speak->asset); ?></h3>
    <?php echo Jot::uploader($config->manager->slug . '/asset' . $q); ?>
  </div>
</div>