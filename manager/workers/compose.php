<div class="tab-area cf">
  <?php if($config->editor_mode != 'ignite'): ?>
  <a class="tab" href="<?php echo $config->url . '/' . $config->manager->slug . '/' . $config->editor_type; ?>/ignite"><i class="fa fa-fw fa-plus-square"></i> <?php echo $speak->new; ?></a>
  <?php endif; ?>
  <a class="tab active" href="#tab-content-1"><i class="fa fa-fw fa-pencil"></i> <?php echo $speak->manager->title_compose; ?></a>
  <a class="tab" href="#tab-content-2"><i class="fa fa-fw fa-leaf"></i> <?php echo $speak->manager->title_custom_css_and_js; ?></a>
  <a class="tab" href="#tab-content-3"><i class="fa fa-fw fa-th-list"></i> <?php echo $speak->fields; ?></a>
  <a class="tab" href="#tab-content-4"><i class="fa fa-fw fa-eye"></i> <?php echo $speak->preview; ?></a>
</div>
<div class="tab-content-area">

  <?php echo Notify::read(); ?>

  <form class="form-compose" action="<?php $cache = Guardian::wayback(); echo $config->url_current; ?>" method="post">

    <input type="hidden" name="token" value="<?php echo Guardian::makeToken(); ?>">

    <div class="tab-content" id="tab-content-1">

      <?php if($config->editor_mode != 'ignite' && $config->editor_type != 'page'): ?>
      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->date; ?></span>
        <span class="grid span-5"><input name="date" type="text" class="input-block" value="<?php echo $cache['date']; ?>" placeholder="0000-00-00T00:00:00+00:00"></span>
      </label>
      <?php endif; ?>

      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-5"><input name="title" type="text" class="input-block" value="<?php echo $cache['title']; ?>" placeholder="<?php echo $speak->manager->placeholder_title; ?>"></span>
      </label>

      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-5"><input name="slug" type="text" class="input-block" value="<?php echo $cache['slug']; ?>" placeholder="<?php echo Text::parse($speak->manager->placeholder_title)->to_slug; ?>"></span>
      </label>

      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->content; ?></span>
        <span class="grid span-5"><textarea name="content" class="input-block" placeholder="<?php echo $speak->manager->placeholder_content; ?>" data-shortcodes='<?php echo json_encode(unserialize(File::open(STATE . '/shortcodes.txt')->read())); ?>'><?php echo Text::parse($cache['content'])->to_encoded_html; ?></textarea></span>
      </label>

      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->description; ?></span>
        <span class="grid span-5"><textarea name="description" class="input-block" placeholder="<?php echo $speak->manager->placeholder_description; ?>"><?php echo Text::parse($cache['description'])->to_encoded_html; ?></textarea></span>
      </label>

      <?php if($config->editor_type == 'article'): ?>
      <div class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->tags; ?></span>
        <span class="grid span-5 tags cf">
        <?php

        foreach(Get::tags() as $tag) {
            if($tag && $tag->id !== 0) {
                echo '<div><label' . (in_array($tag->id, $cache['tags']) ? ' class="selected"' : "") . '><input type="checkbox" name="tags[]" value="' . $tag->id . '"' . (in_array($tag->id, $cache['tags']) ? ' checked' : "") . '> <span>' . $tag->name . '</span></label></div>';
            }
        }

        ?>
        </span>
      </div>
      <?php endif; ?>

      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->author; ?></span>
        <span class="grid span-5"><input name="author" type="text" value="<?php echo $cache['author']; ?>"></span>
      </label>

    </div>

    <div class="tab-content hidden" id="tab-content-2">

      <div class="grid-group">
        <div class="grid span-6">
          <div><label><input name="css_live_check" type="checkbox"> <span><?php echo $speak->manager->title_live_preview_css; ?></span></label></div>
          <!-- div><label><input name="js_live_check" type="checkbox"> <span><?php echo $speak->manager->title_live_preview_js; ?></span></label></div -->
        </div>
      </div>

      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->manager->title_custom_css; ?></span>
        <span class="grid span-5"><textarea name="css" class="input-block"><?php echo $cache['css']; ?></textarea></span>
      </label>

      <label class="grid-group">
        <span class="grid span-1 form-label"><?php echo $speak->manager->title_custom_js; ?></span>
        <span class="grid span-5"><textarea name="js" class="input-block"><?php echo $cache['js']; ?></textarea></span>
      </label>

    </div>

    <div class="tab-content hidden" id="tab-content-3">
    <?php $fields = unserialize(File::open(STATE . '/fields.txt')->read()); if( ! empty($fields)): ?>
    <?php foreach($fields as $key => $value): ?>
    <input name="fields[<?php echo $key; ?>][type]" type="hidden" value="<?php echo $value['type']; ?>">
    <label class="grid-group">
      <?php

      // Custom fields ...

      $extra = Mecha::A($cache['fields']);

      if(isset($extra[$key]) && is_array($extra[$key])) {
          $extra[$key] = isset($extra[$key]['value']) ? $extra[$key]['value'] : "";
      }

      if($value['type'] == 'text') {
          echo '<span class="grid span-1 form-label">' . $value['title'] . '</span>';
          echo '<span class="grid span-5">';
          echo '<input name="fields[' . $key . '][value]" type="text" class="input-block" value="' . (isset($extra[$key]) ? $extra[$key] : "") . '">';
          echo '</span>';
      }

      if($value['type'] == 'summary') {
          echo '<span class="grid span-1 form-label">' . $value['title'] . '</span>';
          echo '<span class="grid span-5">';
          echo '<textarea name="fields[' . $key . '][value]" class="input-block">' . (isset($extra[$key]) ? $extra[$key] : "") . '</textarea>';
          echo '</span>';
      }
  
      if($value['type'] == 'boolean') {
          echo '<span class="grid span-1 form-label">&nbsp;</span>';
          echo '<span class="grid span-5">';
          echo '<input name="fields[' . $key . '][value]" type="checkbox"' . ( ! empty($extra[$key]) ? ' checked' : "") . '> <span>' . $value['title'] . '</span>';
          echo '</span>';
      }

      ?>
    </label>
    <?php endforeach; ?>
    <?php else: ?>
    <p><?php echo Config::speak('notify_empty', array(strtolower($speak->fields))); ?></p>
    <?php endif; ?>
    </div>

    <div class="tab-content hidden" id="tab-content-4">
      <div class="editor-preview"></div>
    </div>

    <hr>

    <p>
      <?php if($config->editor_mode == 'ignite'): ?>
      <button class="btn btn-success btn-publish" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->publish; ?></button>
      <?php else: ?>
      <button class="btn btn-primary btn-update" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button>
      <a class="btn btn-danger btn-delete" href="<?php echo $config->url . '/' . $config->manager->slug . '/' . $config->editor_type . '/kill/' . $cache['id']; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a>
      <?php endif; ?>
    </p>

  </form>

</div>