<div class="main-actions">
  <a href="<?php echo $config->url . '/' . $config->manager->slug . '/' . $config->editor_type; ?>/ignite" class="btn btn-success btn-new"><i class="fa fa-plus-square"></i> <?php echo $config->editor_type == 'article' ? $speak->manager->title_new_article : $speak->manager->title_new_page; ?></a>
</div>
<?php echo Notify::read(); ?>
<?php if($pages): ?>
<ol class="page-list">
  <?php foreach($pages as $page): ?>
  <li class="page" id="page-<?php echo $page->id; ?>">
    <div class="page-header">
      <a class="page-name" href="<?php echo $page->url; ?>" target="_blank"><?php echo $page->title; ?></a>
      <span class="page-time">
        <time datetime="<?php echo Date::format($page->time, 'c'); ?>"><?php echo Date::format($page->time, 'Y/m/d H:i:s'); ?></time>
      </span>
    </div>
    <div class="page-body"><?php echo $page->description; ?></div>
    <div class="page-footer">
      <?php Weapon::fire($config->editor_type . '_footer', array($page)); ?>
    </div>
  </li>
  <?php endforeach; ?>
</ol>
<nav class="blog-pager">
  <span class="pull-left"><?php echo $pager->prev->link; ?></span>
  <span class="pull-right"><?php echo $pager->next->link; ?></span>
</nav>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', array(strtolower($config->editor_type == 'article' ? $speak->articles : $speak->pages))); ?></p>
<?php endif; ?>