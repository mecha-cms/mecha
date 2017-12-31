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
    <div><?php echo $page->description; ?></div>
    <footer></footer>
  </article>
  <?php endforeach; ?>
</main>
<nav><?php echo $pager; ?></nav>
<?php Shield::get('footer'); ?>