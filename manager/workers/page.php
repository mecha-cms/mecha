<div class="main-action-group">
  <?php echo Jot::btn('begin:plus-square', Config::speak('manager.title_new_', $speak->page), $config->manager->slug . '/page/ignite'); ?>
</div>
<?php echo $messages; ?>
<?php if($pages): ?>
<ol class="page-list">
  <?php foreach($pages as $page): ?>
  <li class="page" id="page-<?php echo $page->id; ?>">
    <div class="page-header">
      <?php if($page->state == 'draft'): ?>
      <span class="page-title"><?php echo $page->title; ?></span>
      <?php else: ?>
      <a class="page-title" href="<?php echo $page->url; ?>" target="_blank"><?php echo $page->title; ?></a>
      <?php endif; ?>
      <span class="page-time">
        <time datetime="<?php echo $page->date->W3C; ?>"><?php echo $page->date->FORMAT_3; ?></time>
      </span>
    </div>
    <div class="page-body"><?php echo $page->description; ?></div>
    <div class="page-footer">
      <?php Weapon::fire('page_footer', array($page)); ?>
    </div>
  </li>
  <?php endforeach; ?>
</ol>
<?php if( ! empty($pager->step->url)): ?>
<p class="pager cf"><?php echo $pager->step->link; ?></p>
<?php endif; ?>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', strtolower($speak->pages)); ?></p>
<?php endif; ?>