<ol class="comment-list">
  <?php if($article->total_comments > 0): ?>
  <?php foreach($article->comments as $response): ?>
  <?php Shield::chunk('block.comment.index'); ?>
  <?php endforeach; ?>
  <?php else: ?>
  <li class="comment">
    <?php Shield::chunk('comment.body.204'); ?>
  </li>
  <?php endif; ?>
</ol>