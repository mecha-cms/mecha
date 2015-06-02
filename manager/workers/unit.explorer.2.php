<table class="table-bordered table-full-width">
  <?php if($files): ?>
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
    <?php foreach($files as $file): ?>
    <?php $url = File::url(str_replace($b_path, "", $file->path)); ?>
    <tr<?php echo Session::get('recent_file_update') === basename($file->path) ? ' class="active"' : ""; ?>>
      <td class="td-icon"><?php echo Form::checkbox('selected[]', $url); ?></td>
      <td class="td-collapse"><time datetime="<?php echo Date::format($file->last_update, 'c'); ?>"><?php echo str_replace('-', '/', $file->update); ?></time></td>
      <td><span title="<?php echo $file->size; ?>"><?php echo strpos($url, '/') !== false ? Jot::span('fade', dirname($url) . '/') . basename($url) : $url; ?></span></td>
      <td class="td-icon">
      <?php echo Jot::a('construct', $b_url_repair . $url, Jot::icon('pencil'), array(
          'title' => $speak->edit
      )); ?>
      </td>
      <td class="td-icon">
      <?php echo Jot::a('destruct', $b_url_kill . $url, Jot::icon('times'), array(
          'title' => $speak->delete
      )); ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
  <?php else: ?>
  <tbody>
  <tr>
    <td class="td-icon"><?php echo $config->offset === 1 ? Jot::icon('home') : Jot::a('action', $b_url, Jot::icon('home')); ?></td>
    <td><?php echo Config::speak('notify_' . ($config->offset === 1 ? 'empty' : 'error_not_found'), strtolower($speak->files)); ?></td>
  </tr>
  </tbody>
  <?php endif; ?>
</table>