<?php $FT = 'article'; ?>
<div class="tab-area">
  <?php if(strpos($config->url_current, 'id:') !== false): ?>
  <a class="tab" href="<?php echo $config->url . '/' . $config->manager->slug;  ?>/article/ignite" data-confirm-text="<?php echo $speak->notify_confirm_page_leave; ?>"><i class="fa fa-fw fa-plus-square"></i> <?php echo $speak->new; ?></a>
  <?php endif; ?>
  <a class="tab active" href="#tab-content-1"><i class="fa fa-fw fa-pencil"></i> <?php echo $speak->compose; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-fw fa-leaf"></i> <?php echo $speak->manager->title_custom_css_and_js; ?></a>
  <a class="tab" href="#tab-content-3"><i class="fa fa-fw fa-th-list"></i> <?php echo $speak->fields; ?></a>
  <a class="tab" href="#tab-content-4"><i class="fa fa-fw fa-eye"></i> <?php echo $speak->preview; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <form class="form-compose" action="<?php echo $config->url_current; ?>" method="post" data-preview-url="<?php echo $config->url . '/' . $config->manager->slug . '/ajax/preview:article'; ?>">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <div class="tab-content" id="tab-content-1">
      <?php if(strpos($config->url_current, 'id:') !== false): ?>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->date; ?></span>
        <span class="grid span-5"><input name="date" type="text" class="input-block" value="<?php echo Guardian::wayback('date', $default->date->W3C); ?>" placeholder="0000-00-00T00:00:00+00:00"></span>
      </label>
      <?php endif; ?>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-5"><input name="title" type="text" class="input-block" value="<?php echo Text::parse(Guardian::wayback('title', $default->title))->to_encoded_html; ?>" placeholder="<?php echo $speak->manager->placeholder_title; ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-5"><input name="slug" type="text" class="input-block" value="<?php echo Guardian::wayback('slug', $default->slug); ?>" placeholder="<?php echo Text::parse($speak->manager->placeholder_title)->to_slug; ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->content; ?></span>
        <span class="grid span-5">
          <?php include 'unit.composer.content.php'; ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->description; ?></span>
        <span class="grid span-5"><textarea name="description" class="textarea-block" placeholder="<?php echo $speak->manager->placeholder_description; ?>"><?php echo Text::parse(Guardian::wayback('description', $default->description))->to_encoded_html; ?></textarea></span>
      </label>
      <?php

      $tags = array();

      foreach(Get::tags() as $tag) {
          if($tag && $tag->id !== 0) {
              $tags[] = '<div><label><input type="checkbox" name="kind[]" value="' . $tag->id . '"' . (in_array($tag->id, Guardian::wayback('kind', Mecha::A($default->kind))) ? ' checked' : "") . '> <span>' . $tag->name . '</span></label></div>';
          }
      }

      ?>
      <?php if(count($tags) > 1): ?>
      <div class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->tags; ?></span>
        <span class="grid span-5"><?php echo implode("", $tags); ?></span>
      </div>
      <?php endif; ?>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->author; ?></span>
        <span class="grid span-5"><input name="author" type="text" value="<?php echo Guardian::wayback('author', $default->author); ?>"></span>
      </label>
    </div>
    <div class="tab-content hidden" id="tab-content-2">
      <?php include 'unit.composer.css-js.php'; ?>
    </div>
    <div class="tab-content hidden" id="tab-content-3">
      <?php include 'unit.composer.fields.php'; ?>
    </div>
    <div class="tab-content hidden" id="tab-content-4">
      <div class="editor-preview" data-progress-text="<?php echo $speak->previewing; ?>&hellip;" data-error-text="<?php echo $speak->error; ?>."></div>
    </div>
    <hr>
    <p>
      <?php if(strpos($config->url_current, 'id:') === false): ?>
      <button class="btn btn-construct" name="action" type="submit" value="publish"><i class="fa fa-check-circle"></i> <?php echo $speak->publish; ?></button> <button class="btn btn-action" name="action" type="submit" value="save"><i class="fa fa-clock-o"></i> <?php echo $speak->save; ?></button>
      <?php else: ?>
      <?php if(Guardian::wayback('state', $default->state) == 'published'): ?><button class="btn btn-action" name="action" type="submit" value="publish"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button> <button class="btn btn-action" name="action" type="submit" value="save"><i class="fa fa-history"></i> <?php echo $speak->unpublish; ?></button><?php else: ?><button class="btn btn-construct" name="action" type="submit" value="publish"><i class="fa fa-check-circle"></i> <?php echo $speak->publish; ?></button> <button class="btn btn-action" name="action" type="submit" value="save"><i class="fa fa-clock-o"></i> <?php echo $speak->save; ?></button><?php endif; ?> <a class="btn btn-destruct" href="<?php echo $config->url . '/' . $config->manager->slug . '/article/kill/id:' . Guardian::wayback('id', $default->id); ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a>
      <?php endif; ?>
    </p>
  </form>
</div>