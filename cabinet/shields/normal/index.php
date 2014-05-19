<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="blog-posts">
  <?php if($config->total_articles > 0): ?>
  <?php foreach($pages as $article): ?>
  <article class="post post-index" id="post-<?php echo $article->id; ?>">
    <p class="post-time post-time-index"><i class="fa fa-calendar"></i> <time datetime="<?php echo $article->date->W3C; ?>"><?php echo $article->date->day . ', ' . $article->date->day_number . ' ' . $article->date->month . ' ' . $article->date->year; ?></time></p>
    <h4 class="post-title"><a href="<?php echo $article->url; ?>"><?php echo $article->title; ?></a></h4>
    <div class="post-body">
      <div class="post-description"><?php echo $article->description; ?></div>
    </div>
  </article>
  <?php endforeach; ?>
  <?php else: ?>
  <article class="post post-index" id="post-0">
    <div class="post-body"><?php echo Config::speak('notify_empty', array(strtolower($speak->posts))); ?></div>
  </article>
  <?php endif; ?>

  <nav class="blog-pager">
    <span class="pull-left blog-pager-prev"><?php echo $pager->prev->link; ?></span>
    <span class="pull-right blog-pager-next"><?php echo $pager->next->link; ?></span>
  </nav>

</div>

<?php include 'footer.php'; ?>