<table class="table-bordered table-full-width">
  <tbody>
    <?php if($files): ?>
    <?php foreach($files as $file): ?>
    <?php $url = File::url(str_replace($c_path, "", $file->path)); ?>
    <tr<?php echo Session::get('recent_file_update') === basename($file->path) ? ' class="active"' : ""; ?>>
      <td><?php echo strpos($url, '/') !== false ? Jot::span('fade', dirname($url) . '/') . basename($url) : $url; ?></td>
      <td class="td-icon">
      <?php echo isset($c_url_repair) && $c_url_repair !== false ? Jot::a('construct', $c_url_repair . $url, Jot::icon('pencil'), array(
          'title' => $speak->edit
      )) : Jot::icon('pencil'); ?>
      </td>
      <td class="td-icon">
      <?php echo isset($c_url_kill) && $c_url_kill !== false ? Jot::a('destruct', $c_url_kill . $url, Jot::icon('times'), array(
          'title' => $speak->delete
      )) : Jot::icon('times'); ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php else: ?>
    <tr>
      <td class="td-icon"><?php echo $config->offset === 1 ? Jot::icon('home') : Jot::a('action', $c_url, Jot::icon('home')); ?></td>
      <td><?php echo Config::speak('notify_' . ($config->offset === 1 ? 'empty' : 'error_not_found'), strtolower($speak->files)); ?></td>
    </tr>
    <?php endif; ?>
  </tbody>
</table>