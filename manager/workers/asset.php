<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><?php echo Jot::icon('file-o', 'fw') . ' ' . $speak->file; ?></a>
  <a class="tab" href="#tab-content-2"><?php echo Jot::icon('folder', 'fw') . ' ' . $speak->folder; ?></a>
  <a class="tab" href="#tab-content-3"><?php echo Jot::icon('cloud-upload', 'fw') . ' ' . $speak->upload; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <div class="tab-content" id="tab-content-1">
    <h3><?php echo Config::speak('manager.title_your_', $speak->assets); ?></h3>
    <form class="form-asset" action="<?php echo $config->url . '/' . $config->manager->slug . '/asset/kill'; ?>" method="post">
      <?php echo Form::hidden('token', $token); ?>
      <div class="main-action-group">
        <?php echo Jot::button('destruct', $speak->delete); ?>
      </div>
      <?php

      $c_path = ASSET . DS;

      $c_url = $config->manager->slug . '/asset';
      $c_url_kill = $c_url . '/kill/file:';
      $c_url_repair = $c_url . '/repair/file:';

      include DECK . DS . 'workers' . DS . 'unit.explorer.3.php';
      include DECK . DS . 'workers' . DS . 'unit.pager.1.php';

      ?>
    </form>
    <?php if( ! empty($pager->step->url) || Request::get('q')): ?>
    <hr>
    <?php echo Jot::finder($config->manager->slug . '/asset', 'q', array('path' => Text::parse($q_path, '->encoded_url'))); ?>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <form class="form-ignite form-asset" action="<?php echo $config->url . '/' . $config->manager->slug . '/asset' . $q; ?>" method="post">
      <p><code><?php echo ASSET . DS . ($q_path ? File::path($q_path) . DS : ""); ?> &hellip;</code></p>
      <p><?php echo Form::text('folder', Guardian::wayback('folder'), $speak->manager->placeholder_folder_name) . ' ' . Jot::button('construct', $speak->create); ?></p>
      <p><?php echo Form::checkbox('redirect', 1, $_SERVER['REQUEST_METHOD'] === 'GET' ? true : Guardian::wayback('redirect', false), Config::speak('manager.description_redirect_to_', $speak->folder)); ?></p>
    </form>
  </div>
  <div class="tab-content hidden" id="tab-content-3">
    <h3><?php echo Config::speak('manager.title__upload_alt', $speak->asset); ?></h3>
    <?php echo Jot::uploader($config->manager->slug . '/asset' . $q); ?>
  </div>
</div>