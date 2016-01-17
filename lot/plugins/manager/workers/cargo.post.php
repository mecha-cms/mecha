<?php $hooks = array($pages, $segment); ?>
<div class="main-action-group">
  <?php Weapon::fire('main_action_before', $hooks); ?>
  <?php echo Jot::btn('begin:plus-square', Config::speak('manager.title_new_', $speak->{$segment}), $config->manager->slug . '/' . $segment . '/ignite'); ?>
  <?php Weapon::fire('main_action_after', $hooks); ?>
</div>
<?php echo $messages; ?>
<?php if($pages): ?>
<ol class="pages">
  <?php foreach($pages as $page): ?>
  <li class="page" id="page-<?php echo $page->id; ?>">
    <header class="page-header">
      <h3 class="page-title">
        <?php if($page->state === 'drafted'): ?>
        <span class="a"><?php echo $page->title; ?></span>
        <?php else: ?>
        <a href="<?php echo $page->url; ?>" target="_blank"><?php echo $page->title; ?></a>
        <?php endif; ?>
      </h3>
      <p class="page-time">
        <time datetime="<?php echo $page->date->W3C; ?>"><?php echo $page->date->FORMAT_3; ?></time>
      </p>
    </header>
    <div class="page-body"><p><?php echo Text::parse($page->description, '->text', WISE_CELL_I); ?></p></div>
    <footer class="page-footer">
      <?php Weapon::fire($segment . '_footer', array($page)); ?>
    </footer>
  </li>
  <?php endforeach; ?>
</ol>
<?php include __DIR__ . DS . 'unit' . DS . 'pager' . DS . 'step.php'; ?>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', strtolower($speak->{$segment . 's'})); ?></p>
<?php endif; ?>