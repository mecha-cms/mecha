<?php

$c_path = ASSET . DS;

$c_url = $config->manager->slug . '/asset';
$c_url_kill = $c_url . '/kill/file:';
$c_url_repair = $c_url . '/repair/file:';

?>
<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><?php echo Jot::icon('file-o', 'fw') . ' ' . $speak->file; ?></a>
  <a class="tab" href="#tab-content-2"><?php echo Jot::icon('folder', 'fw') . ' ' . $speak->folder; ?></a>
  <a class="tab" href="#tab-content-3"><?php echo Jot::icon('cloud-upload', 'fw') . ' ' . $speak->upload; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <div class="tab-content" id="tab-content-1">
    <h3><?php echo Config::speak('manager.title_your_', $speak->assets); ?></h3>
    <form class="form-kill form-asset" id="form-kill" action="<?php echo $config->url . '/' . $c_url . '/kill'; ?>" method="post">
      <?php echo Form::hidden('token', $token); ?>
      <div class="main-action-group">
        <?php echo Jot::button('destruct', $speak->delete); ?>
      </div>
      <?php include __DIR__ . DS . 'unit.explorer.3.php'; ?>
      <?php include __DIR__ . DS . 'unit.pager.1.php'; ?>
    </form>
    <?php if( ! empty($pager->step->url) || Request::get('q')): ?>
    <hr>
    <?php echo Jot::finder($c_url, 'q', array('path' => Text::parse($q_path, '->encoded_url'))); ?>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <form class="form-ignite form-asset" id="form-ignite" action="<?php echo $config->url . '/' . $c_url . $q; ?>" method="post">
      <p><code><?php echo ASSET . DS . ($q_path ? File::path($q_path) . DS : ""); ?> &hellip;</code></p>
      <p><?php echo Form::text('folder', Guardian::wayback('folder'), $speak->manager->placeholder_folder_name) . ' ' . Jot::button('construct', $speak->create); ?></p>
      <p><?php echo Form::checkbox('redirect', 1, Request::method('get') ? true : Guardian::wayback('redirect', false), Config::speak('manager.description_redirect_to_', $speak->folder)); ?></p>
      <?php echo Form::hidden('token', $token); ?>
    </form>
  </div>
  <div class="tab-content hidden" id="tab-content-3">
    <h3><?php echo Config::speak('manager.title__upload_alt', $speak->asset); ?></h3>
    <?php echo Jot::uploader($c_url . $q); ?>
  </div>
</div>