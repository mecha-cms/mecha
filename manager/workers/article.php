<div class="main-actions">
  <a class="btn btn-begin" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/article/ignite"><i class="fa fa-plus-square"></i> <?php echo $speak->manager->title_new_article; ?></a>
</div>
<?php echo Notify::read(); ?>
<?php if($articles): ?>
<ol class="article-list">
  <?php foreach($articles as $article): ?>
  <li class="article" id="article-<?php echo $article->id; ?>">
    <div class="article-header">
      <?php if($article->state == 'draft'): ?>
      <span class="article-title"><?php echo $article->title; ?></span>
      <?php else: ?>
      <a class="article-title" href="<?php echo $article->url; ?>" target="_blank"><?php echo $article->title; ?></a>
      <?php endif; ?>
      <span class="article-time">
        <time datetime="<?php echo $article->date->W3C; ?>"><?php echo $article->date->FORMAT_3; ?></time>
      </span>
    </div>
    <div class="article-body"><?php echo $article->description; ?></div>
    <div class="article-footer">
      <?php Weapon::fire('article_footer', array($article)); ?>
    </div>
  </li>
  <?php endforeach; ?>
</ol>
<p class="pager cf"><?php echo $pager->step->link; ?></p>
<?php else: ?>
<p class="empty"><?php echo Config::speak('notify_empty', array(strtolower($speak->articles))); ?></p>
<?php endif; ?>