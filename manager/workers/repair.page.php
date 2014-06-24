<div class="tab-area">
  <?php if($config->editor_mode != 'ignite'): ?>
  <a class="tab" href="<?php echo $config->url . '/' . $config->manager->slug . '/' . $config->editor_type; ?>/ignite" data-confirm-text="<?php echo $speak->notify_confirm_page_leave; ?>"><i class="fa fa-fw fa-plus-square"></i> <?php echo $speak->new; ?></a>
  <?php endif; ?>
  <a class="tab active" href="#tab-content-1"><i class="fa fa-fw fa-pencil"></i> <?php echo $speak->manager->title_compose; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-fw fa-leaf"></i> <?php echo $speak->manager->title_custom_css_and_js; ?></a>
  <a class="tab" href="#tab-content-3"><i class="fa fa-fw fa-th-list"></i> <?php echo $speak->fields; ?></a>
  <a class="tab" href="#tab-content-4"><i class="fa fa-fw fa-eye"></i> <?php echo $speak->preview; ?></a>
</div>
<div class="tab-content-area">
  <?php echo Notify::read(); ?>
  <form class="form-compose" action="<?php $cache = Guardian::wayback(); echo $config->url_current; ?>" method="post" data-preview-url="<?php echo $config->url . '/' . $config->manager->slug . '/' . $config->editor_type . '/preview'; ?>">
    <input type="hidden" name="token" value="<?php echo Guardian::makeToken(); ?>">
    <div class="tab-content" id="tab-content-1">
      <?php include 'repair.page.page.php'; ?>
    </div>
    <div class="tab-content hidden" id="tab-content-2">
      <?php include 'repair.page.custom.php'; ?>
    </div>
    <div class="tab-content hidden" id="tab-content-3">
      <?php include 'repair.page.field.php'; ?>
    </div>
    <div class="tab-content hidden" id="tab-content-4">
      <div class="editor-preview" data-progress-text="<?php echo $speak->previewing; ?>&hellip;" data-error-text="<?php echo $speak->error; ?>."></div>
    </div>
    <hr>
    <p>
      <?php if($config->editor_mode == 'ignite'): ?>
      <button class="btn btn-construct" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->publish; ?></button>
      <?php else: ?>
      <button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button>
      <a class="btn btn-destruct" href="<?php echo $config->url . '/' . $config->manager->slug . '/' . $config->editor_type . '/kill/id:' . $cache['id']; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a>
      <?php endif; ?>
    </p>
  </form>
</div>