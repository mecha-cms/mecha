<form class="form-tag" action="<?php echo $config->url_current; ?>" method="post">
  <?php $ids = array(); echo $messages; ?>
  <input name="token" type="hidden" value="<?php echo $token; ?>">
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
      <?php foreach($files as $tag): $ids[] = $tag->id; ?>
      <tr>
        <td class="text-right"><input name="id[]" type="hidden" value="<?php echo $tag->id; ?>"><span><?php echo $tag->id; ?></span></td>
        <td><input name="name[]" type="text" class="input-block" value="<?php echo $tag->name; ?>"<?php echo Guardian::get('status') != 'pilot' ? ' readonly' : ""; ?>></td>
        <td><input name="slug[]" type="text" class="input-block" value="<?php echo $tag->slug; ?>"<?php echo Guardian::get('status') != 'pilot' ? ' readonly' : ""; ?>></td>
        <td><input name="description[]" type="text" class="input-block" value="<?php echo Text::parse($tag->description, '->encoded_html'); ?>"<?php echo Guardian::get('status') != 'pilot' ? ' readonly' : ""; ?>></td>
      </tr>
      <?php endforeach; ?>
      <tr>
        <td class="text-right"><input name="id[]" type="text" class="input-block no-appearance" value="<?php $id_max = (max($ids) + 1); echo $id_max; ?>" autocomplete="off"></td>
        <td><input name="name[]" type="text" class="input-block" value="<?php echo Guardian::wayback('name.' . $id_max); ?>"></td>
        <td><input name="slug[]" type="text" class="input-block" value="<?php echo Guardian::wayback('slug.' . $id_max); ?>"></td>
        <td><input name="description[]" type="text" class="input-block" value="<?php echo Guardian::wayback('description.' . $id_max); ?>"></td>
      </tr>
      <tr class="row-more-less" data-min="3" data-max="9999">
        <td colspan="4"><a class="btn btn-small btn-default row-more" href="#row:more"><i class="fa fa-plus-circle"></i> <?php echo $speak->more; ?></a> <a class="btn btn-small btn-default row-less" href="#row:less"><i class="fa fa-minus-circle"></i> <?php echo $speak->less; ?></a></td>
      </tr>
    </tbody>
  </table>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button></p>
</form>