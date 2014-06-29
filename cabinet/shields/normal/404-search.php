<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<div class="blog-posts">
  <article class="post">
    <h2 class="post-title"><?php echo $config->search->title . ' &ldquo;' . Text::parse($config->search_query)->to_encoded_html . '&rdquo;'; ?></h2>
    <div class="post-body"><p><?php echo $speak->notify_error_not_found; ?></p></div>
  </article>
</div>
<?php include 'footer.php'; ?>