<?php $hooks = array($files, $segment); ?>
<?php echo $messages; ?>
<form class="form-action form-cache" id="form-action" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/cache/do" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <div class="main-action-group">
    <?php Weapon::fire('main_action_before', $hooks); ?>
    <?php echo Jot::button('destruct', $speak->delete, 'action:kill'); ?>
    <?php Weapon::fire('main_action_after', $hooks); ?>
  </div>
  <?php

  $cache_url = $config->manager->slug . '/cache';
  $cache_url_kill = $cache_url . '/kill/file:';
  $cache_url_repair = $cache_url . '/repair/file:';
  $cache_path = CACHE . DS;

  ?>
  <table class="table-bordered table-full-width">
    <?php if($files): ?>
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
      <?php foreach($files as $file): ?>
      <?php $url = File::url(str_replace($cache_path, "", $file->path)); ?>
      <tr<?php echo Mecha::walk(Session::get('recent_item_update', array()))->has(File::B($file->path)) ? ' class="active"' : ""; ?>>
        <td class="td-icon"><?php echo Form::checkbox('selected[]', $url); ?></td>
        <td class="td-collapse"><time datetime="<?php echo Date::format($file->update_raw, 'c'); ?>"><?php echo str_replace('-', '/', $file->update); ?></time></td>
        <td><span title="<?php echo $file->size; ?>"><?php echo strpos($url, '/') !== false ? Jot::span('fade', File::D($url) . '/') . File::B($url) : $url; ?></span></td>
        <td class="td-icon">
        <?php echo Jot::a('construct', $cache_url_repair . $url, Jot::icon('pencil'), array(
            'title' => $speak->edit
        )); ?>
        </td>
        <td class="td-icon">
        <?php echo Jot::a('destruct', $cache_url_kill . $url, Jot::icon('times'), array(
            'title' => $speak->delete
        )); ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <?php else: ?>
    <tbody>
      <tr>
        <td colspan="3"><?php echo $config->offset !== 1 ? $speak->notify_error_not_found : Cell::strong('..'); ?></td>
        <td class="td-icon"><?php echo Jot::icon('pencil'); ?></td>
        <td class="td-icon"><?php echo Jot::icon('times'); ?></td>
      </tr>
    </tbody>
    <?php endif; ?>
  </table>
  <?php include __DIR__ . DS . 'unit' . DS . 'pager' . DS . 'step.php'; ?>
</form>
<?php if( ! empty($pager->step->url) || Request::get('q')): ?>
<hr>
<?php echo Jot::finder($cache_url, 'q'); ?>
<?php endif; ?>