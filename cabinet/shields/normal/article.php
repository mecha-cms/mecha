<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="blog-posts">

  <article class="post" id="post-<?php echo $article->id; ?>">
    <p class="post-time"><i class="fa fa-calendar"></i> <time datetime="<?php echo $article->date->W3C; ?>"><?php echo $article->date->day . ', ' . $article->date->day_number . ' ' . $article->date->month . ' ' . $article->date->year; ?></time></p>
    <h2 class="post-title"><?php echo $article->title; ?></h2>
    <div class="post-body"><?php echo $article->content; ?></div>
    <footer class="post-footer">
      <div><?php echo $speak->posted_by; ?> <a href="<?php echo $config->author_profile_url; ?>" rel="author"><?php echo $article->author; ?></a> <?php echo strtolower($speak->on) . ' ' . $article->date->hour . ':' . $article->date->minute; ?></div>
      <div><?php echo tag_links($article->tags, ', '); ?></div>
    </footer>
  </article>

  <nav class="blog-pager">
    <span class="pull-left blog-pager-prev"><?php echo $pager->prev->link; ?></span>
    <span class="pull-right blog-pager-next"><?php echo $pager->next->link; ?></span>
  </nav>

  <?php if($config->comments) include 'comments.php'; ?>

</div>

<?php include 'footer.php'; ?>