<?php Shield::get('header'); ?>
<main>
  <?php echo $message; ?>
  <article id="page-<?php echo $page->id; ?>">
    <header>
      <h2><span><?php echo $page->title; ?></span></h2>
      <p><strong><?php echo $language->author; ?>:</strong> <?php echo $page->author; ?></p>
    </header>
    <section>
      <?php if ($page->description): ?>
      <blockquote><?php echo $page->description; ?></blockquote>
      <?php endif; ?>
      <?php echo $page->content; ?>
      <?php if ($page->link): ?>
      <p><a href="<?php echo $page->link; ?>" rel="nofollow" target="_blank"><?php echo $language->link; ?> &#x21E2;</a></p>
      <?php endif; ?>
    </section>
    <footer>
      <?php $update = new Date($page->update); ?>
      <p><time datetime="<?php echo $update->W3C; ?>"><strong><?php echo $language->update; ?>:</strong> <?php echo $update->{str_replace('-', '_', $config->language)}; ?></time></p>
    </footer>
  </article>
  <?php if (strpos($url->path, '/') !== false): ?>
  <nav><?php echo $pager; ?></nav>
  <?php endif; ?>
</main>
<?php Shield::get('footer'); ?>