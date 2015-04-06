<section class="comments">
  <h4 class="comment-header">
    <i class="fa fa-comments"></i> <?php echo $article->total_comments_text; ?>
  </h4>
  <ol class="comment-list">
    <?php if($article->total_comments > 0): ?>
    <?php foreach($article->comments as $comment): ?>
    <li class="comment comment-<?php echo $comment->status; ?>" id="comment-<?php echo $comment->id; ?>">
      <div class="comment-avatar">
        <img alt="" src="<?php echo $config->protocol . 'www.gravatar.com/avatar/' . md5($comment->email) . '?s=60&amp;d=monsterid'; ?>" width="60" height="60">
      </div>
      <div class="comment-header">
        <?php if( ! empty($comment->url) && $comment->url != '#'): ?>
        <a class="comment-name" href="<?php echo $comment->url; ?>" rel="nofollow" target="_blank"><?php echo $comment->name; ?></a>
        <?php else: ?>
        <span class="comment-name"><?php echo $comment->name; ?></span>
        <?php endif; ?>
        <span class="comment-time">
          <time datetime="<?php echo $comment->date->W3C; ?>"><?php echo $comment->date->FORMAT_3; ?></time> <a href="<?php echo $comment->permalink; ?>" title="<?php echo $speak->permalink; ?>" rel="nofollow">#</a>
        </span>
      </div>
      <div class="comment-body"><?php echo $comment->message; ?></div>
      <div class="comment-footer">
        <?php Weapon::fire('comment_footer', array($comment, $article)); ?>
      </div>
    </li>
    <?php endforeach; ?>
    <?php endif; ?>
  </ol>
  <form class="comment-form" id="comment-form" action="<?php echo $config->url_current; ?>" method="post">
    <?php echo $messages; ?>
    <input name="token" type="hidden" value="<?php echo $token; ?>">
    <input name="parent" type="hidden" value="">
    <label class="grid-group">
      <span class="grid span-1 form-label"><?php echo $speak->comment_name; ?></span>
      <span class="grid span-5"><input name="name" type="text" class="input-block" value="<?php echo Guardian::wayback('name'); ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-1 form-label"><?php echo $speak->comment_email; ?></span>
      <span class="grid span-5"><input name="email" type="email" class="input-block" value="<?php echo Guardian::wayback('email'); ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-1 form-label"><?php echo $speak->comment_url; ?></span>
      <span class="grid span-5"><input name="url" type="url" class="input-block" value="<?php echo Guardian::wayback('url'); ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-1 form-label"><?php echo $speak->comment_message; ?></span>
      <span class="grid span-5"><textarea name="message" class="textarea-block"><?php echo Guardian::wayback('message'); ?></textarea></span>
    </label>
    <label class="grid-group">
      <span class="grid span-1 form-label"><?php echo Guardian::math(); ?> =</span>
      <span class="grid span-5"><input name="math" type="text" value="" autocomplete="off"></span>
    </label>
    <div class="grid-group">
      <span class="grid span-1"></span>
      <div class="grid span-5">
        <p><button class="btn btn-construct" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->publish; ?></button></p>
        <p><?php echo $speak->comment_guide; ?></p>
      </div>
    </div>
  </form>
  <?php Weapon::fire('comments_footer', array($article)); ?>
</section>