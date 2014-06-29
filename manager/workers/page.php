<div class="main-actions">
  <a class="btn btn-begin" href="<?php echo $config->url . '/' . $config->manager->slug . '/' . $config->file_type; ?>/ignite"><i class="fa fa-plus-square"></i> <?php echo $config->file_type == 'article' ? $speak->manager->title_new_article : $speak->manager->title_new_page; ?></a>
</div>
<?php echo Notify::read(); ?>
<?php if($pages): ?>
<ol class="page-list">
  <?php foreach($pages as $page): ?>
  <li class="page" id="page-<?php echo $page->id; ?>">
    <div class="page-header">
      <?php if($page->status == 'draft'): ?>
      <span class="page-name"><?php echo $page->title; ?></span>
      <?php else: ?>
      <a class="page-name" href="<?php echo $page->url; ?>" target="_blank"><?php echo $page->title; ?></a>
      <?php endif; ?>
      <span class="page-time">
        <time datetime="<?php echo $page->date->W3C; ?>"><?php echo $page->date->FORMAT_3; ?></time>
      </span>
    </div>
    <div class="page-body"><?php echo $page->description; ?></div>
    <div class="page-footer">
      <?php Weapon::fire($config->file_type . '_footer', array($page)); ?>
    </div>
  </li>
  <?php endforeach; ?>
</ol>
<p class="pager cf"><?php echo $pager->step->link; ?></p>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', array(strtolower($config->file_type == 'article' ? $speak->articles : $speak->pages))); ?></p>
<?php endif; ?>