<?php $hooks = array($page, $segment); ?>
<div class="tab-button-area">
  <?php Weapon::fire('tab_button_before', $hooks); ?>
  <a class="tab-button active" href="#tab-content-1"><?php echo Jot::icon('pencil', 'fw') . ' ' . (strpos($config->url_path, '/id:') === false ? $speak->new : $speak->edit); ?></a>
  <a class="tab-button" href="#tab-content-2"><?php echo Jot::icon('th-list', 'fw') . ' ' . $speak->fields; ?></a>
  <?php Weapon::fire('tab_button_after', $hooks); ?>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <form class="form-repair form-<?php echo $segment[0]; ?>" id="form-repair" action="<?php echo $config->url_current; ?>" method="post" enctype="multipart/form-data">
    <?php echo Form::hidden('token', $token); ?>
    <?php Weapon::fire('tab_content_before', $hooks); ?>
    <div class="tab-content" id="tab-content-1">
      <?php Weapon::fire('tab_content_1_before', $hooks); ?>
      <?php Weapon::fire('tab_content_1_after', $hooks); ?>
    </div>
    <div class="tab-content hidden" id="tab-content-2">
      <?php Weapon::fire('tab_content_2_before', $hooks); ?>
      <?php Weapon::fire('tab_content_2_after', $hooks); ?>
    </div>
    <?php Weapon::fire('tab_content_after', $hooks); ?>
    <hr>
    <p>
      <?php if(strpos($config->url_path, '/id:') === false): ?>
      <?php echo Jot::button('construct', $speak->create, 'action:publish'); ?>
      <?php echo Jot::button('action:clock-o', $speak->save, 'action:save'); ?>
      <?php else: ?>
      <?php if(Guardian::wayback('state', $page->state) === 'approved'): ?>
      <?php echo Jot::button('action', $speak->update, 'action:publish'); ?>
      <?php echo Jot::button('action:history', $speak->unapprove, 'action:save'); ?>
      <?php else: ?>
      <?php echo Jot::button('construct', $speak->approve, 'action:publish'); ?>
      <?php endif; ?>
      <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/' . $segment[0] . '/kill/id:' . Guardian::wayback('id', $page->id)); ?>
      <?php endif; ?>
    </p>
  </form>
</div>