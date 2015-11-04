<?php if($response->url !== '#'): ?>
<a class="comment-name" href="<?php echo $response->url; ?>" rel="nofollow" target="_blank"><?php echo $response->name; ?></a>
<?php else: ?>
<span class="comment-name"><?php echo $response->name; ?></span>
<?php endif; ?>