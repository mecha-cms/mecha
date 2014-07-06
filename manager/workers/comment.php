<?php echo Notify::read(); ?>
<?php if($responses): ?>
<ol class="comment-list">
  <?php foreach($responses as $response): ?>
  <li class="comment" id="comment-<?php echo $response->id; ?>">
    <div class="comment-header">
      <?php if($response->url != '#'): ?>
      <a class="comment-name" href="<?php echo $response->url; ?>" rel="nofollow" target="_blank"><?php echo $response->name; ?></a>
      <?php else: ?>
      <span class="comment-name"><?php echo $response->name; ?></span>
      <?php endif; ?>
      <span class="comment-time">
        <time datetime="<?php echo $response->date->W3C; ?>"><?php echo Date::format($response->time, 'Y/m/d H:i:s'); ?></time>
        <a href="<?php echo $response->permalink; ?>" title="<?php echo $speak->permalink; ?>" rel="nofollow" target="_blank">#</a>
      </span>
    </div>
    <div class="comment-body"><?php echo $response->message; ?></div>
    <div class="comment-footer">
      <?php Weapon::fire('comment_footer', array($response, $article = false)); ?>
    </div>
  </li>
  <?php endforeach; ?>
</ol>
<p class="pager cf"><?php echo $pager->step->link; ?></p>
<?php else: ?>
<p class="empty"><?php echo Config::speak('notify_empty', array(strtolower($speak->comments))); ?></p>
<?php endif; ?>