<form class="form-repair form-tag" id="form-repair" action="<?php echo $config->url_current; ?>" method="post">
  <?php $id = array(); echo $messages; ?>
  <?php echo Form::hidden('token', $token); ?>
  <table class="table-bordered table-full-width">
    <thead>
      <tr>
        <th class="text-right" style="width:3em;"><?php echo $speak->id; ?></th>
        <th><?php echo $speak->name; ?></th>
        <th><?php echo $speak->slug; ?></th>
        <th><?php echo $speak->description; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($files as $tag): $id[] = $tag->id; ?>
      <tr>
        <td class="text-right">
        <?php echo Form::hidden('id[]', $tag->id); ?>
        <span><?php echo $tag->id; ?></span>
        </td>
        <td>
        <?php echo Form::text('name[]', $tag->name, null, array(
            'class' => 'input-block',
            'readonly' => ! Guardian::happy(1) ? true : null
        )); ?>
        </td>
        <td>
        <?php echo Form::text('slug[]', $tag->slug, null, array(
            'class' => 'input-block',
            'readonly' => ! Guardian::happy(1) ? true : null
        )); ?>
        </td>
        <td>
        <?php echo Form::text('description[]', Converter::toText($tag->description), null, array(
            'class' => 'input-block',
            'readonly' => ! Guardian::happy(1) ? true : null
        )); ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <tr>
        <td class="text-right">
        <?php echo Form::text('id[]', max($id) + 1, null, array(
            'class' => array(
                'input-block',
                'na'
            ),
            'autocomplete' => 'off'
        )); ?>
        </td>
        <td>
        <?php echo Form::text('name[]', Guardian::wayback('name.' . (max($id) + 1)), null, array(
            'class' => array(
                'input-block',
                'ignite'
            )
        )); ?>
        </td>
        <td>
        <?php echo Form::text('slug[]', Guardian::wayback('slug.' . (max($id) + 1)), null, array(
            'class' => 'input-block'
        )); ?>
        </td>
        <td>
        <?php echo Form::text('description[]', Guardian::wayback('description.' . (max($id) + 1)), null, array(
            'class' => 'input-block'
        )); ?>
        </td>
      </tr>
      <tr class="row-more-less" data-min="<?php echo max($id) + 1; ?>" data-max="9999">
        <td colspan="4"><?php echo Jot::btn('default.small:plus-circle', $speak->more, '#row:more', array('class' => 'row-more')); ?> <?php echo Jot::btn('default.small:minus-circle', $speak->less, '#row:less', array('class' => 'row-less')); ?></td>
      </tr>
    </tbody>
  </table>
  <p><?php echo Jot::button('action', $speak->update); ?></p>
</form>