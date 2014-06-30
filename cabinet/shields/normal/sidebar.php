<a href="#toggle" class="blog-sidebar-toggle" id="toggle">
  <i class="fa fa-bars"></i>
</a>
<aside class="blog-sidebar">
  <div class="widget-wrapper">
    <div class="widget-content">
      <?php echo Widget::search($speak->search . '&hellip;', '<i class="fa fa-search"></i>'); ?>
    </div>
  </div>
  <?php if($manager): ?>
  <div class="widget-wrapper">
    <h4 class="widget-title">Menu Manager</h4>
    <div class="widget-content">
      <?php echo Widget::manager(); ?>
    </div>
  </div>
  <?php endif; ?>
  <div class="widget-wrapper">
    <h4 class="widget-title">Tags</h4>
    <div class="widget-content">
      <?php echo Widget::tag('LIST', 'ASC'); ?>
    </div>
  </div>
  <div class="widget-wrapper">
    <h4 class="widget-title">Related Posts</h4>
    <div class="widget-content">
      <?php echo Widget::relatedPost(); ?>
    </div>
  </div>
  <div class="widget-wrapper">
    <h4 class="widget-title">Archives</h4>
    <div class="widget-content">
      <?php echo Widget::archive('HIERARCHY', 'DESC'); ?>
    </div>
  </div>
</aside>