<?php echo $messages; ?>
<?php if($responses): ?>
<ol class="page-list">
  <?php foreach($responses as $response): ?>
  <li class="page" id="page-<?php echo $response->id; ?>">
    <div class="page-header">
      <?php if($response->url != '#'): ?>
      <a class="page-title" href="<?php echo $response->url; ?>" rel="nofollow" target="_blank"><?php echo $response->name; ?></a>
      <?php else: ?>
      <span class="page-title"><?php echo $response->name; ?></span>
      <?php endif; ?>
      <span class="page-time">
        <time datetime="<?php echo $response->date->W3C; ?>"><?php echo Date::format($response->time, 'Y/m/d H:i:s'); ?></time>
        <a href="<?php echo $response->permalink; ?>" title="<?php echo $speak->permalink; ?>" rel="nofollow" target="_blank">#</a>
      </span>
    </div>
    <div class="page-body"><?php echo $response->message; ?></div>
    <div class="page-footer">
      <?php Weapon::fire('comment_footer', array($response, $article = false)); ?>
    </div>
  </li>
  <?php endforeach; ?>
</ol>
<?php if( ! empty($pager->step->url)): ?>
<p class="pager cf"><?php echo $pager->step->link; ?></p>
<?php endif; ?>
<?php else: ?>
<p><?php echo Config::speak('notify_empty', strtolower($speak->comments)); ?></p>
<?php endif; ?>