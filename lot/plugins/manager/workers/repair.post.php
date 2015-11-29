<?php $hooks = array($page, $segment); ?>
<div class="tab-button-area">
  <?php Weapon::fire('tab_button_before', $hooks); ?>
  <?php if(strpos($config->url_path, '/id:') !== false): ?>
  <a class="tab-button" href="<?php echo $config->url . '/' . $config->manager->slug . '/' . $segment; ?>/ignite" data-confirm-text="<?php echo $speak->notify_confirm_page_leave; ?>"><?php echo Jot::icon('plus-square', 'fw') . ' ' . $speak->new; ?></a>
  <?php endif; ?>
  <a class="tab-button active" href="#tab-content-1"><?php echo Jot::icon('pencil', 'fw') . ' ' . $speak->compose; ?></a>
  <a class="tab-button" href="#tab-content-2"><?php echo Jot::icon('leaf', 'fw') . ' ' . $speak->manager->title_css_and_js_custom; ?></a>
  <a class="tab-button" href="#tab-content-3"><?php echo Jot::icon('th-list', 'fw') . ' ' . $speak->fields; ?></a>
  <?php Weapon::fire('tab_button_after', $hooks); ?>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <form class="form-<?php echo $page->id ? 'repair' : 'ignite'; ?> form-<?php echo $segment; ?>" id="form-<?php echo $page->id ? 'repair' : 'ignite'; ?>" action="<?php echo $config->url_current; ?>" method="post" enctype="multipart/form-data">
    <?php echo Form::hidden('token', $token); ?>
    <?php Weapon::fire('tab_content_before', $hooks); ?>
    <div class="tab-content" id="tab-content-1">
      <?php include __DIR__ . DS . 'unit.composer.1.php'; ?>
    </div>
    <div class="tab-content hidden" id="tab-content-2">
      <?php include __DIR__ . DS . 'unit.composer.2.php'; ?>
    </div>
    <div class="tab-content hidden" id="tab-content-3">
      <?php include __DIR__ . DS . 'unit.composer.3.php'; ?>
    </div>
    <?php Weapon::fire('tab_content_after', $hooks); ?>
    <?php include __DIR__ . DS . 'unit.composer.4.php'; ?>
  </form>
</div>