<?php if(Weapon::exist('article_footer')): ?>
<div><?php Weapon::fire('article_footer', array($article)); ?></div>
<?php endif; ?>