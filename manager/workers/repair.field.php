<?php echo $messages; ?>
<form class="form-repair form-field" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->title; ?></span>
    <span class="grid span-5">
    <?php echo Form::text('title', Guardian::wayback('title', $file->title), null, array(
        'class' => 'input-block'
    )); ?>
    </span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->key; ?></span>
    <span class="grid span-5">
    <?php echo Form::text('key', Guardian::wayback('key', $the_key), null, array(
        'class' => 'input-block'
    )); ?>
    </span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->type; ?></span>
    <span class="grid span-5">
    <?php

    $cache = Guardian::wayback('type', $file->type);

    echo Form::select('type', array(
        't' => $speak->text,
        's' => $speak->summary,
        'b' => $speak->boolean,
        'o' => $speak->option
    ), $cache[0]);

    ?>
    </span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->scope; ?></span>
    <span class="grid span-5">
    <?php

    $file = (object) Converter::str($file);
    $cache = Guardian::wayback('scope', isset($file->scope) ? $file->scope : "");

    echo Form::select('scope', array(
        'article' => $speak->article,
        'page' => $speak->page,
        "" => $speak->article . '/' . $speak->page,
        'comment' => $speak->comment
    ), $cache);

    ?>
    </span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->value; ?></span>
    <span class="grid span-5">
    <?php echo Form::textarea('value', Guardian::wayback('value', $file->value), null, array(
        'class' => 'input-block'
    )); ?>
    </span>
  </label>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <span class="grid span-5">
      <?php if($the_key): ?>
      <?php echo UI::button('action', $speak->update); ?>
      <?php echo UI::btn('destruct', $speak->delete, $config->manager->slug . '/field/kill/key:' . $the_key); ?>
      <?php else: ?>
      <?php echo UI::button('construct', $speak->create); ?>
      <?php endif; ?>
    </span>
  </div>
</form>
<hr>
<?php echo Config::speak('file:field'); ?>