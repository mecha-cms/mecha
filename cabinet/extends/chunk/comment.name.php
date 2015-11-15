<?php if($comment->url !== '#'): ?>
<a class="comment-name" href="<?php echo $comment->url; ?>" rel="nofollow" target="_blank"><?php echo $comment->name; ?></a>
<?php else: ?>
<span class="comment-name"><?php echo $comment->name; ?></span>
<?php endif; ?>