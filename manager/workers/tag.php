<form class="form-tag" action="<?php echo $config->url_current; ?>" method="post">
  <?php $ids = array(); echo Notify::read(); ?>
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <table class="table-bordered table-full">
    <thead>
      <tr>
        <th style="width:2.5em;" class="text-right"><?php echo $speak->id; ?></th>
        <th><?php echo $speak->name; ?></th>
        <th><?php echo $speak->slug; ?></th>
        <th><?php echo $speak->description; ?></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="text-right"><input name="id[]" type="hidden" value="0">0</td>
        <td><input name="name[]" type="text" class="input-block" value="<?php echo $speak->untagged; ?>" readonly></td>
        <td><input name="slug[]" type="text" class="input-block" value="<?php echo Text::parse($speak->untagged)->to_slug; ?>" readonly></td>
        <td><input name="description[]" type="text" class="input-block" value="<?php echo Text::parse(Get::tagsBy(0)->description)->to_encoded_html; ?>"></td>
      </tr>
      <?php foreach($pages as $tag): ?>
      <?php $ids[] = $tag->id; if($tag->id !== 0): ?>
      <tr>
        <td class="text-right"><input name="id[]" type="hidden" value="<?php echo $tag->id; ?>"><?php echo $tag->id; ?></td>
        <td><input name="name[]" type="text" class="input-block" value="<?php echo $tag->name; ?>"></td>
        <td><input name="slug[]" type="text" class="input-block" value="<?php echo $tag->slug; ?>"></td>
        <td><input name="description[]" type="text" class="input-block" value="<?php echo Text::parse($tag->description)->to_encoded_html; ?>"></td>
      </tr>
      <?php endif; ?>
      <?php endforeach; ?>
      <tr>
        <td class="text-right"><input name="id[]" type="hidden" value="<?php echo (max($ids) + 1); ?>"><?php echo (max($ids) + 1); ?></td>
        <td><input name="name[]" type="text" class="input-block" value=""></td>
        <td><input name="slug[]" type="text" class="input-block" value=""></td>
        <td><input name="description[]" type="text" class="input-block" value=""></td>
      </tr>
    </tbody>
  </table>
  <div class="grid-group">
    <span class="grid span-6"><button class="btn btn-primary btn-update" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button></span>
  </div>
</form>