<?php echo $messages; ?>
<?php if($pages): ?>
<ol class="pages">
  <?php foreach($pages as $page): ?>
  <li class="page" id="page-<?php echo $page->id; ?>">
    <header class="page-header">
      <h3 class="page-title">
        <?php $x = $page->permalink === '#' ? '<span class="text-error">' . Jot::icon('exclamation-triangle') . '</span> ' : ""; ?>
        <?php if($page->url !== '#'): ?>
        <a href="<?php echo $page->url; ?>" rel="nofollow" target="_blank"><?php echo $x . $page->name; ?></a>
        <?php else: ?>
        <span class="a"><?php echo $x . $page->name; ?></span>
        <?php endif; ?>
      </h3>
      <p class="page-time">
        <time datetime="<?php echo $page->date->W3C; ?>"><?php echo Date::format($page->time, 'Y/m/d H:i:s'); ?></time>
        <a href="<?php echo $page->permalink; ?>" title="<?php echo $x ? $speak->error : $speak->permalink; ?>" rel="nofollow" target="_blank">#</a>
      </p>
    </header>
    <div class="page-body"><?php echo $page->message; ?></div>
    <footer class="page-footer">
      <?php Weapon::fire('comment_footer', array($page, false)); ?>
    </footer>
  </li>
  <?php endforeach; ?>
</ol>
<?php include __DIR__ . DS . 'unit.pager.1.php'; ?>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', strtolower($speak->comments)); ?></p>
<?php endif; ?>