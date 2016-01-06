<?php

class Widget {

    public static $config = array(
        'classes' => array(
            'current' => 'current'
        )
    );

    protected static $w = array(
        'archive' => 1,
        'tag' => 1,
        'search' => 1,
        'recentResponse' => 1,
        'recentPost' => 1,
        'randomPost' => 1,
        'relatedPost' => 1
    );

    protected static $w_x = array();


    /**
     * Widget Archive
     * --------------
     *
     * [1]. Widget::archive('HIERARCHY');
     * [2]. Widget::archive('HIERARCHY', 'ASC');
     *
     */

    public static function archive($type = 'HIERARCHY', $sort = 'DESC', $folder = ARTICLE) {
        $T1 = TAB;
        $T2 = str_repeat($T1, 2);
        $T3 = str_repeat($T1, 3);
        $T4 = str_repeat($T1, 4);
        $kin = strtolower($type);
        $p = $folder !== POST ? File::B($folder) : 'post';
        $id = Config::get('widget_archive_' . $kin . '_id', 0) + 1;
        $config = Config::get();
        $speak = Config::speak();
        $query = $config->archive_query;
        $month_names = (array) $speak->month_names;
        $archives = array();
        $html = O_BEGIN . '<div class="widget widget-archive widget-archive-' . $kin . '" id="widget-archive-' . $kin . '-' . $id . '">' . NL;
        if($files = call_user_func('Get::' . $p . 's', $sort)) {
            if($type === 'HIERARCHY') {
                $i = 0;
                foreach($files as $file) {
                    list($year, $month) = explode('-', File::N($file));
                    $archives[$year][$month][] = $file;
                }
                $html .= $T1 . '<ul>' . NL;
                foreach($archives as $year => $months) {
                    if(is_array($months)) {
                        $posts_count_per_year = 0;
                        $expand = $query ? (int) substr($query, 0, 4) === (int) $year : $i === 0;
                        foreach($months as $month) {
                            $posts_count_per_year += count($month);
                        }
                        $html .= $T2 . '<li class="' . ($expand ? 'open' : 'close') . ((int) $query === (int) $year ? ' ' . self::$config['classes']['current'] : "") . '">' . NL . $T3 . '<a href="javascript:;" class="toggle ' . ($expand ? 'open' : 'close') . '">' . ($expand ? '&#9660;' : '&#9658;') . '</a> <a href="' . Filter::colon('archive:url', $config->url . '/' . $config->archive->slug . '/' . $year) . '">' . $year . '</a> <span class="counter">' . $posts_count_per_year . '</span>' . NL;
                        $html .= $T3 . '<ul>' . NL;
                        foreach($months as $month => $days) {
                            if(is_array($days)) {
                                $html .= $T4 . '<li' . ((string) $query === $year . '-' . $month ? ' class="' . self::$config['classes']['current'] . '"' : "") . '><a href="' . Filter::colon('archive:url', $config->url . '/' . $config->archive->slug . '/' . $year . '-' . $month) . '">' . $year . ' ' . $month_names[(int) $month - 1] . '</a> <span class="counter">' . count($days) . '</span></li>' . NL;
                            }
                        }
                        $html .= $T3 . '</ul>' . NL;
                        $html .= $T2 . '</li>' . NL;
                    }
                    $i++;
                }
                $html .= $T1 . '</ul>' . NL;
            }
            if($type === 'LIST' || $type === 'DROPDOWN') {
                foreach($files as $name) {
                    $archives[] = substr(File::N($name), 0, 7);
                }
                $counter = array_count_values($archives);
                $archives = array_unique($archives);
                $i = 0;
                if($type === 'LIST') {
                    $html .= $T1 . '<ul>' . NL;
                    foreach($archives as $archive) {
                        list($year, $month) = explode('-', $archive);
                        $html .= $T2 . '<li' . ((string) $query === $year . '-' . $month ? ' class="' . self::$config['classes']['current'] . '"' : "") . '><a href="' . Filter::colon('archive:url', $config->url . '/' . $config->archive->slug . '/' . $archive) . '">' . $year . ' ' . $month_names[(int) $month - 1] . '</a> <span class="counter">' . $counter[$archive] . '</span></li>' . NL;
                        $i++;
                    }
                    $html .= $T1 . '</ul>' . NL;
                } else {
                    $html .= $T1 . '<select>' . NL . ($query === "" ? $T2 . '<option disabled selected>' . $speak->select . '&hellip;</option>' . NL : "");
                    foreach($archives as $archive) {
                        list($year, $month) = explode('-', $archive);
                        $html .= $T2 . '<option value="' . Filter::colon('archive:url', $config->url . '/' . $config->archive->slug . '/' . $archive) . '"' . ((string) $query === $year . '-' . $month ? ' selected' : "") . '>' . $year . ' ' . $month_names[(int) $month - 1] . ' (' . $counter[$archive] . ')</option>' . NL;
                    }
                    $html .= $T1 . '</select>' . NL;
                }
            }
        } else {
            $html .= $T1 . Config::speak('notify_empty', strtolower($speak->{$p . 's'})) . NL;
        }
        $html .= '</div>' . O_END;
        Config::set('widget_archive_' . $kin . '_id', $id);
        return Filter::apply(array('widget:archive.' . $kin, 'widget:archive', 'widget'), $html, $id);
    }


    /**
     * Widget Tag
     * ----------
     *
     * [1]. Widget::tag('LIST');
     * [2]. Widget::tag('LIST', 'ASC');
     * [3]. Widget::tag('CLOUD', 'ASC', 'count');
     * [4]. Widget::tag('CLOUD', 'ASC', 'name', 7);
     *
     */

    public static function tag($type = 'LIST', $order = 'ASC', $sorter = 'name', $max_level = 6, $folder = ARTICLE) {
        $T1 = TAB;
        $T2 = str_repeat($T1, 2);
        $kin = strtolower($type);
        $p = $folder !== POST ? File::B($folder) : 'post';
        $id = Config::get('widget_tag_' . $kin . '_id', 0) + 1;
        $config = Config::get();
        $speak = Config::speak();
        $counters = array();
        $tags = array();
        $html = O_BEGIN . '<div class="widget widget-tag widget-tag-' . $kin . '" id="widget-tag-' . $kin . '-' . $id . '">' . NL;
        if($files = call_user_func('Get::' . $p . 's')) {
            foreach($files as $file) {
                list($_time, $_kind, $_name) = explode('_', File::B($file), 3);
                foreach(explode(',', $_kind) as $kind) {
                    $counters[] = (int) $kind;
                }
            }
            foreach(array_count_values($counters) as $id => $count) {
                $tag = call_user_func('Get::' . $p . 'Tag', 'id:' . $id);
                if($tag && $id !== 0) {
                    $tags[] = array(
                        'id' => $id,
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                        'count' => $count
                    );
                }
            }
            if( ! empty($tags)) {
                $tags = Mecha::eat($tags)->order($order, $sorter)->vomit();
                if($type === 'LIST') {
                    $html .= $T1 . '<ul>' . NL;
                    foreach($tags as $tag) {
                        $html .= $T2 . '<li' . ($config->tag_query === $tag['slug'] ? ' class="' . self::$config['classes']['current'] . '"' : "") . '><a href="' . Filter::colon('tag:url', $config->url . '/' . $config->tag->slug . '/' . $tag['slug']) . '" rel="tag">' . $tag['name'] . '</a> <span class="counter">' . $tag['count'] . '</span></li>' . NL;
                    }
                    $html .= $T1 . '</ul>' . NL;
                }
                if($type === 'CLOUD') {
                    $tags_counter = array();
                    foreach($tags as $tag) {
                        $tags_counter[] = $tag['count'];
                    }
                    $highest_count = max($tags_counter);
                    $_html = array();
                    foreach($tags as $tag) {
                        $size = ceil(($tag['count'] / $highest_count) * $max_level);
                        $_html[] = '<span class="size size-' . $size . ($config->tag_query === $tag['slug'] ? ' ' . self::$config['classes']['current'] : "") . '"><a href="' . Filter::colon('tag:url', $config->url . '/' . $config->tag->slug . '/' . $tag['slug']) . '" rel="tag">' . $tag['name'] . '</a> <span class="counter">' . $tag['count'] . '</span></span>';
                    }
                    $html .= $T1 . implode(' ', $_html) . NL;
                }
                if($type === 'DROPDOWN') {
                    $html .= $T1 . '<select>' . NL . ($config->tag_query === "" ? $T2 . '<option disabled selected>' . $speak->select . '&hellip;</option>' . NL : "");
                    foreach($tags as $tag) {
                        $html .= $T2 . '<option value="' . Filter::colon('tag:url', $config->url . '/' . $config->tag->slug . '/' . $tag['slug']) . '"' . ($config->tag_query === $tag['slug'] ? ' selected' : "") . '>' . $tag['name'] . ' (' . $tag['count'] . ')</option>' . NL;
                    }
                    $html .= $T1 . '</select>' . NL;
                }
            } else {
                $html .= $T1 . Config::speak('notify_empty', strtolower($speak->tags)) . NL;
            }
        } else {
            $html .= $T1 . Config::speak('notify_empty', strtolower($speak->{$p . 's'})) . NL;
        }
        $html .= '</div>' . O_END;
        Config::set('widget_tag_' . $kin . '_id', $id);
        return Filter::apply(array('widget:tag.' . $kin, 'widget:tag', 'widget'), $html, $id);
    }


    /**
     * Widget Search Form
     * ------------------
     *
     * [1]. Widget::search();
     * [2]. Widget::search('search query...');
     * [3]. Widget::search('search query...', '<i class="icon icon-search"></i>');
     *
     */

    public static function search($placeholder = null, $submit = null) {
        $T1 = TAB;
        $T2 = str_repeat($T1, 2);
        $id = Config::get('widget_search_form_id', 0) + 1;
        $config = Config::get();
        $speak = Config::speak();
        $html = O_BEGIN . '<div class="widget widget-search widget-search-form" id="widget-search-form-' . $id . '">' . NL;
        $html .= $T1 . '<form action="' . Filter::colon('search:url', $config->url . '/' . $config->search->slug) . '" method="post">' . NL;
        $html .= $T2 . '<input type="text" name="q" value="' . $config->search_query . '"' . ( ! is_null($placeholder) ? ' placeholder="' . $placeholder . '"' : "") . ' autocomplete="off"' . ES . ($submit !== false ? ' <button type="submit">' . (is_null($submit) ? $speak->search : $submit) . '</button>' : "") . NL;
        $html .= $T1 . '</form>' . NL;
        $html .= '</div>' . O_END;
        Config::set('widget_search_form_id', $id);
        return Filter::apply(array('widget:search.form', 'widget:search', 'widget'), $html, $id);
    }


    /**
     * Widget Recent Response
     * ----------------------
     *
     * [1]. Widget::recentResponse();
     * [2]. Widget::recentResponse(5);
     *
     */

    public static function recentResponse($total = 7, $avatar_size = 50, $summary = 100, $d = 'monsterid', $folder = array(COMMENT, ARTICLE)) {
        $T1 = TAB;
        $T2 = str_repeat($T1, 2);
        $T3 = str_repeat($T1, 3);
        $T4 = str_repeat($T1, 4);
        $T5 = str_repeat($T1, 5);
        $r = $folder[0] !== RESPONSE ? File::B($folder[0]) : 'response';
        $p = $folder[1] !== POST ? File::B($folder[1]) : 'post';
        $id = Config::get('widget_recent_' . $r . '_id', 0) + 1;
        $config = Config::get();
        $speak = Config::speak();
        $html = O_BEGIN . '<div class="widget widget-recent widget-recent-response" id="widget-recent-response-' . $id . '">' . NL;
        if($responses = call_user_func('Get::' . $r . 's')) {
            $responses_id = Mecha::walk($responses, function($v) {
                $parts = explode('_', File::B($v));
                return $parts[1];
            });
            rsort($responses_id);
            $html .= $T1 . '<ul class="recent-responses">' . NL;
            for($i = 0, $count = count($responses_id); $i < $total; ++$i) {
                if($i === $count) break;
                $response = call_user_func('Get::' . $r, $responses_id[$i]);
                $post = call_user_func('Get::' . $p . 'Anchor', $response->post);
                $html .= $T2 . '<li class="recent-response">' . NL;
                if($avatar_size !== false && $avatar_size > 0) {
                    $html .= $T3 . '<div class="recent-response-avatar">' . NL;
                    $html .= $T4;
                    $attr = ' alt="" width="' . $avatar_size . '" height="' . $avatar_size . '"';
                    if($avatar = File::exist(ASSET . DS . '__avatar' . DS . $avatar_size . 'x' . $avatar_size . DS . md5($response->email) . '.png')) {
                        $html .= Asset::image($avatar, $attr);
                    } else if($avatar = File::exist(ASSET . DS . '__avatar' . DS . '60x60' . DS . md5($response->email) . '.png')) {
                        $html .= Asset::image($avatar, $attr);
                    } else if($avatar = File::exist(ASSET . DS . '__avatar' . DS . md5($response->email) . '.png')) {
                        $html .= Asset::image($avatar, $attr);
                    } else {
                        $html .= Asset::image($config->protocol . 'www.gravatar.com/avatar/' . md5($response->email) . '?s=' . $avatar_size . '&amp;d=' . urlencode($d), $attr);
                    }
                    $html .= $T3 . '</div>' . NL;
                }
                $html .= $T3 . '<div class="recent-response-header">' . NL;
                if($response->url === '#') {
                    $html .= $T4 . '<span class="recent-response-name">' . $response->name . '</span>' . NL;
                } else {
                    $html .= $T4 . '<a class="recent-response-name" href="' . $response->url . '" rel="nofollow">' . $response->name . '</a>' . NL;
                }
                $html .= $T3 . '</div>' . NL;
                $html .= $T3 . '<div class="recent-response-body">' . Converter::curt($response->message, $summary, $config->excerpt->suffix) . '</div>' . NL;
                $html .= $T3 . '<div class="recent-response-footer">' . NL;
                $html .= $T4 . '<span class="recent-response-time">' . NL;
                $html .= $T5 . '<time datetime="' . $response->date->W3C . '">' . $response->date->FORMAT_3 . '</time> <a title="' . ($post ? Text::parse($post->title, '->text') : $speak->notify_error_not_found) . '" href="' . $response->permalink . '" rel="nofollow">#</a>' . NL;
                $html .= $T4 . '</span>' . NL;
                $html .= $T3 . '</div>' . NL;
                $html .= $T2 . '</li>' . NL;
            }
            $html .= $T1 . '</ul>' . NL;
        } else {
            $html .= $T1 . Config::speak('notify_empty', strtolower($speak->{$r . 's'})) . NL;
        }
        $html .= '</div>' . O_END;
        Config::set('widget_recent_' . $r . '_id', $id);
        return Filter::apply(array('widget:recent.' . $r, 'widget:recent.response', 'widget:recent', 'widget'), $html, $id);
    }


    /**
     * Widget Recent Post
     * ------------------
     *
     * [1]. Widget::recentPost();
     * [2]. Widget::recentPost(5);
     *
     */

    public static function recentPost($total = 7, $folder = ARTICLE, $class = 'recent') {
        $T1 = TAB;
        $T2 = str_repeat($T1, 2);
        $p = $folder !== POST ? File::B($folder) : 'post';
        $id = Config::get('widget_' . $class . '_' . $p . '_id', 0) + 1;
        $config = Config::get();
        $speak = Config::speak();
        $html = O_BEGIN . '<div class="widget widget-' . $class . ' widget-' . $class . '-post" id="widget-' . $class . '-post-' . $id . '">' . NL;
        if($files = call_user_func('Get::' . $p . 's')) {
            if($class !== 'recent') {
                $files = Mecha::eat($files)->shake()->vomit();
            }
            $html .= $T1 . '<ul>' . NL;
            for($i = 0, $count = count($files); $i < $total; ++$i) {
                if($i === $count) break;
                $post = call_user_func('Get::' . $p . 'Anchor', $files[$i]);
                $html .= $T2 . '<li' . ($config->url_current === $post->url ? ' class="' . self::$config['classes']['current'] . '"' : "") . '><a href="' . $post->url . '">' . $post->title . '</a></li>' . NL;
            }
            $html .= $T1 . '</ul>' . NL;
        } else {
            $html .= $T1 . Config::speak('notify_empty', strtolower($speak->{$p . 's'})) . NL;
        }
        $html .= '</div>' . O_END;
        Config::set('widget_' . $class . '_' . $p . '_id', $id);
        return Filter::apply(array('widget:' . $class . '.' . $p, 'widget:' . $class . '.post', 'widget:' . $class, 'widget'), $html, $id);
    }


    /**
     * Widget Random Post
     * ------------------
     *
     * [1]. Widget::randomPost();
     * [2]. Widget::randomPost(5);
     *
     */

    public static function randomPost($total = 7, $folder = ARTICLE) {
        return self::recentPost($total, $folder, 'random');
    }


    /**
     * Widget Related Post
     * -------------------
     *
     * [1]. Widget::relatedPost();
     * [2]. Widget::relatedPost(5);
     *
     */

    public static function relatedPost($total = 7, $folder = ARTICLE) {
        $T1 = TAB;
        $T2 = str_repeat($T1, 2);
        $p = $folder !== POST ? File::B($folder) : 'post';
        $id = Config::get('widget_related_' . $p . '_id', 0) + 1;
        $config = Config::get();
        $speak = Config::speak();
        $kind = isset($config->{$p}->kind) ? (array) $config->{$p}->kind : array();
        $html = O_BEGIN . '<div class="widget widget-related widget-related-post" id="widget-related-post-' . $id . '">' . NL;
        if($config->page_type !== $p) {
            return self::randomPost($total, $folder);
        } else {
            if($files = call_user_func('Get::' . $p . 's', 'DESC', 'kind:' . implode(',', $kind))) {
                if(count($files) <= 1) {
                    return self::randomPost($total, $folder);
                }
                $files = Mecha::eat($files)->shake()->vomit();
                $html .= $T1 . '<ul>' . NL;
                for($i = 0, $count = count($files); $i < $total; ++$i) {
                    if($i === $count) break;
                    if($files[$i] !== $config->{$p}->path) {
                        $post = call_user_func('Get::' . $p . 'Anchor', $files[$i]);
                        $html .= $T2 . '<li><a href="' . $post->url . '">' . $post->title . '</a></li>' . NL;
                    }
                }
                $html .= $T1 . '</ul>' . NL;
            } else {
                $html .= $T1 . Config::speak('notify_empty', strtolower($speak->{$p . 's'})) . NL;
            }
        }
        $html .= '</div>' . O_END;
        Config::set('widget_related_' . $p . '_id', $id);
        return Filter::apply(array('widget:related.' . $p, 'widget:related.post', 'widget:related', 'widget'), $html, $id);
    }


    /**
     * Add a Built-In Widget
     * ---------------------
     *
     * [1]. Widget::plug('my_widget', function($a, $b, $c) { ... });
     *
     */

    public static function plug($kin, $action) {
        $c = get_called_class();
        if( ! isset(self::$w_x[$c][$kin])) {
            if(isset(self::$w[$c][$kin])) {
                Guardian::abort(Config::speak('notify_exist', '<code>' . $c . '::' . $kin . '()</code>'));
            }
            self::$w[$c][$kin] = $action;
        }
    }


    /**
     * Add a Custom Widget
     * -------------------
     *
     * [1]. Widget::add('my_custom_widget', function($a, $b, $c) { ... });
     *
     */

    public static function add($kin, $action, $wrapper = false) {
        $c = get_called_class();
        if( ! isset(self::$w_x[$c][$kin])) {
            if(isset(self::$w[$c][$kin])) {
                Guardian::abort(Config::speak('notify_exist', '<code>' . $c . '::' . $kin . '()</code>'));
            }
            self::$w[$c][$kin] = array(
                'fn' => $action,
                'wrapper' => $wrapper
            );
        }
    }


    /**
     * Remove a Custom Widget
     * ----------------------
     *
     * [1]. Widget::remove('my_custom_widget');
     *
     */

    public static function remove($kin) {
        $c = get_called_class();
        self::$w_x[$c][$kin] = isset(self::$w[$c][$kin]) ? self::$w[$c][$kin] : 1;
        unset(self::$w[$c][$kin]);
    }


    /**
     * Check a Custom Widget
     * ---------------------
     *
     * [1]. if(Widget::exist('my_custom_widget')) { ... }
     *
     */

    public static function exist($kin, $fallback = false) {
        $c = get_called_class();
        return isset(self::$w[$c][$kin]) ? self::$w[$c][$kin] : $fallback;
    }


    /**
     * Custom Widget
     * -------------
     *
     * [1]. Widget::my_custom_widget($a, $b, $c);
     *
     */

    public static function __callStatic($kin, $arguments = array()) {
        $c = get_called_class();
        if( ! isset(self::$w[$c][$kin])) {
            Guardian::abort(Config::speak('notify_not_exist', '<code>' . $c . '::' . $kin . '()</code>'), false);
        } else {
            // built-in
            if( ! is_array(self::$w[$c][$kin])) {
                return call_user_func_array(self::$w[$c][$kin], $arguments);
            }
            // custom
            $snake = Text::parse($kin, '->snake_case');
            $slug = Text::parse($snake, '->slug');
            $id = Config::get('widget_custom_' . $snake . '_id', 0) + 1;
            $html = "";
            if(self::$w[$c][$kin]['wrapper']) {
                $html .= O_BEGIN . '<div class="widget widget-custom widget-custom-' . $slug . '" id="widget-custom-' . $slug . '-' . $id . '">' . NL;
            }
            $html .= call_user_func_array(self::$w[$c][$kin]['fn'], $arguments);
            if(self::$w[$c][$kin]['wrapper']) {
                $html .= '</div>' . O_END;
            }
            Config::set('widget_custom_' . $snake . '_id', $id);
            return Filter::apply(array('widget:custom.' . $snake, 'widget:custom.' . $kin, 'widget:custom', 'widget'), $html, $id);
        }
    }

}