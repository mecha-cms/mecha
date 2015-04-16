<?php if($files): ?>
<form class="form-cache" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/cache/kill" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <div class="main-action-group">
    <?php echo UI::button('destruct', $speak->delete); ?>
  </div>
  <?php echo $messages; ?>
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
      <?php $editable = explode(',', SCRIPT_EXT); foreach($files as $file): $the_cache_url = File::url(str_replace(CACHE . DS, "", $file->path)); ?>
      <tr>
        <td class="td-icon">
        <?php echo Form::checkbox('selected[]', $the_cache_url); ?>
        </td>
        <td class="td-collapse"><time datetime="<?php echo Date::format($file->update, 'c'); ?>"><?php echo Date::format($file->update, 'Y/m/d H:i:s'); ?></time></td>
        <td><span title="<?php echo $file->size; ?>"><?php echo strpos($the_cache_url, '/') !== false ? UI::span('fade', dirname($the_cache_url) . '/') . basename($the_cache_url) : $the_cache_url; ?></span></td>
        <?php if(in_array($file->extension, $editable)): ?>
        <td class="td-icon">
        <?php echo UI::a('construct', $config->manager->slug . '/cache/repair/file:' . $the_cache_url, UI::icon('pencil'), array(
            'title' => $speak->edit
        )); ?>
        </td>
        <?php else: ?>
        <td></td>
        <?php endif; ?>
        <td class="td-icon">
        <?php echo UI::a('destruct', $config->manager->slug . '/cache/kill/file:' . $the_cache_url, UI::icon('times'), array(
            'title' => $speak->delete
        )); ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if( ! empty($pager->step->url)): ?>
  <p class="pager cf"><?php echo $pager->step->link; ?></p>
  <?php endif; ?>
</form>
<?php if( ! empty($pager->step->url) || Request::get('q')): ?>
<hr>
<?php echo UI::finder($config->manager->slug . '/cache', 'q'); ?>
<?php endif; ?>
<?php else: ?>
<?php echo $messages; ?>
<p><?php echo Config::speak('notify_empty', strtolower($speak->caches)); ?></p>
<?php endif; ?>