<div class="main-action-group">
  <?php echo Jot::btn('begin:plus-square', Config::speak('manager.title_new_', $speak->page), $config->manager->slug . '/page/ignite'); ?>
</div>
<?php echo $messages; ?>
<?php if($pages): ?>
<ol class="pages">
  <?php foreach($pages as $page): ?>
  <li class="page" id="page-<?php echo $page->id; ?>">
    <header class="page-header">
      <h3 class="page-title">
        <?php if($page->state === 'draft'): ?>
        <span class="a"><?php echo $page->title; ?></span>
        <?php else: ?>
        <a href="<?php echo $page->url; ?>" target="_blank"><?php echo $page->title; ?></a>
        <?php endif; ?>
      </h3>
      <p class="page-time">
        <time datetime="<?php echo $page->date->W3C; ?>"><?php echo $page->date->FORMAT_3; ?></time>
      </p>
    </header>
    <div class="page-body"><p><?php echo Text::parse($page->description, '->text', '<a><abbr><b><code><del><dfn><em><i><ins><kbd><mark><strong><sub><sup><time><u>'); ?></p></div>
    <footer class="page-footer">
      <?php Weapon::fire('page_footer', array($page)); ?>
    </footer>
  </li>
  <?php endforeach; ?>
</ol>
<?php include __DIR__ . DS . 'unit.pager.1.php'; ?>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', strtolower($speak->pages)); ?></p>
<?php endif; ?>