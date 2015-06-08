<?php $segment = 'article'; ?>
<div class="tab-area">
  <?php if(strpos($config->url_path, '/id:') !== false): ?>
  <a class="tab" href="<?php echo $config->url . '/' . $config->manager->slug;  ?>/article/ignite" data-confirm-text="<?php echo $speak->notify_confirm_page_leave; ?>"><?php echo Jot::icon('plus-square', 'fw') . ' ' . $speak->new; ?></a>
  <?php endif; ?>
  <a class="tab active" href="#tab-content-1"><?php echo Jot::icon('pencil', 'fw') . ' ' . $speak->compose; ?></a>
  <a class="tab" href="#tab-content-2"><?php echo Jot::icon('leaf', 'fw') . ' ' . $speak->manager->title_custom_css_and_js; ?></a>
  <a class="tab" href="#tab-content-3"><?php echo Jot::icon('th-list', 'fw') . ' ' . $speak->fields; ?></a>
  <a class="tab ajax-post" href="#tab-content-4" data-action-url="<?php echo $config->url . '/' . $config->manager->slug . '/ajax/preview:' . $segment; ?>" data-text-progress="<?php echo $speak->previewing; ?>&hellip;" data-text-error="<?php echo $speak->error; ?>." data-scope="#form-<?php echo $default->id ? 'repair' : 'ignite'; ?>" data-target="#form-<?php echo $default->id ? 'repair' : 'ignite'; ?>-preview"><?php echo Jot::icon('eye', 'fw') . ' ' . $speak->preview; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <form class="form-<?php echo $default->id ? 'repair' : 'ignite'; ?> form-article" id="form-<?php echo $default->id ? 'repair' : 'ignite'; ?>" action="<?php echo $config->url_current; ?>" method="post">
    <?php echo Form::hidden('token', $token); ?>
    <div class="tab-content" id="tab-content-1">
      <?php Weapon::fire('unit_composer_1_before', array($segment)); ?>
      <?php if(strpos($config->url_path, '/id:') !== false): ?>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->date; ?></span>
        <span class="grid span-5">
        <?php echo Form::text('date', Guardian::wayback('date', $default->date->W3C), date('c'), array(
            'class' => 'input-block'
        )); ?>
        </span>
      </label>
      <?php endif; ?>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-5">
        <?php echo Form::text('title', Guardian::wayback('title', $default->title), $speak->manager->placeholder_title, array(
            'class' => 'input-block'
        )); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-5">
        <?php echo Form::text('slug', Guardian::wayback('slug', $default->slug), Text::parse($speak->manager->placeholder_title, '->slug'), array(
            'class' => 'input-block'
        )); ?>
        </span>
      </label>
      <?php include 'unit.composer.1.php'; ?>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->description; ?></span>
        <span class="grid span-5">
        <?php echo Form::textarea('description', Guardian::wayback('description', $default->description), Config::speak('manager.placeholder_description', strtolower($speak->article)), array(
            'class' => 'textarea-block'
        )); ?>
        </span>
      </label>
      <?php

      $tags = array();
      $tags_wayback = Guardian::wayback('kind', Mecha::A($default->kind));

      foreach(Get::tags() as $tag) {
          if($tag && $tag->id !== 0) {
              $tags[] = '<div>' . Form::checkbox('kind[]', $tag->id, in_array((int) $tag->id, $tags_wayback), $tag->name) . '</div>';
          }
      }

      ?>
      <?php if(count($tags) > 0): ?>
      <div class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->tags; ?></span>
        <div class="grid span-5"><?php echo implode("", $tags); ?></div>
      </div>
      <?php endif; ?>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->author; ?></span>
        <span class="grid span-5">
          <?php if(Guardian::get('status') === 'pilot'): ?>
          <?php echo Form::text('author', Guardian::wayback('author', $default->author)); ?>
          <?php else: ?>
          <?php echo Form::hidden('author', $default->author); ?>
          <span class="form-static"><?php echo Jot::icon('lock') . ' ' . $default->author; ?></span>
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
      <div id="form-<?php echo $default->id ? 'repair' : 'ignite'; ?>-preview"></div>
      <?php Weapon::fire('unit_composer_4_after', array($segment)); ?>
    </div>
    <hr>
    <p>
      <?php if(strpos($config->url_path, '/id:') === false): ?>
      <?php echo Jot::button('construct', $speak->publish, 'action:publish'); ?>
      <?php echo Jot::button('action:clock-o', $speak->save, 'action:save'); ?>
      <?php else: ?>
      <?php if(Guardian::wayback('state', $default->state) === 'published'): ?>
      <?php echo Jot::button('action', $speak->update, 'action:publish'); ?>
      <?php echo Jot::button('action:history', $speak->unpublish, 'action:save'); ?>
      <?php else: ?>
      <?php echo Jot::button('construct', $speak->publish, 'action:publish'); ?>
      <?php echo Jot::button('action:clock-o', $speak->save, 'action:save'); ?>
      <?php endif; ?>
      <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/article/kill/id:' . Guardian::wayback('id', $default->id)); ?>
      <?php endif; ?>
    </p>
  </form>
</div>