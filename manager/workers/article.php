<div class="main-action-group">
  <?php echo Jot::btn('begin:plus-square', Config::speak('manager.title_new_', $speak->article), $config->manager->slug . '/article/ignite'); ?>
</div>
<?php echo $messages; ?>
<?php if($articles): ?>
<ol class="page-list">
  <?php foreach($articles as $article): ?>
  <li class="page" id="page-<?php echo $article->id; ?>">
    <div class="page-header">
      <?php if($article->state == 'draft'): ?>
      <span class="page-title"><?php echo $article->title; ?></span>
      <?php else: ?>
      <a class="page-title" href="<?php echo $article->url; ?>" target="_blank"><?php echo $article->title; ?></a>
      <?php endif; ?>
      <span class="page-time">
        <time datetime="<?php echo $article->date->W3C; ?>"><?php echo $article->date->FORMAT_3; ?></time>
      </span>
    </div>
    <div class="page-body"><?php echo $article->description; ?></div>
    <div class="page-footer">
      <?php Weapon::fire('article_footer', array($article)); ?>
    </div>
  </li>
  <?php endforeach; ?>
</ol>
<?php include DECK . DS . 'workers' . DS . 'unit.pager.1.php'; ?>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', strtolower($speak->articles)); ?></p>
<?php endif; ?>