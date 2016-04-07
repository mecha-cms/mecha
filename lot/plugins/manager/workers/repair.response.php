<?php $hooks = array($page, $segment); ?>
<div class="tab-area">
  <div class="tab-button-area">
    <?php Weapon::fire('tab_button_before', $hooks); ?>
    <a class="tab-button active" href="#tab-content-1"><?php echo Jot::icon('pencil', 'fw') . ' ' . (strpos($config->url_path, '/id:') === false ? $speak->new : $speak->edit); ?></a>
    <a class="tab-button" href="#tab-content-2"><?php echo Jot::icon('th-list', 'fw') . ' ' . $speak->fields; ?></a>
    <?php Weapon::fire('tab_button_after', $hooks); ?>
  </div>
  <div class="tab-content-area">
    <?php echo $messages; ?>
    <form class="form-<?php echo strpos($config->url_path, '/id:') === false ? 'ignite' : 'repair'; ?> form-<?php echo $segment[0]; ?>" id="form-<?php echo strpos($config->url_path, '/id:') === false ? 'ignite' : 'repair'; ?>" action="<?php echo $config->url_current . str_replace('&', '&amp;', $config->url_query); ?>" method="post" enctype="multipart/form-data">
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
        <?php Weapon::fire('action_before', $hooks); ?>
        <?php if(strpos($config->url_path, '/id:') === false): ?>
          <?php echo Jot::button('construct', $speak->create, 'extension:.txt'); ?>
          <?php echo Jot::button('action:clock-o', $speak->save, 'extension:.hold'); ?>
        <?php else: ?>
          <?php echo Jot::button('action', $speak->update, 'extension:.' . File::E($page->path)); ?>
          <?php if($page->state === 'approved'): ?>
            <?php echo Jot::button('action:history', $speak->unapprove, 'extension:.hold'); ?>
          <?php else: ?>
            <?php echo Jot::button('construct', $speak->approve, 'extension:.txt'); ?>
          <?php endif; ?>
          <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/' . $segment[0] . '/kill/id:' . $page->id); ?>
        <?php endif; ?>
        <?php Weapon::fire('action_after', $hooks); ?>
      </p>
    </form>
  </div>
</div>