<form class="form-tag" action="<?php echo $config->url_current; ?>" method="post">
  <?php $ids = array(); echo $messages; ?>
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <table class="table-bordered table-full">
    <colgroup>
      <col style="width:5em;">
      <col>
      <col>
      <col>
    </colgroup>
    <thead>
      <tr>
        <th class="text-right"><?php echo $speak->id; ?></th>
        <th><?php echo $speak->name; ?></th>
        <th><?php echo $speak->slug; ?></th>
        <th><?php echo $speak->description; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($files as $tag): $ids[] = $tag->id; ?>
      <tr>
        <td class="text-right"><input name="id[]" type="hidden" value="<?php echo $tag->id; ?>"><?php echo $tag->id; ?></td>
        <td><input name="name[]" type="text" class="input-block" value="<?php echo $tag->name; ?>"></td>
        <td><input name="slug[]" type="text" class="input-block" value="<?php echo $tag->slug; ?>"></td>
        <td><input name="description[]" type="text" class="input-block" value="<?php echo Text::parse($tag->description)->to_encoded_html; ?>"></td>
      </tr>
      <?php endforeach; ?>
      <tr>
        <td class="text-right"><input name="id[]" type="text" class="input-block text-center" style="padding-right:2px;padding-left:2px;" value="<?php $id_max = (max($ids) + 1); echo $id_max; ?>"></td>
        <td><input name="name[]" type="text" class="input-block" value="<?php echo Guardian::wayback('name.' . $id_max); ?>"></td>
        <td><input name="slug[]" type="text" class="input-block" value="<?php echo Guardian::wayback('slug.' . $id_max); ?>"></td>
        <td><input name="description[]" type="text" class="input-block" value="<?php echo Guardian::wayback('description.' . $id_max); ?>"></td>
      </tr>
      <tr class="row-more-less" data-min="3" data-max="9999">
        <td colspan="4"><a class="btn btn-sm btn-default btn-increase" href="#add"><i class="fa fa-plus-circle"></i> <?php echo $speak->more; ?></a> <a class="btn btn-sm btn-default btn-decrease" href="#remove"><i class="fa fa-minus-circle"></i> <?php echo $speak->less; ?></a></td>
      </tr>
    </tbody>
  </table>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button></p>
</form>