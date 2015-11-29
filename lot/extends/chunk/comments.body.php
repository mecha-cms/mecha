<ol class="comments-body">
  <?php if($article->total_comments > 0): ?>
  <?php foreach($article->comments as $comment): ?>
  <?php Shield::lot(array('comment' => $comment)); ?>
  <?php Shield::chunk('block.comment.index'); ?>
  <?php endforeach; ?>
  <?php else: ?>
  <li class="comment">
    <?php Shield::chunk('comment.body.204'); ?>
  </li>
  <?php endif; ?>
</ol>