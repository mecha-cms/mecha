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

      $b_path = ASSET . DS;

      $b_url = $config->manager->slug . '/asset';
      $b_url_kill = $b_url . '/kill/file:';
      $b_url_repair = $b_url . '/repair/file:';

      include DECK . DS . 'workers' . DS . 'unit.explorer.3.php';

      ?>
      <?php if( ! empty($pager->step->url)): ?>
      <p class="pager cf"><?php echo $pager->step->link; ?></p>
      <?php endif; ?>
    </form>
    <?php if( ! empty($pager->step->url) || Request::get('q')): ?>
    <hr>
    <?php echo Jot::finder($config->manager->slug . '/asset', 'q', array('path' => Text::parse($g_path, '->encoded_url'))); ?>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <form class="form-ignite form-asset" action="<?php echo $config->url . '/' . $config->manager->slug . '/asset' . $q; ?>" method="post">
      <p><code><?php echo ASSET . DS . ($g_path ? File::path($g_path) . DS : ""); ?> &hellip;</code></p>
      <p><?php echo Form::text('folder', Guardian::wayback('folder'), $speak->manager->placeholder_folder_name) . ' ' . Jot::button('construct', $speak->create); ?></p>
      <p><?php echo Form::checkbox('redirect', 1, $_SERVER['REQUEST_METHOD'] === 'GET' ? true : Guardian::wayback('redirect', false), Config::speak('manager.description_redirect_to_', $speak->folder)); ?></p>
    </form>
  </div>
  <div class="tab-content hidden" id="tab-content-3">
    <h3><?php echo Config::speak('manager.title__upload_alt', $speak->asset); ?></h3>
    <?php echo Jot::uploader($config->manager->slug . '/asset' . $q); ?>
  </div>
</div>