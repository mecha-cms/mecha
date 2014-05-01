<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="blog-posts">
  <article class="post">
    <h2 class="post-title"><?php echo $config->search->title . ' &ldquo;' . $config->search_query . '&rdquo;'; ?></h2>
    <div class="post-body"><?php echo $speak->page . ' ' . strtolower($speak->notify_error_not_found); ?></div>
  </article>
</div>

<?php include 'footer.php'; ?>