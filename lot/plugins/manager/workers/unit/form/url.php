<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->url; ?></span>
  <span class="grid span-5">
  <?php $url = Request::get('url', Guardian::wayback('url', $page->url_raw)); ?>
  <?php echo Form::url('url', $url !== '#' ? $url : "", null, array(
      'class' => 'input-block'
  )); ?>
  </span>
</label>