<?php

$q_path = Request::get('path', "");
$q_path_dir = rtrim(dirname($q_path), '.');
$q = $q_path ? '?path=' . Text::parse($q_path, '->encoded_url') : "";

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
    <?php $url = File::url(str_replace($c_path, "", $file->path)); ?>
    <tr<?php echo Session::get('recent_file_update') === basename($file->path) ? ' class="active"' : ""; ?>>
      <td class="td-icon"><?php echo Form::checkbox('selected[]', $url); ?></td>
      <td class="td-collapse"><time datetime="<?php echo Date::format($file->last_update, 'c'); ?>"><?php echo str_replace('-', '/', $file->update); ?></time></td>
      <?php

      $n = Jot::icon(is_file($file->path) ? 'file-' . Mecha::alter(strtolower(pathinfo($file->path, PATHINFO_EXTENSION)), array(
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
      ), 'o') : 'folder') . ' ' . basename($url);

      ?>
      <td>
        <?php if(is_dir($file->path)): ?>
        <a href="<?php echo $config->url_current . '?path=' . Text::parse(ltrim($q_path . '/' . basename($url), '/'), '->encoded_url'); ?>" title="<?php echo $speak->enter; ?>&hellip;"><?php echo $n; ?></a>
        <?php else: ?>
        <a href="<?php echo $file->url; ?>" title="<?php echo File::size($file->path); ?>" target="_blank"><?php echo $n; ?></a>
        <?php endif; ?>
      </td>
      <td class="td-icon">
      <?php echo Jot::a('construct', $c_url_repair . $url . $q, Jot::icon('pencil'), array(
          'title' => $speak->edit
      )); ?>
      </td>
      <td class="td-icon">
      <?php echo Jot::a('destruct', $c_url_kill . $url . $q, Jot::icon('times'), array(
          'title' => $speak->delete
      )); ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php else: ?>
    <?php if(isset($_GET['path'])): ?>
    <tr>
      <td colspan="3"><?php echo $config->offset !== 1 ? Config::speak('notify_error_not_found') : Cell::strong('..'); ?></td>
      <td class="td-icon"></td>
      <td class="td-icon"></td>
    </tr>
    <?php endif; ?>
    <?php endif; ?>
    <?php if(isset($_GET['path'])): ?>
    <tr>
      <td colspan="3"><?php echo basename($q_path); ?></td>
      <td class="td-icon"><?php echo Jot::a('accept', $config->url_path . ($q_path_dir ? '?path=' . Text::parse($q_path_dir, '->encoded_url') : ""), Jot::icon('folder-open'), array(
          'title' => $speak->exit . '&hellip;'
      )); ?></td>
      <td class="td-icon"><?php echo Jot::a('destruct', $c_url_kill . $q_path . ($q_path_dir ? '?path=' . Text::parse($q_path_dir, '->encoded_url') : ""), Jot::icon('times'), array(
          'title' => $speak->delete
      )); ?></td>
    </tr>
    <?php else: ?>
    <?php if( ! $files): ?>
    <tr>
      <td colspan="3"><?php echo $config->offset !== 1 ? Config::speak('notify_error_not_found') : Cell::strong('..'); ?></td>
      <td class="td-icon"><?php echo Jot::icon('pencil'); ?></td>
      <td class="td-icon"><?php echo Jot::icon('times'); ?></td>
    </tr>
    <?php endif; ?>
    <?php endif; ?>
  </tbody>
</table>