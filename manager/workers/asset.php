<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><?php echo UI::icon('briefcase', 'fw') . ' ' . $speak->assets; ?></a>
  <a class="tab" href="#tab-content-2"><?php echo UI::icon('cloud-upload', 'fw') . ' ' . $speak->upload; ?></a>
</div>
<div class="tab-content-area">
  <div class="tab-content" id="tab-content-1">
    <?php if($files): ?>
    <h3><?php echo Config::speak('manager.title_your_', $speak->assets); ?></h3>
    <form class="form-asset" action="<?php echo $config->url . '/' . $config->manager->slug; ?>/asset/kill" method="post">
      <?php echo Form::hidden('token', $token); ?>
      <div class="main-action-group">
        <?php echo UI::button('destruct', $speak->delete); ?>
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
          <?php foreach($files as $file): $the_asset_url = File::url(str_replace(ASSET . DS, "", $file->path)); ?>
          <tr<?php echo Session::get('recent_asset_uploaded') === basename($file->path) ? ' class="active"' : ""; ?>>
            <td class="td-icon">
            <?php echo Form::checkbox('selected[]', $the_asset_url); ?>
            </td>
            <td><time datetime="<?php echo Date::format($file->update, 'c'); ?>"><?php echo Date::format($file->update, 'Y/m/d H:i:s'); ?></time></td>
            <td><a href="<?php echo $file->url; ?>" title="<?php echo $file->size; ?>" target="_blank"><?php echo strpos($the_asset_url, '/') !== false ? UI::span('fade', dirname($the_asset_url) . '/') . basename($the_asset_url) : $the_asset_url; ?></a></td>
            <td class="td-icon">
            <?php echo UI::a('construct', $config->url . '/' . $config->manager->slug . '/asset/repair/file:' . $the_asset_url, UI::icon('pencil'), array(
                'title' => $speak->edit
            )); ?>
            </td>
            <td class="td-icon">
            <?php echo UI::a('destruct', $config->url . '/' . $config->manager->slug . '/asset/kill/file:' . $the_asset_url, UI::icon('times'), array(
                'title' => $speak->delete
            )); ?>
            </td>
          </tr>
          <?php endforeach; ?>
      </table>
      <?php if( ! empty($pager->step->url)): ?>
      <p class="pager cf"><?php echo $pager->step->link; ?></p>
      <?php endif; ?>
    </form>
    <?php if( ! empty($pager->step->url) || Request::get('q')): ?>
    <hr>
    <?php echo UI::finder($config->url . '/' . $config->manager->slug . '/asset', 'q'); ?>
    <?php endif; ?>
    <?php else: ?>
    <p><?php echo Config::speak('notify_' . (Request::get('q') || $config->offset !== 1 ? 'error_not_found' : 'empty'), strtolower($speak->assets)); ?></p>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <?php echo Notify::errors() ? $messages : ""; ?>
    <h3><?php echo Config::speak('manager.title__upload_alt', $speak->asset); ?></h3>
<?php echo UI::uploader($config->url . '/' . $config->manager->slug . '/asset'); ?>
  </div>
</div>