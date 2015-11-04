<footer class="post-footer">
  <div><?php echo $speak->posted_by; ?> <?php Shield::chunk('article.author'); ?> <?php echo strtolower($speak->on) . ' ' . $article->date->FORMAT_5; ?></div>
  <div><?php echo Widget::exist('tagLinks') ? Widget::tagLinks() : ""; ?></div>
  <div><?php Weapon::fire('article_footer', array($article)); ?></div>
</footer>