<form class="form-shortcode" action="<?php echo $config->url_current; ?>" method="post">
  <?php $count = 0; echo $messages; ?>
  <?php echo Form::hidden('token', $token); ?>
  <table class="table-bordered table-full-width table-sortable">
    <thead>
      <tr>
        <th class="th-icon"><?php echo UI::icon('sort'); ?></th>
        <th><?php echo $speak->key; ?></th>
        <th><?php echo $speak->value; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php if($files): ?>
      <?php foreach($files as $key => $value): $count++; ?>
      <tr draggable="true">
        <td class="handle"></td>
        <td class="align-middle">
        <?php echo Form::text('key[]', $key, '{{' . strtolower($speak->key) . '}}', array(
            'class' => 'input-block'
        )); ?>
        </td>
        <td class="align-middle">
        <?php echo Form::text('value[]', $value, null, array(
            'class' => 'input-block'
        )); ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
      <tr draggable="true">
        <td class="handle"></td>
        <td class="align-middle">
        <?php echo Form::text('key[]', "", '{{' . strtolower($speak->key) . '}}', array(
            'class' => 'input-block'
        )); ?>
        </td>
        <td class="align-middle">
        <?php echo Form::text('value[]', "", null, array(
            'class' => 'input-block'
        )); ?>
        </td>
      </tr>
      <tr class="row-more-less" data-min="<?php echo $count + 1; ?>" data-max="9999">
        <td colspan="3"><?php echo UI::btn('default.small:plus-circle', $speak->more, '#row:more', array('class' => 'row-more')); ?> <?php echo UI::btn('default.small:minus-circle', $speak->less, '#row:less', array('class' => 'row-less')); ?></td>
      </tr>
    </tbody>
  </table>
  <p><?php echo UI::button('action', $speak->update); ?></p>
</form>
<hr>
<?php echo Config::speak('file:shortcode'); ?>