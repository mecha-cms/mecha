<?php echo $messages; ?>
<form class="form-repair form-field" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->title; ?></span>
    <span class="grid span-5"><input name="title" type="text" class="input-block" value="<?php echo Text::parse(Guardian::wayback('title', $file->title), '->encoded_html'); ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->key; ?></span>
    <span class="grid span-5"><input name="key" type="text" class="input-block" value="<?php echo Guardian::wayback('key', $the_key); ?>"></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->type; ?></span>
    <span class="grid span-5">
      <select name="type">
      <?php

      $options = array(
          't' => $speak->text,
          's' => $speak->summary,
          'b' => $speak->boolean,
          'o' => $speak->option
      );

      $cache = Guardian::wayback('type', $file->type);
      foreach($options as $k => $v) {
          echo '<option value="' . $k . '"' . ($cache[0] == $k ? ' selected' : "") . '>' . $v . '</option>';
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

      if( ! isset($file->scope)) $file->scope = "";

      $options = array(
          'article' => $speak->article,
          'page' => $speak->page,
          "" => $speak->article . ' ' . strtolower($speak->and) . ' ' . $speak->page
      );

      $cache = Guardian::wayback('scope', $file->scope);
      foreach($options as $k => $v) {
          echo '<option value="' . $k . '"' . ($cache == $k ? ' selected' : "") . '>' . $v . '</option>';
      }

      if($file->value === true) $file->value = 'true';
      if($file->value === false) $file->value = 'false';
      if($file->value === null) $file->value = 'null';
      if($file->value === TRUE) $file->value = 'TRUE';
      if($file->value === FALSE) $file->value = 'FALSE';
      if($file->value === NULL) $file->value = 'NULL';

      ?>
      </select>
    </span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->value; ?></span>
    <span class="grid span-5"><textarea name="value" class="textarea-block"><?php echo Text::parse(Guardian::wayback('value', $file->value), '->encoded_html'); ?></textarea></span>
  </label>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <span class="grid span-5">
      <?php if($the_key): ?>
      <button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button> <a class="btn btn-destruct" href="<?php echo $config->url . '/' . $config->manager->slug . '/field/kill/key:' . $the_key; ?>"><i class="fa fa-times-circle"></i> <?php echo $speak->delete; ?></a>
      <?php else: ?>
      <button class="btn btn-construct" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->create; ?></button>
      <?php endif; ?>
    </span>
  </div>
</form>
<hr>
<?php echo Config::speak('file:field'); ?>