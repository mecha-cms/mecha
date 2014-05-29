<?php echo Notify::read(); ?>
<form class="form-field" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo Guardian::makeToken(); ?>">
  <table class="table-bordered table-full">
    <thead>
      <tr>
        <th><?php echo $speak->name; ?></th>
        <th><?php echo $speak->key; ?></th>
        <th><?php echo $speak->type; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php if($pages): ?>
      <?php foreach($pages as $key => $value): ?>
      <tr>
        <td><input name="title[]" type="text" class="input-block" value="<?php echo $value->title; ?>"></td>
        <td><input name="key[]" type="text" class="input-block" value="<?php echo $key; ?>"></td>
        <td>
          <select name="type[]" class="input-block">
            <?php

            $options = array(
                'text' => $speak->text,
                'summary' => $speak->summary,
                'boolean' => $speak->boolean
            );

            foreach($options as $k => $v) {
                echo '<option value="' . $k . '"' . ($k == $value->type ? ' selected' : "") . '>' . $v . '</option>';
            }

            ?>
          </select>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
      <tr>
        <td><input name="title[]" type="text" class="input-block" value=""></td>
        <td><input name="key[]" type="text" class="input-block" value=""></td>
        <td>
          <select name="type[]" class="input-block">
            <option value="text"><?php echo $speak->text; ?></option>
            <option value="summary"><?php echo $speak->summary; ?></option>
            <option value="boolean"><?php echo $speak->boolean; ?></option>
          </select>
        </td>
      </tr>
      <tr class="row-more-less" data-min="1" data-max="9999" data-callback="Zepto(&#39;input[name=&quot;title[]&quot;]').off(&quot;keyup&quot;).each(function(){Zepto.slugger(Zepto(this),Zepto(this).parent().next().find(&#39;input&#39;),&#39;_&#39;)});">
        <td colspan="3"><a class="btn btn-sm btn-more" href="#add"><i class="fa fa-plus-circle"></i> <?php echo $speak->more; ?></a> <a class="btn btn-sm btn-less" href="#remove"><i class="fa fa-minus-circle"></i> <?php echo $speak->less; ?></a></td>
      </tr>
    </tbody>
  </table>
  <div class="grid-group">
    <span class="grid span-6"><button class="btn btn-primary btn-update" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button></span>
  </div>
</form>
<hr>
<?php echo Config::speak('file:field'); ?>