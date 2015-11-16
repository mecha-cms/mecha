<a href="#toggle" class="blog-sidebar-toggle">
  <i class="fa fa-bars"></i>
</a>
<aside class="blog-sidebar widgets">
  <?php Shield::chunk('block.widget', array(
      'title' => false,
      'content' => Widget::search($speak->search . '&hellip;', '<i class="fa fa-search"></i>')
  )); ?>
  <?php if($manager): ?>
  <?php Shield::chunk('block.widget', array(
      'title' => $speak->widget->manager_menus,
      'content' => Widget::manager()
  )); ?>
  <?php endif; ?>
  <?php Shield::chunk('block.widget', array(
      'title' => $speak->widget->tags,
      'content' => Widget::tag()
  )); ?>
  <?php Shield::chunk('block.widget', array(
      'title' => $speak->widget->related_articles,
      'content' => Widget::relatedArticle()
  )); ?>
  <?php Shield::chunk('block.widget', array(
      'title' => $speak->widget->archives,
      'content' => Widget::archive()
  )); ?>
</aside>