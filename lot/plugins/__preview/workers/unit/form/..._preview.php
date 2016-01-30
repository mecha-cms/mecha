<div class="grid-group">
  <div class="grid span-1"></div>
  <div class="grid span-5">
    <?php echo Form::checkbox('css_preview', null, false, $speak->plugin_preview->title->css); ?>
    <?php echo Form::checkbox('js_preview', null, false, $speak->plugin_preview->title->js); ?>
  </div>
</div>