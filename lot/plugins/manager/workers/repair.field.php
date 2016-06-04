<?php $hooks = array($file, $segment); echo $messages; ?>
<form class="form-<?php echo $id !== false ? 'repair' : 'ignite'; ?> form-field" id="form-<?php echo $id !== false ? 'repair' : 'ignite'; ?>" action="<?php echo $config->url_current . str_replace('&', '&amp;', $config->url_query); ?>" method="post">
  <?php echo Form::hidden('token', $token); $page = $file; $_ = __DIR__ . DS . 'unit' . DS . 'form' . DS; ?>
  <?php $o = $speak->manager->placeholder_title; ?>
  <?php $speak->manager->placeholder_title = null; ?>
  <?php include $_ . 'title.php'; ?>
  <?php include $_ . 'key.php'; ?>
  <?php

  $speak->manager->placeholder_title = $o;

  $scopes = Mecha::walk(array_merge(
      glob(POST . DS . '*', GLOB_ONLYDIR),
      glob(RESPONSE . DS . '*', GLOB_ONLYDIR)
  ), function($v) {
      return File::N($v);
  });

  $types = Mecha::walk(glob($_ . 'fields[[][]]' . DS . '*.php'), function($v) {
      return File::N($v);
  });

  ?>
  <?php include $_ . 'type.php'; ?>
  <?php include $_ . 'scope[].php'; ?>
  <?php include $_ . 'value.textarea.php'; ?>
  <?php include $_ . 'description.text.php'; ?>
  <?php include $_ . 'placeholder.php'; ?>
  <?php include $_ . 'attributes[].php'; ?>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <span class="grid span-5">
      <?php if($id !== false): ?>
      <?php echo Jot::button('action', $speak->update); ?>
      <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/field/kill/key:' . $id); ?>
      <?php else: ?>
      <?php echo Jot::button('construct', $speak->create); ?>
      <?php endif; ?>
    </span>
  </div>
</form>
<script>!function(e){function o(){var e=this.value,o=e.match(/^(composer|editor|options?|summary|text)$/),i=!e.match(/^(hidden)$/),t=!e.match(/^(boolean|file|hidden|options?)$/);a.prop("disabled",!o).closest(".grid-group")[o?"show":"hide"](),d.prop("disabled",!i).closest(".grid-group")[i?"show":"hide"](),r.prop("disabled",!t).closest(".grid-group")[t?"show":"hide"](),s=e.match(/^options?$/)?"<?php echo strtolower($speak->key) . S . ' ' . $speak->value; ?>":"file"===e?"<?php echo IMAGE_EXT; ?>":"boolean"===e?"1":"",n.attr("placeholder",s)}var i=e("#form-<?php echo $id !== false ? 'repair' : 'ignite'; ?>"),t=i.find('[name="type"]'),n=i.find('[name="value"]'),a=i.find('[name="placeholder"]'),d=i.find('[name="description"]'),r=i.find('[name="attributes[pattern]"]'),s="";t.on("change",o).trigger("change"),e(function(){e.slug("title","key","_")})}(DASHBOARD.$);</script>
<hr>
<?php echo Guardian::wizard($segment); ?>