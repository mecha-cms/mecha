<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<div class="blog-posts">
  <article class="post" id="post-<?php echo $article->id; ?>">
    <p class="post-time"><i class="fa fa-calendar"></i> <time datetime="<?php echo $article->date->W3C; ?>"><?php echo $article->date->FORMAT_1; ?></time></p>
    <?php if($article->link): ?>
    <h2 class="post-title"><a href="<?php echo $article->link; ?>"><?php echo $article->title; ?></a></h2>
    <?php else: ?>
    <h2 class="post-title"><?php echo $article->title; ?></h2>
    <?php endif; ?>
    <div class="post-body"><?php echo $article->content; ?></div>
    <footer class="post-footer">
      <div><?php echo $speak->posted_by; ?> <a href="<?php echo $config->author_profile_url; ?>" rel="author"><?php echo $article->author; ?></a> <?php echo strtolower($speak->on) . ' ' . $article->date->FORMAT_5; ?></div>
      <div><?php echo Widget::tagLinks(', '); ?></div>
      <div><?php Weapon::fire('article_footer', array($article)); ?></div>
    </footer>
  </article>
  <nav class="blog-pager">
    <span class="blog-pager-prev"><?php echo $pager->prev->link; ?></span>
    <span class="blog-pager-next"><?php echo $pager->next->link; ?></span>
  </nav>
  <?php if($config->comments) include 'comments.php'; ?>
</div>
<?php include 'footer.php'; ?>