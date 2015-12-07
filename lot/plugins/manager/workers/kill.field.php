<?php echo $messages; ?>
<table class="table-bordered table-full-width">
  <thead>
    <tr>
      <th><?php echo $speak->title; ?></th>
      <th><?php echo $speak->key; ?></th>
      <th><?php echo $speak->type; ?></th>
      <th><?php echo $speak->scope; ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><?php echo $file->title; ?></td>
      <td><code><?php echo $id; ?></code></td>
      <?php

      $s = Mecha::alter($file->type[0], array(
          't' => 'Text',
          'b' => 'Boolean',
          'o' => 'Option',
          'f' => 'File',
          'c' => 'Composer',
          'e' => 'Editor'
      ), 'Summary');

      ?>
      <td><?php echo Jot::em('info', $s); ?></td>
      <td><?php echo isset($file->scope) ? str_replace(',', '/', $file->scope) : '<em>' . $speak->all . '</em>'; ?></td>
    </tr>
  </tbody>
</table>
<form class="form-kill form-field" id="form-kill" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <?php echo Jot::button('action', $speak->yes); ?> <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/field/repair/key:' . $id); ?>
</form>