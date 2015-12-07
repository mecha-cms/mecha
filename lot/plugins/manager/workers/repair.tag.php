<?php echo $messages; ?>
<form class="form-<?php echo $id !== false ? 'repair' : 'ignite'; ?> form-tag" id="form-<?php echo $id !== false ? 'repair' : 'ignite'; ?>" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); $page = $file; ?>
  <?php include __DIR__ . DS . 'unit' . DS . 'form' . DS . 'name.php'; ?>
  <?php include __DIR__ . DS . 'unit' . DS . 'form' . DS . 'tag' . DS . 'slug.php'; ?>
  <?php $scopes = Mecha::walk(glob(POST . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR), function($v) {
      return File::B($v);
  }); ?>
  <?php include __DIR__ . DS . 'unit' . DS . 'form' . DS . 'scope[].php'; ?>
  <?php include __DIR__ . DS . 'unit' . DS . 'form' . DS . 'description.php'; ?>
  <?php include __DIR__ . DS . 'unit' . DS . 'form' . DS . 'id.php'; ?>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <span class="grid span-5">
      <?php if($id !== false): ?>
      <?php echo Jot::button('action', $speak->update); ?>
      <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/tag/kill/id:' . $id); ?>
      <?php else: ?>
      <?php echo Jot::button('construct', $speak->create); ?>
      <?php endif; ?>
    </span>
  </div>
</form>