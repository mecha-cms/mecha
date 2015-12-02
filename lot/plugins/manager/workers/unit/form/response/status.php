<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->status; ?></span>
  <span class="grid span-5">
  <?php echo Form::select('status', array(
      1 => $speak->pilot,
      2 => $speak->passenger,
      0 => $speak->intruder
  ), Request::get('status', Guardian::wayback('status', $page->status_raw))); ?>
  </span>
</label>