<?php $hooks = array($page, $segment); ?>
<div class="tab-area">
  <div class="tab-button-area">
    <?php Weapon::fire('tab_button_before', $hooks); ?>
    <a class="tab-button active" href="#tab-content-1"><?php echo Jot::icon('pencil', 'fw') . ' ' . $speak->compose; ?></a>
    <a class="tab-button" href="#tab-content-2"><?php echo Jot::icon('leaf', 'fw') . ' ' . $speak->manager->title_css_and_js_custom; ?></a>
    <a class="tab-button" href="#tab-content-3"><?php echo Jot::icon('th-list', 'fw') . ' ' . $speak->fields; ?></a>
    <?php Weapon::fire('tab_button_after', $hooks); ?>
  </div>
  <div class="tab-content-area">
    <?php echo $messages; ?>
    <form class="form-<?php echo $page->id ? 'repair' : 'ignite'; ?> form-<?php echo $segment; ?>" id="form-<?php echo $page->id ? 'repair' : 'ignite'; ?>" action="<?php echo $config->url_current . str_replace('&', '&amp;', $config->url_query); ?>" method="post" enctype="multipart/form-data">
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
      <div class="tab-content hidden" id="tab-content-3">
        <?php Weapon::fire('tab_content_3_before', $hooks); ?>
        <?php Weapon::fire('tab_content_3_after', $hooks); ?>
      </div>
      <?php Weapon::fire('tab_content_after', $hooks); ?>
      <hr>
      <p>
        <?php Weapon::fire('action_before', $hooks); ?>
        <?php if(strpos($config->url_path, '/id:') === false): ?>
          <?php echo Jot::button('construct', $speak->publish, 'extension:.txt'); ?>
          <?php echo Jot::button('action:clock-o', $speak->save, 'extension:.draft'); ?>
        <?php else: ?>
          <?php if($page->state === 'published'): ?>
            <?php echo Jot::button('action', $speak->update, 'extension:.txt'); ?>
            <?php echo Jot::button('action:history', $speak->unpublish, 'extension:.draft'); ?>
          <?php else: ?>
            <?php echo Jot::button('construct', $speak->publish, 'extension:.txt'); ?>
            <?php echo Jot::button('action:clock-o', $speak->save, 'extension:.draft'); ?>
          <?php endif; ?>
          <?php if($page->state === 'archived'): ?>
            <?php echo Jot::button('action:history', $speak->archive, 'extension:.archive'); ?>
          <?php endif; ?>
          <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/' . $segment . '/kill/id:' . $page->id); ?>
        <?php endif; ?>
        <?php Weapon::fire('action_after', $hooks); ?>
      </p>
    </form>
  </div>
</div>
<?php if(strpos($config->url_path, '/id:') === false): ?>
<script>!function(e){e(function(){e.slug("title","slug","-")})}(DASHBOARD.$);</script>
<?php endif; ?>