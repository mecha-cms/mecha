<div class="tab-content hidden" id="tab-content-preview">
  <?php Weapon::fire('tab_content_preview_before', array($page, $segment)); ?>
  <div id="form-<?php echo $page->id ? 'repair' : 'ignite'; ?>-preview"></div>
  <?php Weapon::fire('tab_content_preview_after', array($page, $segment)); ?>
</div>