<?php $hooks = array($file, $segment); echo $messages; ?>
<form class="form-<?php echo $id !== false ? 'repair' : 'ignite'; ?> form-tag" id="form-<?php echo $id !== false ? 'repair' : 'ignite'; ?>" action="<?php echo $config->url_current . str_replace('&', '&amp;', $config->url_query); ?>" method="post">
  <?php echo Form::hidden('token', $token); $page = $file; $_ = __DIR__ . DS . 'unit' . DS . 'form' . DS; ?>
  <?php $o = $speak->manager->placeholder_title; ?>
  <?php $speak->manager->placeholder_title = null; ?>
  <?php include $_ . 'name.php'; ?>
  <?php include $_ . 'slug.php'; ?>
  <?php 

  $speak->manager->placeholder_title = $o;

  $scopes = Mecha::walk(glob(POST . DS . '*', GLOB_ONLYDIR), function($v) {
      return File::B($v);
  });

  ?>
  <?php include $_ . 'scope[].php'; ?>
  <?php include $_ . 'description.php'; ?>
  <?php include $_ . 'id.php'; ?>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <span class="grid span-5">
      <?php Weapon::fire('action_before', $hooks); ?>
      <?php if($id !== false): ?>
      <?php echo Jot::button('action', $speak->update); ?>
      <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/tag/kill/id:' . $id); ?>
      <?php else: ?>
      <?php echo Jot::button('construct', $speak->create); ?>
      <?php endif; ?>
      <?php Weapon::fire('action_after', $hooks); ?>
    </span>
  </div>
</form>
<?php if($id === false): ?>
<script>!function(e){e(function(){e.slug("name","slug","-")})}(DASHBOARD.$);</script>
<?php endif; ?>