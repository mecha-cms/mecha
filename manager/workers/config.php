<?php echo $messages; ?>
<form class="form-config" action="<?php echo $config->url_current; ?>" method="post">
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <fieldset>
    <legend><?php echo $speak->manager->title_general; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->timezone; ?></span>
      <span class="grid span-4">
        <select name="timezone" class="select-block">
        <?php

        foreach(Get::timezone() as $key => $value) {
            echo '<option value="' . $key . '"' . (Guardian::wayback('timezone', $config->timezone) == $key ? ' selected' : "") . '>' . $value . '</option>';
        }

        ?>
        </select>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_html_charset; ?></span>
      <span class="grid span-4"><input name="charset" type="text" class="input-block" value="<?php echo Guardian::wayback('charset', $config->charset); ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->language; ?></span>
      <span class="grid span-4">
        <select name="language" class="select-block">
        <?php

        foreach(glob(LANGUAGE . DS . '*', GLOB_ONLYDIR) as $folder) {
            $lang = basename($folder);
            echo '<option value="' . $lang . '"' . (Guardian::wayback('language', $config->language) == $lang ? ' selected' : "") . '>' . $lang . '</option>';
        }

        ?>
        </select>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_language_direction; ?></span>
      <span class="grid span-4">
        <select name="language_direction" class="select-block">
          <option value="ltr"<?php echo (Guardian::wayback('language_direction', $config->language_direction) == 'ltr' ? ' selected' : ""); ?>>Left to Right (LTR)</option>
          <option value="rtl"<?php echo (Guardian::wayback('language_direction', $config->language_direction) == 'rtl' ? ' selected' : ""); ?>>Right to Left (RTL)</option>
        </select>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->shield; ?></span>
      <span class="grid span-4">
        <select name="shield" class="select-block">
        <?php

        foreach(glob(SHIELD . DS . '*', GLOB_ONLYDIR) as $folder) {
            $shield = basename($folder);
            $info = Shield::info($shield);
            echo strpos($shield, '__') !== 0 ? '<option value="' . $shield . '"' . (Guardian::wayback('shield', $config->shield) == $shield ? ' selected' : "") . '>' . $info->title . '</option>' : "";
        }

        ?>
        </select>
      </span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?> (<?php echo $speak->all; ?>)</span>
      <span class="grid span-4"><input name="per_page" type="number" value="<?php echo Guardian::wayback('per_page', $config->per_page); ?>"></span>
    </label>
    <div class="grid-group">
      <span class="grid span-2"></span>
      <div class="grid span-4">
        <div><label><input name="comments" type="checkbox" value="true"<?php echo Guardian::wayback('comments', $config->comments) ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_comment_allow; ?></span></label></div>
        <div><label><input name="comment_moderation" type="checkbox" value="true"<?php echo Guardian::wayback('comment_moderation', $config->comment_moderation) ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_comment_moderation; ?></span></label></div>
        <div><label><input name="email_notification" type="checkbox" value="true"<?php echo Guardian::wayback('email_notification', $config->email_notification) ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_comment_notification; ?></span></label></div>
      </div>
    </div>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_page_excerpt_length; ?></span>
      <span class="grid span-4"><input name="excerpt_length" type="number" value="<?php echo Guardian::wayback('excerpt_length', $config->excerpt_length); ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_page_excerpt_tail; ?></span>
      <span class="grid span-4"><input name="excerpt_tail" type="text" value="<?php echo Text::parse(Guardian::wayback('excerpt_tail', $config->excerpt_tail))->to_encoded_html; ?>"></span>
    </label>
    <div class="grid-group">
      <span class="grid span-2"></span>
      <div class="grid span-4">
        <div><label><input name="widget_year_first" type="checkbox" value="true"<?php echo Guardian::wayback('widget_year_first', $config->widget_year_first) ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_widget_time; ?></span></label></div>
        <div><label><input name="resource_versioning" type="checkbox" value="true"<?php echo Guardian::wayback('resource_versioning', $config->resource_versioning) ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_resource_versioning; ?></span></label></div>
      </div>
    </div>
    <div class="grid-group">
      <span class="grid span-2"></span>
      <span class="grid span-4">
        <div><label><input name="html_minifier" type="checkbox" value="true"<?php echo Guardian::wayback('html_minifier', $config->html_minifier) ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_html_minifier; ?></span></label></div>
        <div><label><input name="html_parser" type="checkbox" value="<?php echo HTML_PARSER; ?>"<?php echo Guardian::wayback('html_parser', $config->html_parser) ? ' checked' : ""; ?>> <span><?php echo $speak->manager->title_html_parser; ?></span></label></div>
      </span>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->manager->title_header; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
      <span class="grid span-4"><input name="title" type="text" class="input-block" value="<?php echo Guardian::wayback('title', $config->title); ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_title_separator; ?></span>
      <span class="grid span-4"><input name="title_separator" type="text" class="input-block" value="<?php echo Text::parse(Guardian::wayback('title_separator', $config->title_separator))->to_encoded_html; ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->slogan; ?></span>
      <span class="grid span-4"><textarea name="slogan" class="textarea-block"><?php echo Text::parse(Guardian::wayback('slogan', $config->slogan))->to_encoded_html; ?></textarea></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->description; ?></span>
      <span class="grid span-4"><textarea name="description" class="textarea-block"><?php echo Text::parse(Guardian::wayback('description', $config->description))->to_encoded_html; ?></textarea></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->keywords; ?></span>
      <span class="grid span-4"><input name="keywords" type="text" class="input-block" value="<?php echo Guardian::wayback('keywords', $config->keywords); ?>"></span>
    </label>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->manager->title_authorization; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->name; ?></span>
      <span class="grid span-4"><input name="author" type="text" class="input-block" value="<?php echo Guardian::wayback('author', $config->author); ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->url; ?></span>
      <span class="grid span-4"><input name="author_profile_url" type="url" class="input-block" value="<?php echo Guardian::wayback('author_profile_url', $config->author_profile_url); ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->email; ?></span>
      <span class="grid span-4"><input name="author_email" type="email" class="input-block" value="<?php echo Guardian::wayback('author_email', $config->author_email); ?>"></span>
    </label>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->manager->title_page; ?></legend>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_index; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4"><input name="index[title]" type="text" value="<?php echo Guardian::wayback('index.title', $config->index->title); ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4"><input name="index[slug]" type="text" value="<?php echo Guardian::wayback('index.slug', $config->index->slug); ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4"><input name="index[per_page]" type="number" value="<?php echo Guardian::wayback('index.per_page', $config->index->per_page); ?>"></span>
      </label>
    </fieldset>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_tag; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4"><input name="tag[title]" type="text" value="<?php echo Guardian::wayback('tag.title', $config->tag->title); ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4"><input name="tag[slug]" type="text" value="<?php echo Guardian::wayback('tag.slug', $config->tag->slug); ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4"><input name="tag[per_page]" type="number" value="<?php echo Guardian::wayback('tag.per_page', $config->tag->per_page); ?>"></span>
      </label>
    </fieldset>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_archive; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4"><input name="archive[title]" type="text" value="<?php echo Guardian::wayback('archive.title', $config->archive->title); ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4"><input name="archive[slug]" type="text" value="<?php echo Guardian::wayback('archive.slug', $config->archive->slug); ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4"><input name="archive[per_page]" type="number" value="<?php echo Guardian::wayback('archive.per_page', $config->archive->per_page); ?>"></span>
      </label>
    </fieldset>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_search; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4"><input name="search[title]" type="text" value="<?php echo Guardian::wayback('seearch.title', $config->search->title); ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4"><input name="search[slug]" type="text" value="<?php echo Guardian::wayback('search.slug', $config->search->slug); ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4"><input name="search[per_page]" type="number" value="<?php echo Guardian::wayback('search.per_page', $config->search->per_page); ?>"></span>
      </label>
    </fieldset>
    <fieldset>
      <legend><?php echo $speak->manager->title_page_manager; ?></legend>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->title; ?></span>
        <span class="grid span-4"><input name="manager[title]" type="text" value="<?php echo Guardian::wayback('manager.title', $config->manager->title); ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->slug; ?></span>
        <span class="grid span-4"><input name="manager[slug]" type="text" value="<?php echo Guardian::wayback('manager.slug', $config->manager->slug); ?>"></span>
      </label>
      <label class="grid-group">
        <span class="grid span-2 form-label"><?php echo $speak->manager->title_per_page; ?></span>
        <span class="grid span-4"><input name="manager[per_page]" type="number" value="<?php echo Guardian::wayback('manager.per_page', $config->manager->per_page); ?>"></span>
      </label>
    </fieldset>
  </fieldset>
  <fieldset>
    <legend><?php echo $speak->manager->title_other; ?></legend>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_keyword_spam; ?></span>
      <span class="grid span-4"><textarea name="spam_keywords" class="textarea-block" placeholder="<?php echo $speak->manager->placeholder_keyword_spam; ?>"><?php echo Text::parse(Guardian::wayback('spam_keywords', $config->spam_keywords))->to_encoded_html; ?></textarea></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_article_title; ?></span>
      <span class="grid span-4"><input name="defaults[article_title]" class="input-block" value="<?php echo Guardian::wayback('defaults.article_title', $config->defaults->article_title); ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_article_content ?></span>
      <span class="grid span-4"><textarea name="defaults[article_content]" class="textarea-block code"><?php echo Text::parse(Guardian::wayback('defaults.article_content', $config->defaults->article_content))->to_encoded_html; ?></textarea></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_article_custom_css; ?></span>
      <span class="grid span-4"><textarea name="defaults[article_custom_css]" class="textarea-block code"><?php echo Text::parse(Guardian::wayback('defaults.article_custom_css', $config->defaults->article_custom_css))->to_encoded_html; ?></textarea></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_article_custom_js; ?></span>
      <span class="grid span-4"><textarea name="defaults[article_custom_js]" class="textarea-block code"><?php echo Text::parse(Guardian::wayback('defaults.article_custom_js', $config->defaults->article_custom_js))->to_encoded_html; ?></textarea></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_page_title; ?></span>
      <span class="grid span-4"><input name="defaults[page_title]" class="input-block" value="<?php echo Guardian::wayback('defaults.page_title', $config->defaults->page_title); ?>"></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_page_content ?></span>
      <span class="grid span-4"><textarea name="defaults[page_content]" class="textarea-block code"><?php echo Text::parse(Guardian::wayback('defaults.page_content', $config->defaults->page_content))->to_encoded_html; ?></textarea></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_page_custom_css; ?></span>
      <span class="grid span-4"><textarea name="defaults[page_custom_css]" class="textarea-block code"><?php echo Text::parse(Guardian::wayback('defaults.page_custom_css', $config->defaults->page_custom_css))->to_encoded_html; ?></textarea></span>
    </label>
    <label class="grid-group">
      <span class="grid span-2 form-label"><?php echo $speak->manager->title_defaults_page_custom_js; ?></span>
      <span class="grid span-4"><textarea name="defaults[page_custom_js]" class="textarea-block code"><?php echo Text::parse(Guardian::wayback('defaults.page_custom_js', $config->defaults->page_custom_js))->to_encoded_html; ?></textarea></span>
    </label>
  </fieldset>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button></p>
</form>