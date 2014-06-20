<?php echo Notify::read(); ?>
<form class="form-repair form-field" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->title; ?></span>
    <span class="grid span-5"><input name="title" type="text" class="input-block" value="<?php echo $page->title; ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->key; ?></span>
    <span class="grid span-5"><input name="key" type="text" class="input-block" value="<?php echo $config->key ? $config->key : ""; ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->type; ?></span>
    <span class="grid span-5">
      <select name="type">
      <?php

      $options = array(
          'text' => $speak->text,
          'summary' => $speak->summary,
          'option' => $speak->options,
          'boolean' => $speak->boolean
      );

      foreach($options as $k => $v) {
          echo '<option value="' . $k . '"' . ($k == $page->type ? ' selected' : "") . '>' . $v . '</option>';
      }

      ?>
      </select>
    </span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->scope; ?></span>
    <span class="grid span-5">
      <select name="scope">
      <?php

      if( ! isset($page->scope)) $page->scope = 'all';

      $options = array(
          'article' => $speak->article,
          'page' => $speak->page,
          'all' => $speak->both
      );

      foreach($options as $k => $v) {
          echo '<option value="' . $k . '"' . ($k == $page->scope ? ' selected' : "") . '>' . $v . '</option>';
      }

      ?>
      </select>
    </span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->value; ?></span>
    <span class="grid span-5"><textarea name="value" class="input-block"><?php echo isset($page->value) ? $page->value : ""; ?></textarea></span>
  </label>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <span class="grid span-5">
      <?php if($config->key): ?>
      <button class="btn btn-primary btn-update" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button> <a class="btn btn-danger btn-delete" href="<?php echo $config->url . '/' . $config->manager->slug . '/field/kill/key:' . $config->key; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a>
      <?php else: ?>
      <button class="btn btn-success btn-create" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->create; ?></button>
      <?php endif; ?>
    </span>
  </div>
</form>
<hr>
<?php echo Config::speak('file:field'); ?>