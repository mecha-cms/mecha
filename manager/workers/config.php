<?php echo $messages; ?>
<form class="form-config" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <fieldset>
    <legend><?php echo $speak->manager->title_general; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->timezone; ?></span>
      <span class="grid span-4">
      <?php echo Form::select('timezone', Get::timezone(), Guardian::wayback('timezone', $config->timezone), array(
          'class' => 'select-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_html_charset; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('charset', Guardian::wayback('charset', $config->charset), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->language; ?></span>
      <span class="grid span-4">
      <?php

      $language = array();
      foreach(glob(LANGUAGE . DS . '*', GLOB_ONLYDIR) as $folder) {
          $language[basename($folder)] = basename($folder);
      }
      echo Form::select('language', $language, Guardian::wayback('language', $config->language), array(
          'class' => 'select-block'
      ));

      ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_language_direction; ?></span>
      <span class="grid span-4">
      <?php echo Form::select('language_direction', array(
          'ltr' => 'Left to Right (LTR)',
          'rtl' => 'Right to Left (RTL)'
      ), Guardian::wayback('language_direction', $config->language_direction), array(
          'class' => 'select-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->shield; ?></span>
      <span class="grid span-4">
      <?php

      $info = array();
      foreach(glob(SHIELD . DS . '*', GLOB_ONLYDIR) as $folder) {
          $s = basename($folder);
          if(strpos($s, '__') !== 0) {
              $info[$s] = Shield::info($s)->title;
          }
      }
      echo Form::select('shield', $info, Guardian::wayback('shield', $config->shield), array(
          'class' => 'select-block'
      ));

      ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?> (<?php echo $speak->all; ?>)</span>
      <span class="grid span-4">
      <?php echo Form::number('per_page', Guardian::wayback('per_page', $config->per_page), null, array(
          'min' => 1
      )); ?>
      </span>
    </label>
    <div class="grid-group">
      <span class="grid span-2"></span>
      <div class="grid span-4">
        <div><?php echo Form::checkbox('comments', 'true', Guardian::wayback('comments', $config->comments), $speak->manager->title_comment_allow); ?></div>
        <div><?php echo Form::checkbox('comment_moderation', 'true', Guardian::wayback('comment_moderation', $config->comment_moderation), $speak->manager->title_comment_moderation); ?></div>
        <div><?php echo Form::checkbox('comment_notification_email', 'true', Guardian::wayback('comment_notification_email', $config->comment_notification_email), $speak->manager->title_comment_notification_email); ?></div>
      </div>
    </div>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_page_excerpt_length; ?></span>
      <span class="grid span-4">
      <?php echo Form::number('excerpt_length', Guardian::wayback('excerpt_length', $config->excerpt_length)); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_page_excerpt_tail; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('excerpt_tail', Guardian::wayback('excerpt_tail', $config->excerpt_tail)); ?>
      </span>
    </label>
    <div class="grid-group">
      <span class="grid span-2"></span>
      <div class="grid span-4">
        <div><?php echo Form::checkbox('widget_year_first', 'true', Guardian::wayback('widget_year_first', $config->widget_year_first), $speak->manager->title_widget_time); ?></div>
        <div><?php echo Form::checkbox('widget_include_css', 'true', Guardian::wayback('widget_include_css', $config->widget_include_css), $speak->manager->title_widget_include_css); ?></div>
        <div><?php echo Form::checkbox('widget_include_js', 'true', Guardian::wayback('widget_include_js', $config->widget_include_js), $speak->manager->title_widget_include_js); ?></div>
      </div>
    </div>
    <div class="grid-group">
      <span class="grid span-2"></span>
      <span class="grid span-4">
        <div><?php echo Form::checkbox('html_minifier', 'true', Guardian::wayback('html_minifier', $config->html_minifier), $speak->manager->title_html_minifier); ?></div>
        <div><?php echo Form::checkbox('resource_versioning', 'true', Guardian::wayback('resource_versioning', $config->resource_versioning), $speak->manager->title_resource_versioning); ?></div>
        <div><?php echo Form::checkbox('html_parser', HTML_PARSER, Guardian::wayback('html_parser', $config->html_parser) === HTML_PARSER, $speak->manager->title_html_parser); ?></div>
      </span>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->manager->title_header; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('title', Guardian::wayback('title', $config->title), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_title_separator; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('title_separator', Guardian::wayback('title_separator', $config->title_separator), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->slogan; ?></span>
      <span class="grid span-4">
      <?php echo Form::textarea('slogan', Guardian::wayback('slogan', $config->slogan), null, array(
          'class' => 'textarea-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->description; ?></span>
      <span class="grid span-4">
      <?php echo Form::textarea('description', Guardian::wayback('description', $config->description), null, array(
          'class' => 'textarea-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->keywords; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('keywords', Guardian::wayback('keywords', $config->keywords), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->manager->title_authorization; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->name; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('author', Guardian::wayback('author', $config->author), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->url; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('author_profile_url', Guardian::wayback('author_profile_url', $config->author_profile_url), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->email; ?></span>
      <span class="grid span-4">
      <?php echo Form::text('author_email', Guardian::wayback('author_email', $config->author_email), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->manager->title_page; ?></legend>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_index; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4">
        <?php echo Form::text('index[title]', Guardian::wayback('index.title', $config->index->title)); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4">
        <?php echo Form::text('index[slug]', Guardian::wayback('index.slug', $config->index->slug)); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4">
        <?php echo Form::number('index[per_page]', Guardian::wayback('index.per_page', $config->index->per_page), null, array(
            'min' => 1
        )); ?>
        </span>
      </label>
    </fieldset>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_tag; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4">
        <?php echo Form::text('tag[title]', Guardian::wayback('tag.title', $config->tag->title)); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4">
        <?php echo Form::text('tag[slug]', Guardian::wayback('tag.slug', $config->tag->slug)); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4">
        <?php echo Form::number('tag[per_page]', Guardian::wayback('tag.per_page', $config->tag->per_page), array(
            'min' => 1
        )); ?>
        </span>
      </label>
    </fieldset>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_archive; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4">
        <?php echo Form::text('archive[title]', Guardian::wayback('archive.title', $config->archive->title)); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4">
        <?php echo Form::text('archive[slug]', Guardian::wayback('archive.slug', $config->archive->slug)); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4">
        <?php echo Form::number('archive[per_page]', Guardian::wayback('archive.per_page', $config->archive->per_page), array(
            'min' => 1
        )); ?>
        </span>
      </label>
    </fieldset>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_search; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4">
        <?php echo Form::text('search[title]', Guardian::wayback('search.title', $config->search->title)); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4">
        <?php echo Form::text('search[slug]', Guardian::wayback('search.slug', $config->search->slug)); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4">
        <?php echo Form::number('search[per_page]', Guardian::wayback('search.per_page', $config->search->per_page), array(
            'min' => 1
        )); ?>
        </span>
      </label>
    </fieldset>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_manager; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4">
        <?php echo Form::text('manager[title]', Guardian::wayback('manager.title', $config->manager->title)); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4">
        <?php echo Form::text('manager[slug]', Guardian::wayback('manager.slug', $config->manager->slug)); ?>
        </span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4">
        <?php echo Form::number('manager[per_page]', Guardian::wayback('manager.per_page', $config->manager->per_page), array(
            'min' => 1
        )); ?>
        </span>
      </label>
    </fieldset>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->manager->title_other; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_keyword_spam; ?></span>
      <span class="grid span-4">
      <?php echo Form::textarea('spam_keywords', Guardian::wayback('spam_keywords', $config->spam_keywords), $speak->manager->placeholder_keyword_spam, array(
          'class' => 'textarea-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo Config::speak('manager.title_defaults__title', $speak->article); ?></span>
      <span class="grid span-4">
      <?php echo Form::text('defaults[article_title]', Guardian::wayback('defaults.article_title', $config->defaults->article_title), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo Config::speak('manager.title_defaults__content', $speak->article); ?></span>
      <span class="grid span-4">
      <?php echo Form::textarea('defaults[article_content]', Guardian::wayback('defaults.article_content', $config->defaults->article_content), null, array(
          'class' => array(
              'textarea-block',
              'code',
              'MTE'
          )
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo Config::speak('manager.title_defaults__custom_css', $speak->article); ?></span>
      <span class="grid span-4">
      <?php echo Form::textarea('defaults[article_custom_css]', Guardian::wayback('defaults.article_custom_css', $config->defaults->article_custom_css), null, array(
          'class' => array(
              'textarea-block',
              'code',
              'MTE'
          )
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo Config::speak('manager.title_defaults__custom_js', $speak->article); ?></span>
      <span class="grid span-4">
      <?php echo Form::textarea('defaults[article_custom_js]', Guardian::wayback('defaults.article_custom_js', $config->defaults->article_custom_js), null, array(
          'class' => array(
              'textarea-block',
              'code',
              'MTE'
          )
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo Config::speak('manager.title_defaults__title', $speak->page); ?></span>
      <span class="grid span-4">
      <?php echo Form::text('defaults[page_title]', Guardian::wayback('defaults.page_title', $config->defaults->page_title), null, array(
          'class' => 'input-block'
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo Config::speak('manager.title_defaults__title', $speak->page); ?></span>
      <span class="grid span-4">
      <?php echo Form::textarea('defaults[page_content]', Guardian::wayback('defaults.page_content', $config->defaults->page_content), null, array(
          'class' => array(
              'textarea-block',
              'code',
              'MTE'
          )
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo Config::speak('manager.title_defaults__custom_css', $speak->page); ?></span>
      <span class="grid span-4">
      <?php echo Form::textarea('defaults[page_custom_css]', Guardian::wayback('defaults.page_custom_css', $config->defaults->page_custom_css), null, array(
          'class' => array(
              'textarea-block',
              'code',
              'MTE'
          )
      )); ?>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo Config::speak('manager.title_defaults__custom_js', $speak->page); ?></span>
      <span class="grid span-4">
      <?php echo Form::textarea('defaults[page_custom_js]', Guardian::wayback('defaults.page_custom_js', $config->defaults->page_custom_js), null, array(
          'class' => array(
              'textarea-block',
              'code',
              'MTE'
          )
      )); ?>
      </span>
    </label>
  </fieldset>
  <p><?php echo Jot::button('action', $speak->update); ?></p>
</form>