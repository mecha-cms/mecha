<div class="main-actions">
  <a class="btn btn-begin" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/page/ignite"><i class="fa fa-plus-square"></i> <?php echo Config::speak('manager.title_new_', array($speak->page)); ?></a>
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
<p class="pager cf"><?php echo $pager->step->link; ?></p>
<?php else: ?>
<p class="empty"><?php echo Config::speak('notify_empty', array(strtolower($speak->pages))); ?></p>
<?php endif; ?>