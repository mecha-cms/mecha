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
      <tr>
        <td><input name="keys[]" type="text" class="input-block" value="{{url}}" placeholder="{{<?php echo strtolower($speak->key); ?>}}" readonly></td>
        <td><input name="values[]" type="text" class="input-block" value="<?php echo $config->url; ?>/" readonly></td>
      </tr>
      <tr>
        <td><input name="keys[]" type="text" class="input-block" value="{{asset}}" placeholder="{{<?php echo strtolower($speak->key); ?>}}" readonly></td>
        <td><input name="values[]" type="text" class="input-block" value="<?php echo $config->url; ?>/assets/" readonly></td>
      </tr>
      <?php foreach($pages as $key => $value): ?>
      <?php if($key !== '{{url}}' && $key !== '{{asset}}'): ?>
      <tr>
        <td><input name="keys[]" type="text" class="input-block" value="<?php echo Text::parse($key)->to_encoded_html; ?>" placeholder="{{<?php echo strtolower($speak->key); ?>}}"></td>
        <td><input name="values[]" type="text" class="input-block" value="<?php echo Text::parse($value)->to_encoded_html; ?>"></td>
      </tr>
      <?php endif; ?>
      <?php endforeach; ?>
      <tr>
        <td><input name="keys[]" type="text" class="input-block" placeholder="{{<?php echo strtolower($speak->key); ?>}}"></td>
        <td><input name="values[]" type="text" class="input-block"></td>
      </tr>
      <tr class="row-more-less" data-min="3" data-max="9999" data-callback="Zepto(&#39;input[name=&quot;title[]&quot;]').off(&quot;keyup&quot;).each(function(){Zepto.slugger(Zepto(this),Zepto(this).parent().next().find(&#39;input&#39;),&#39;_&#39;)});">
        <td colspan="2"><a class="btn btn-sm btn-more" href="#add"><i class="fa fa-plus-circle"></i> <?php echo $speak->more; ?></a> <a class="btn btn-sm btn-less" href="#remove"><i class="fa fa-minus-circle"></i> <?php echo $speak->less; ?></a></td>
      </tr>
    </tbody>
  </table>
  <div class="grid-group form-actions">
    <span class="grid span-6"><button class="btn btn-primary btn-update" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button></span>
  </div>
</form>
<hr>
<?php echo Config::speak('file:shortcode'); ?>