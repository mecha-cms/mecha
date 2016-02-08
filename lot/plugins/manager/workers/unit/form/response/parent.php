<?php if($responses = call_user_func('Get::' . $segment[0] . 's', 'DESC', 'post:' . Date::slug(Request::get('post', $page->post)))): ?>
<div class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->parent; ?></span>
  <span class="grid span-5">
  <?php

  if(count($responses) > 600) {
      // 600+ response(s), fallback to text input for performance reason
      $_parent = call_user_func('Get::' . $segment[0], $page->parent, array('message', 'message_raw'));
      echo Form::text('parent', Request::get('parent', Guardian::wayback('parent', $page->parent)), time());
      echo $_parent ? ' ' . Jot::btn('action:external-link', "", $_parent->permalink, array(
          'title' => $_parent->name,
          'target' => '_blank'
      )) : "";
  } else {
      $results = array();
      $s = Date::slug($page->id);
      foreach($responses as $v) {
          list($_post, $_time, $_parent) = explode('_', File::N($v), 3);
          $results[($s === $_time ? '.' : "") . Date::format($_time, 'U')] = Date::extract($_time, 'FORMAT_3');
      }
      arsort($results);
      echo Form::select('parent', array("" => '&mdash; ' . $speak->none . ' &mdash;') + $results, Request::get('parent', Guardian::wayback('parent', $page->parent)));
  }

  ?>
  </span>
</div>
<?php endif; ?>