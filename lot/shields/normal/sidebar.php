<a href="#toggle" class="blog-sidebar-toggle">
  <i class="fa fa-navicon"></i>
</a>
<aside class="blog-sidebar widgets">
  <?php Shield::chunk('block.widget', array(
      'title' => false,
      'content' => Widget::search($speak->search . '&hellip;', '<i class="fa fa-search"></i>')
  )); ?>
  <?php if($manager && Widget::exist('manager')): ?>
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
      'title' => $speak->widget->related_posts,
      'content' => Widget::relatedPost()
  )); ?>
  <?php Shield::chunk('block.widget', array(
      'title' => $speak->widget->archives,
      'content' => Widget::archive()
  )); ?>
</aside>