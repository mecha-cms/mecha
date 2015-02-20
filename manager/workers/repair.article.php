<?php $segment = 'article'; ?>
<div class="tab-area">
  <?php if(strpos($config->url_current, 'id:') !== false): ?>
  <a class="tab" href="<?php echo $config->url . '/' . $config->manager->slug;  ?>/article/ignite" data-confirm-text="<?php echo $speak->notify_confirm_page_leave; ?>"><i class="fa fa-fw fa-plus-square"></i> <?php echo $speak->new; ?></a>
  <?php endif; ?>
  <a class="tab active" href="#tab-content-1"><i class="fa fa-fw fa-pencil"></i> <?php echo $speak->compose; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-fw fa-leaf"></i> <?php echo $speak->manager->title_custom_css_and_js; ?></a>
  <a class="tab" href="#tab-content-3"><i class="fa fa-fw fa-th-list"></i> <?php echo $speak->fields; ?></a>
  <a class="tab ajax-post" href="#tab-content-4" data-url="<?php echo $config->url . '/' . $config->manager->slug . '/ajax/preview:' . $segment; ?>" data-loading-text="<?php echo $speak->previewing; ?>&hellip;" data-error-text="<?php echo $speak->error; ?>." data-scope="#form-compose" data-destination="#form-compose-preview"><i class="fa fa-fw fa-eye"></i> <?php echo $speak->preview; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <form class="form-compose" id="form-compose" action="<?php echo $config->url_current; ?>" method="post">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <div class="tab-content" id="tab-content-1">
      <?php Weapon::fire('unit_composer_1_before', array($segment)); ?>
      <?php if(strpos($config->url_current, 'id:') !== false): ?>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->date; ?></span>
        <span class="grid span-5"><input name="date" type="text" class="input-block" value="<?php echo Guardian::wayback('date', $default->date->W3C); ?>" placeholder="0000-00-00T00:00:00+00:00"></span>
      </label>
      <?php endif; ?>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-5"><input name="title" type="text" class="input-block" value="<?php echo Text::parse(Guardian::wayback('title', $default->title), '->encoded_html'); ?>" placeholder="<?php echo $speak->manager->placeholder_title; ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-5"><input name="slug" type="text" class="input-block" value="<?php echo Guardian::wayback('slug', $default->slug); ?>" placeholder="<?php echo Text::parse($speak->manager->placeholder_title, '->slug'); ?>"></span>
      </label>
      <?php include 'unit.composer.1.php'; ?>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->description; ?></span>
        <span class="grid span-5"><textarea name="description" class="textarea-block" placeholder="<?php echo Config::speak('manager.placeholder_description', array(strtolower($speak->article))); ?>"><?php echo Text::parse(Guardian::wayback('description', $default->description), '->encoded_html'); ?></textarea></span>
      </label>
      <?php

      $tags = array();
      $tags_wayback = Guardian::wayback('kind', Mecha::A($default->kind));

      foreach(Get::tags() as $tag) {
          if($tag && $tag->id !== 0) {
              $tags[] = '<div><label><input type="checkbox" name="kind[]" value="' . $tag->id . '"' . (in_array((int) $tag->id, $tags_wayback) ? ' checked' : "") . '> <span>' . $tag->name . '</span></label></div>';
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
        <span class="grid span-5"><input name="author" type="text" value="<?php echo Text::parse(Guardian::wayback('author', $default->author), '->encoded_html'); ?>"<?php echo Guardian::get('status') != 'pilot' ? ' readonly' : ""; ?>></span>
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
      <div id="form-compose-preview"></div>
      <?php Weapon::fire('unit_composer_4_after', array($segment)); ?>
    </div>
    <hr>
    <p><?php if(strpos($config->url_current, 'id:') === false): ?><button class="btn btn-construct" name="action" type="submit" value="publish"><i class="fa fa-check-circle"></i> <?php echo $speak->publish; ?></button> <button class="btn btn-action" name="action" type="submit" value="save"><i class="fa fa-clock-o"></i> <?php echo $speak->save; ?></button><?php else: ?><?php if(Guardian::wayback('state', $default->state) == 'published'): ?><button class="btn btn-action" name="action" type="submit" value="publish"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button> <button class="btn btn-action" name="action" type="submit" value="save"><i class="fa fa-history"></i> <?php echo $speak->unpublish; ?></button><?php else: ?><button class="btn btn-construct" name="action" type="submit" value="publish"><i class="fa fa-check-circle"></i> <?php echo $speak->publish; ?></button> <button class="btn btn-action" name="action" type="submit" value="save"><i class="fa fa-clock-o"></i> <?php echo $speak->save; ?></button><?php endif; ?> <a class="btn btn-destruct" href="<?php echo $config->url . '/' . $config->manager->slug . '/article/kill/id:' . Guardian::wayback('id', $default->id); ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a><?php endif; ?></p>
  </form>
</div>