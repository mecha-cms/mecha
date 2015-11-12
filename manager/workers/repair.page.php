<div class="tab-area">
  <?php if(strpos($config->url_path, '/id:') !== false): ?>
  <a class="tab" href="<?php echo $config->url . '/' . $config->manager->slug;  ?>/page/ignite" data-confirm-text="<?php echo $speak->notify_confirm_page_leave; ?>"><?php echo Jot::icon('plus-square', 'fw') . ' ' . $speak->new; ?></a>
  <?php endif; ?>
  <a class="tab active" href="#tab-content-1"><?php echo Jot::icon('pencil', 'fw') . ' ' . $speak->compose; ?></a>
  <a class="tab" href="#tab-content-2"><?php echo Jot::icon('leaf', 'fw') . ' ' . $speak->manager->title_css_and_js_custom; ?></a>
  <a class="tab" href="#tab-content-3"><?php echo Jot::icon('th-list', 'fw') . ' ' . $speak->fields; ?></a>
  <a class="tab ajax-post" href="#tab-content-4" data-action-url="<?php echo $config->url . '/' . $config->manager->slug . '/ajax/preview:' . $segment; ?>" data-text-progress="<?php echo $speak->previewing; ?>&hellip;" data-text-error="<?php echo $speak->error; ?>." data-scope="#form-<?php echo $page->id ? 'repair' : 'ignite'; ?>" data-target="#form-<?php echo $page->id ? 'repair' : 'ignite'; ?>-preview"><?php echo Jot::icon('eye', 'fw') . ' ' . $speak->preview; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <form class="form-<?php echo $page->id ? 'repair' : 'ignite'; ?> form-page" id="form-<?php echo $page->id ? 'repair' : 'ignite'; ?>" action="<?php echo $config->url_current; ?>" method="post" enctype="multipart/form-data">
    <?php echo Form::hidden('token', $token); ?>
    <div class="tab-content" id="tab-content-1">
      <?php Weapon::fire('unit_composer_1_before', array($segment)); ?>
      <?php include 'unit.composer.1.php'; ?>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->description; ?></span>
        <span class="grid span-5">
        <?php echo Form::textarea('description', Guardian::wayback('description', $page->description), Config::speak('manager.placeholder_description_', strtolower($speak->page)), array(
            'class' => 'textarea-block'
        )); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->author; ?></span>
        <span class="grid span-5">
          <?php if(Guardian::get('status') === 'pilot'): ?>
          <?php echo Form::text('author', Guardian::wayback('author', $page->author)); ?>
          <?php else: ?>
          <?php echo Form::hidden('author', $page->author); ?>
          <span class="form-static"><?php echo Jot::icon('lock') . ' ' . $page->author; ?></span>
          <?php endif; ?>
        </span>
      </label>
      <?php Weapon::fire('unit_composer_1_after', array($segment)); ?>
    </div>
    <div class="tab-content hidden" id="tab-content-2">
      <?php Weapon::fire('unit_composer_2_before', array($segment)); ?>
      <?php include 'unit.composer.2.php'; ?>
      <?php Weapon::fire('unit_composer_2_after', array($segment)); ?>
    </div>
    <div class="tab-content hidden" id="tab-content-3">
      <?php include 'unit.composer.3.php'; ?>
    </div>
    <div class="tab-content hidden" id="tab-content-4">
      <?php Weapon::fire('unit_composer_4_before', array($segment)); ?>
      <div id="form-<?php echo $page->id ? 'repair' : 'ignite'; ?>-preview"></div>
      <?php Weapon::fire('unit_composer_4_after', array($segment)); ?>
    </div>
    <?php include 'unit.composer.4.php'; ?>
  </form>
</div>