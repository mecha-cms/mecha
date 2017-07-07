<?php Shield::get('header'); ?>
<main>
  <?php echo $message; ?>
  <?php foreach ($pages as $page): ?>
  <article id="page-<?php echo $page->id; ?>">
    <header>
      <h3>
        <?php if ($page->link): ?>
        <a href="<?php echo $page->link; ?>" rel="nofollow" target="_blank"><?php echo $page->title; ?> &#x21E2;</a>
        <?php else: ?>
        <a href="<?php echo $page->url; ?>"><?php echo $page->title; ?></a>
        <?php endif; ?>
      </h3>
    </header>
    <section><?php echo $page->description; ?></section>
    <footer></footer>
  </article>
  <?php endforeach; ?>
  <nav><?php echo $pager; ?></nav>
</main>
<?php Shield::get('footer'); ?>