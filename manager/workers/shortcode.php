<form class="form-shortcode" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Notify::read(); ?>
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <table class="table-bordered table-full">
    <thead>
      <tr>
        <th><?php echo $speak->key; ?></th>
        <th><?php echo $speak->value; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php if($pages): ?>
      <?php foreach($pages as $key => $value): ?>
      <tr>
        <td><input name="keys[]" type="text" class="input-block" value="<?php echo Text::parse($key)->to_encoded_html; ?>" placeholder="{{<?php echo strtolower($speak->key); ?>}}"<?php echo ($key === '{{url}}' || $key === '{{asset}}') ? ' readonly' : ""; ?>></td>
        <td><input name="values[]" type="text" class="input-block" value="<?php echo Text::parse($value)->to_encoded_html; ?>"<?php echo ($key === '{{url}}' || $key === '{{asset}}') ? ' readonly' : ""; ?>></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
      <tr>
        <td><input name="keys[]" type="text" class="input-block" placeholder="{{<?php echo strtolower($speak->key); ?>}}"></td>
        <td><input name="values[]" type="text" class="input-block"></td>
      </tr>
      <tr class="row-more-less" data-min="3" data-max="9999">
        <td colspan="2"><a class="btn btn-sm btn-default btn-increase" href="#add"><i class="fa fa-plus-circle"></i> <?php echo $speak->more; ?></a> <a class="btn btn-sm btn-default btn-decrease" href="#remove"><i class="fa fa-minus-circle"></i> <?php echo $speak->less; ?></a></td>
      </tr>
    </tbody>
  </table>
  <div class="grid-group">
    <span class="grid span-6"><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button></span>
  </div>
</form>
<hr>
<?php echo Config::speak('file:shortcode'); ?>