<ol class="comment-list">
  <?php if($article->total_comments > 0): ?>
  <?php foreach($article->comments as $response): ?>
  <?php Shield::lot('response', $response)->chunk('block.comment.index'); ?>
  <?php endforeach; ?>
  <?php endif; ?>
</ol>