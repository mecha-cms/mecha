<?php if($posts = call_user_func('Get::' . $segment[1] . 's')): ?>
<div class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->to; ?></span>
  <span class="grid span-5">
  <?php

  if(count($posts) > 600) {
      // 600+ post(s), fallback to text input for performance reason
      $_post = call_user_func('Get::' . $segment[1] . 'Anchor', $page->post);
      echo Form::text('post', Request::get('post', Guardian::wayback('post', $page->post)), time());
      echo $_post ? ' ' . Jot::btn('action:external-link', "", $_post->url, array(
          'title' => $_post->title,
          'target' => '_blank'
      )) : "";
  } else {
      $results = array();
      foreach($posts as $v) {
          list($_time, $_kind, $_slug) = explode('_', File::N($v), 3);
          $results[Date::format($_time, 'U')] = Filter::colon($segment[1] . ':url', $config->url . '/' . $config->index->slug . '/' . $_slug);
      }
      asort($results);
      echo Form::select('post', $results, Request::get('post', Guardian::wayback('post', $page->post)), array(
          'class' => 'select-block'
      ));
  }

  ?>
  </span>
</div>
<?php endif; ?>