<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<div class="blog-main">
  <?php if($config->total_articles > 0): ?>
  <?php foreach($articles as $article): ?>
  <article class="post post-index" id="post-<?php echo $article->id; ?>">
    <p class="post-time"><i class="fa fa-calendar"></i> <time datetime="<?php echo $article->date->W3C; ?>"><?php echo $article->date->FORMAT_1; ?></time></p>
    <?php if($article->link): ?>
    <h4 class="post-title"><a href="<?php echo $article->link; ?>"><?php echo $article->title; ?></a></h4>
    <?php else: ?>
    <h4 class="post-title"><a href="<?php echo $article->url; ?>"><?php echo $article->title; ?></a></h4>
    <?php endif; ?>
    <div class="post-body">
      <?php if($article->excerpt): ?>
      <div class="post-excerpt"><?php echo $article->excerpt; ?></div>
      <?php else: ?>
      <div class="post-description"><?php echo $article->description; ?></div>
      <?php endif; ?>
    </div>
    <div><?php Weapon::fire('article_footer', array($article)); ?></div>
  </article>
  <?php endforeach; ?>
  <?php else: ?>
  <article class="post post-index">
    <div class="post-body"><?php echo Config::speak('notify_empty', strtolower($speak->posts)); ?></div>
  </article>
  <?php endif; ?>
  <nav class="blog-pager">
    <span class="blog-pager-prev"><?php echo $pager->prev->link; ?></span>
    <span class="blog-pager-next"><?php echo $pager->next->link; ?></span>
  </nav>
</div>
<?php include 'footer.php'; ?>