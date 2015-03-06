<div class="main-action-group">
  <a class="btn btn-begin" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/article/ignite"><i class="fa fa-plus-square"></i> <?php echo Config::speak('manager.title_new_', array($speak->article)); ?></a>
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
<p class="pager cf"><?php echo $pager->step->link; ?></p>
<?php else: ?>
<p class="empty"><?php echo Config::speak('notify_empty', array(strtolower($speak->articles))); ?></p>
<?php endif; ?>