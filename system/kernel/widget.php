<?php

class Widget {

    protected static $o = array();

    public static $id = array(
        'manager_menu' => 1,
        'manager_bar' => 1,
        'archive_hierarchy' => 1,
        'archive_list' => 1,
        'archive_dropdown' => 1,
        'tag_list' => 1,
        'tag_cloud' => 1,
        'tag_dropdown' => 1,
        'recent_post' => 1,
        'recent_comment' => 1,
        'random_post' => 1,
        'related_post' => 1,
        'search_form' => 1
    );


    /**
     * Widget Manager
     * --------------
     *
     * [1]. Widget::manager('MENU');
     * [2]. Widget::manager('BAR');
     *
     */

    public static function manager($type = 'MENU') {
        if( ! Guardian::happy()) return "";
        $T1 = TAB;
        if($type === 'MENU') {
            $menus = array();
            if($_menus = Mecha::A(Config::get('manager_menu'))) {
                $_menus = Mecha::eat($_menus)->order('ASC', 'stack', true, 10)->vomit();
                foreach($_menus as $k => $v) {
                    // < 1.1.3
                    if(is_string($v)) {
                        $menus[$k] = $v;
                    } else {
                        $stack = isset($v['stack']) ? $v['stack'] : 10;
                        $_k = (strpos($v['icon'], '<') === false ? '<i class="fa fa-fw fa-' . $v['icon'] . '"></i>' : $v['icon']) . ' <span class="label">' . $k . '</span>' . (isset($v['count']) && ($v['count'] === '&infin;' || (float) $v['count'] > 0) ? ' <span class="counter">' . $v['count'] . '</span>' : "");
                        $menus[$_k] = isset($v['url']) ? $v['url'] : null;
                    }
                }
            }
            $html  = O_BEGIN . '<div class="widget widget-manager widget-manager-menu" id="widget-manager-menu-' . self::$id['manager_menu'] . '">' . NL;
            self::$id['manager_menu']++;
            $html .= Menu::get($menus, 'ul', $T1, 'manager:');
            $html .= '</div>' . O_END;
            $html  = Filter::apply('widget', $html);
            return Filter::apply('widget:manager.menu', Filter::apply('widget:manager', $html));
        }
        if($type === 'BAR') {
            $html = O_BEGIN . '<div class="widget widget-manager widget-manager-bar" id="widget-manager-bar-' . self::$id['manager_bar'] . '">' . NL;
            self::$id['manager_bar']++;
            $bars = array();
            if($_bars = Mecha::A(Config::get('manager_bar'))) {
                $_bars = Mecha::eat($_bars)->order('ASC', 'stack', true, 10)->vomit();
                foreach($_bars as $k => $v) {
                    if(is_string($v)) {
                        $bar  = $v;
                    } else {
                        $t = ' data-tooltip="' . Text::parse(isset($v['description']) ? $v['description'] : $k, '->encoded_html') . '"';
                        $bar  = isset($v['url']) ? '<a class="item" href="' . Converter::url($v['url']) . '"' . $t . '>' : '<span class="item a"' . $t . '>';
                        $bar .= isset($v['icon']) ? (strpos($v['icon'], '<') === false ? Jot::icon($v['icon']) : $v['icon']) : $k;
                        $bar .= ' <span class="label">' . $k . '</span>';
                        $bar .= isset($v['url']) ? '</a>' : '</span>';
                    }
                    $bars[] = Filter::apply('manager:bar.item', $bar);
                }
            }
            $html .= $T1 . Filter::apply('manager:bar', implode(' ', $bars)) . NL;
            $html .= '</div>';
            return Filter::apply('widget:manager.bar', Filter::apply('widget:manager', $html));
        }
    }


    /**
     * Widget Archive
     * --------------
     *
     * [1]. Widget::archive('HIERARCHY');
     * [2]. Widget::archive('HIERARCHY', 'ASC');
     *
     */

    public static function archive($type = 'HIERARCHY', $sort = 'DESC') {
        $T1 = TAB;
        $T2 = str_repeat($T1, 2);
        $T3 = str_repeat($T1, 3);
        $T4 = str_repeat($T1, 4);
        $config = Config::get();
        $speak = Config::speak();
        $year_first = $config->widget_year_first;
        $query = $config->archive_query;
        $months_array = explode(',', $speak->months);
        $archives = array();
        if( ! $files = Get::articles($sort)) {
            return O_BEGIN . '<div class="widget widget-archive">' . Config::speak('notify_empty', strtolower($speak->posts)) . '</div>' . O_END;
        }
        if($type === 'HIERARCHY') {
            $i = 0;
            foreach($files as $file) {
                list($year, $month) = explode('-', File::N($file));
                $archives[$year][$month][] = $file;
            }
            $html  = O_BEGIN . '<div class="widget widget-archive widget-archive-hierarchy" id="widget-archive-hierarchy-' . self::$id['archive_hierarchy'] . '">' . NL;
            self::$id['archive_hierarchy']++;
            $html .= $T1 . '<ul>' . NL;
            foreach($archives as $year => $months) {
                if(is_array($months)) {
                    $posts_count_per_year = 0;
                    $expand = empty($query) ? $i === 0 : (int) substr($query, 0, 4) === (int) $year;
                    foreach($months as $month) {
                        $posts_count_per_year += count($month);
                    }
                    $html .= $T2 . '<li class="' . ($expand ? 'expanded' : 'collapsed') . ((int) $query === (int) $year ? ' selected' : "") . '">' . NL . $T3 . '<a href="javascript:;" class="toggle ' . ($expand ? 'open' : 'close') . '">' . ($expand ? '&#9660;' : '&#9658;') . '</a> <a href="' . $config->url . '/' . $config->archive->slug . '/' . $year . '">' . $year . '</a> <span class="counter">' . $posts_count_per_year . '</span>' . NL;
                    $html .= $T3 . '<ul>' . NL;
                    foreach($months as $month => $days) {
                        if(is_array($days)) {
                            $html .= $T4 . '<li' . ((string) $query === $year . '-' . $month ? ' class="selected"' : "") . '><a href="' . $config->url . '/' . $config->archive->slug . '/' . $year . '-' . $month . '">' . ($year_first ? $year . ' ' . $months_array[(int) $month - 1] : $months_array[(int) $month - 1] . ' ' . $year) . '</a> <span class="counter">' . count($days) . '</span></li>' . NL;
                        }
                    }
                    $html .= $T3 . '</ul>' . NL;
                    $html .= $T2 . '</li>' . NL;
                }
                $i++;
            }
            $html .= $T1 . '</ul>' . NL;
            $html .= '</div>' . O_END;
            $html  = Filter::apply('widget', $html);
            return Filter::apply('widget:archive.hierarchy', Filter::apply('widget:archive', $html));
        }
        if($type === 'LIST' || $type === 'DROPDOWN') {
            foreach($files as $name) {
                $archives[] = substr(File::N($name), 0, 7);
            }
            $counter = array_count_values($archives);
            $archives = array_unique($archives);
            $i = 0;
            if($type === 'LIST') {
                $html  = O_BEGIN . '<div class="widget widget-archive widget-archive-list" id="widget-archive-list-' . self::$id['archive_list'] . '">' . NL;
                self::$id['archive_list']++;
                $html .= $T1 . '<ul>' . NL;
                foreach($archives as $archive) {
                    list($year, $month) = explode('-', $archive);
                    $html .= $T2 . '<li' . ((string) $query === $year . '-' . $month ? ' class="selected"' : "") . '><a href="' . $config->url . '/' . $config->archive->slug . '/' . $archive . '">' . ($year_first ? $year . ' ' . $months_array[(int) $month - 1] : $months_array[(int) $month - 1] . ' ' . $year) . '</a> <span class="counter">' . $counter[$archive] . '</span></li>' . NL;
                    $i++;
                }
                $html .= $T1 . '</ul>' . NL;
                $html .= '</div>' . O_END;
                $html  = Filter::apply('widget', $html);
                return Filter::apply('widget:archive.list', Filter::apply('widget:archive', $html));
            } else {
                $html  = O_BEGIN . '<div class="widget widget-archive widget-archive-dropdown" id="widget-archive-dropdown-' . self::$id['archive_dropdown'] . '">' . NL;
                self::$id['archive_dropdown']++;
                $html .= $T1 . '<select>' . NL . ($query === "" ? $T2 . '<option disabled selected>' . $speak->select . '&hellip;</option>' . NL : "");
                foreach($archives as $archive) {
                    list($year, $month) = explode('-', $archive);
                    $html .= $T2 . '<option value="' . $config->url . '/' . $config->archive->slug . '/' . $archive . '"' . ((string) $query === $year . '-' . $month ? ' selected' : "") . '>' . ($year_first ? $year . ' ' . $months_array[(int) $month - 1] : $months_array[(int) $month - 1] . ' ' . $year) . ' (' . $counter[$archive] . ')</option>' . NL;
                }
                $html .= $T1 . '</select>' . NL;
                $html .= '</div>' . O_END;
                $html  = Filter::apply('widget', $html);
                return Filter::apply('widget:archive.dropdown', Filter::apply('widget:archive', $html));
            }
        }
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

    public static function tag($type = 'LIST', $order = 'ASC', $sorter = 'name', $max_level = 6) {
        $T1 = TAB;
        $T2 = str_repeat($T1, 2);
        $config = Config::get();
        $speak = Config::speak();
        $counters = array();
        $tags = array();
        if( ! $files = Get::articles()) {
            return O_BEGIN . '<div class="widget widget-tag">' . Config::speak('notify_empty', strtolower($speak->posts)) . '</div>' . O_END;
        }
        foreach($files as $file) {
            list($_time, $_kind, $_name) = explode('_', File::B($file), 3);
            foreach(explode(',', $_kind) as $kind) {
                $counters[] = (int) $kind;
            }
        }
        $i = 0;
        foreach(array_count_values($counters) as $id => $count) {
            $tag = Get::rawTag($id);
            if($tag && $id !== 0) {
                $tags[$i] = array(
                    'id' => $id,
                    'name' => $tag['name'],
                    'slug' => $tag['slug'],
                    'count' => $count
                );
                $i++;
            }
        }
        if(empty($tags)) {
            return O_BEGIN . '<div class="widget widget-tag">' . Config::speak('notify_empty', strtolower($speak->tags)) . '</div>' . O_END;
        }
        $tags = Mecha::eat($tags)->order($order, $sorter)->vomit();
        if($type === 'LIST') {
            $html  = O_BEGIN . '<div class="widget widget-tag widget-tag-list" id="widget-tag-list-' . self::$id['tag_list'] . '">' . NL;
            self::$id['tag_list']++;
            $html .= $T1 . '<ul>' . NL;
            foreach($tags as $tag) {
                $html .= $T2 . '<li' . ($config->tag_query === $tag['slug'] ? ' class="selected"' : "") . '><a href="' . $config->url . '/' . $config->tag->slug . '/' . $tag['slug'] . '" rel="tag">' . $tag['name'] . '</a> <span class="counter">' . $tag['count'] . '</span></li>' . NL;
            }
            $html .= $T1 . '</ul>' . NL;
            $html .= '</div>' . O_END;
            $html  = Filter::apply('widget', $html);
            return Filter::apply('widget:tag.list', Filter::apply('widget:tag', $html));
        }
        if($type === 'CLOUD') {
            $tags_counter = array();
            foreach($tags as $tag) {
                $tags_counter[] = $tag['count'];
            }
            $highest_count = max($tags_counter);
            $html = O_BEGIN . '<div class="widget widget-tag widget-tag-cloud" id="widget-tag-cloud-' . self::$id['tag_cloud'] . '">' . NL . TAB;
            self::$id['tag_cloud']++;
            $_html = array();
            foreach($tags as $tag) {
                $size = ceil(($tag['count'] / $highest_count) * $max_level);
                $_html[] = '<span class="size size-' . $size . ($config->tag_query === $tag['slug'] ? ' selected' : "") . '"><a href="' . $config->url . '/' . $config->tag->slug . '/' . $tag['slug'] . '" rel="tag">' . $tag['name'] . '</a> <span class="counter">' . $tag['count'] . '</span></span>';
            }
            $html .= implode(' ', $_html) . NL . '</div>' . O_END;
            $html  = Filter::apply('widget', $html);
            return Filter::apply('widget:tag.cloud', Filter::apply('widget:tag', $html));
        }
        if($type === 'DROPDOWN') {
            $html  = O_BEGIN . '<div class="widget widget-tag widget-tag-dropdown" id="widget-tag-dropdown-' . self::$id['tag_dropdown'] . '">' . NL;
            self::$id['tag_dropdown']++;
            $html .= $T1 . '<select>' . NL . ($config->tag_query === "" ? $T2 . '<option disabled selected>' . $speak->select . '&hellip;</option>' . NL : "");
            foreach($tags as $tag) {
                $html .= $T2 . '<option value="' . $config->url . '/' . $config->tag->slug . '/' . $tag['slug'] . '"' . ($config->tag_query === $tag['slug'] ? ' selected' : "") . '>' . $tag['name'] . ' (' . $tag['count'] . ')</option>' . NL;
            }
            $html .= $T1 . '</select>' . NL;
            $html .= '</div>' . O_END;
            $html  = Filter::apply('widget', $html);
            return Filter::apply('widget:tag.dropdown', Filter::apply('widget:tag', $html));
        }
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

    public static function search($placeholder = "", $submit = "") {
        $T1 = TAB;
        $T2 = str_repeat($T1, 2);
        $config = Config::get();
        $speak = Config::speak();
        $html  = O_BEGIN . '<div class="widget widget-search widget-search-form" id="widget-search-' . self::$id['search_form'] . '">' . NL;
        self::$id['search_form']++;
        $html .= $T1 . '<form action="' . $config->url . '/' . $config->search->slug . '" method="post">' . NL;
        $html .= $T2 . '<input type="text" name="q" value="' . $config->search_query . '"' . ( ! empty($placeholder) ? ' placeholder="' . $placeholder . '"' : "") . ' autocomplete="off"' . ES . ' <button type="submit">' . (empty($submit) ? $speak->search : $submit) . '</button>' . NL;
        $html .= $T1 . '</form>' . NL;
        $html .= '</div>' . O_END;
        $html  = Filter::apply('widget', $html);
        return Filter::apply('widget:search.form', Filter::apply('widget:search', $html));
    }


    /**
     * Widget Recent Post
     * ------------------
     *
     * [1]. Widget::recentPost();
     * [2]. Widget::recentPost(5);
     *
     */

    public static function recentPost($total = 7, $class = 'recent') {
        $T1 = TAB;
        $T2 = str_repeat($T1, 2);
        $config = Config::get();
        $speak = Config::speak();
        if( ! $files = Get::articles()) {
            return O_BEGIN . '<div class="widget widget-' . $class . ' widget-' . $class . '-post">' . Config::speak('notify_empty', strtolower($speak->posts)) . '</div>' . O_END;
        }
        if($class === 'random') {
            $files = Mecha::eat($files)->shake()->vomit();
        }
        $html  = O_BEGIN . '<div class="widget widget-' . $class . ' widget-' . $class . '-post" id="widget-' . $class . '-post-' . self::$id[$class . '_post'] . '">' . NL;
        self::$id[$class . '_post']++;
        $html .= $T1 . '<ul>' . NL;
        for($i = 0, $count = count($files); $i < $total; ++$i) {
            if($i === $count) break;
            $article = Get::articleAnchor($files[$i]);
            $html .= $T2 . '<li' . ($config->url_current === $article->url ? ' class="selected"' : "") . '><a href="' . $article->url . '">' . $article->title . '</a></li>' . NL;
        }
        $html .= $T1 . '</ul>' . NL;
        $html .= '</div>' . O_END;
        $html  = Filter::apply('widget', $html);
        return Filter::apply('widget:' . $class . '.post', Filter::apply('widget:' . $class, $html));
    }


    /**
     * Widget Random Post
     * ------------------
     *
     * [1]. Widget::randomPost();
     * [2]. Widget::randomPost(5);
     *
     */

    public static function randomPost($total = 7) {
        return self::recentPost($total, 'random');
    }


    /**
     * Widget Related Post
     * -------------------
     *
     * [1]. Widget::relatedPost();
     * [2]. Widget::relatedPost(5);
     *
     */

    public static function relatedPost($total = 7) {
        $T1 = TAB;
        $T2 = str_repeat($T1, 2);
        $config = Config::get();
        if($config->page_type !== 'article') {
            return self::randomPost($total);
        } else {
            if( ! $files = Get::articles('DESC', 'kind:' . implode(',', (array) $config->article->kind))) {
                return O_BEGIN . '<div class="widget widget-related widget-related-post">' . Config::speak('notify_empty', strtolower($speak->posts)) . '</div>' . O_END;
            }
            if(count($files) <= 1) {
                return self::randomPost($total);
            }
            $files = Mecha::eat($files)->shake()->vomit();
            $html  = O_BEGIN . '<div class="widget widget-related widget-related-post" id="widget-related-post-' . self::$id['related_post'] . '">' . NL;
            self::$id['related_post']++;
            $html .= $T1 . '<ul>' . NL;
            for($i = 0, $count = count($files); $i < $total; ++$i) {
                if($i === $count) break;
                if($files[$i] !== $config->article->path) {
                    $article = Get::articleAnchor($files[$i]);
                    $html .= $T2 . '<li><a href="' . $article->url . '">' . $article->title . '</a></li>' . NL;
                }
            }
            $html .= $T1 . '</ul>' . NL;
            $html .= '</div>' . O_END;
            $html  = Filter::apply('widget', $html);
            return Filter::apply('widget:related.post', Filter::apply('widget:related', $html));
        }
    }


    /**
     * Widget Recent Comment
     * ---------------------
     *
     * [1]. Widget::recentComment();
     * [2]. Widget::recentComment(5);
     *
     */

    public static function recentComment($total = 7, $avatar_size = 50, $summary = 100, $d = 'monsterid') {
        $T1 = TAB;
        $T2 = str_repeat(TAB, 2);
        $T3 = str_repeat(TAB, 3);
        $T4 = str_repeat(TAB, 4);
        $T5 = str_repeat(TAB, 5);
        $config = Config::get();
        $speak = Config::speak();
        $comments = Get::comments();
        $html = O_BEGIN . '<div class="widget widget-recent widget-recent-comment"' . ($comments ? ' id="widget-recent-comment-' . self::$id['recent_comment'] . '"' : "") . '>' . NL;
        self::$id['recent_comment']++;
        if($comments) {
            $comments_id = array();
            foreach($comments as $comment) {
                $parts = explode('_', File::B($comment));
                $comments_id[] = $parts[1];
            }
            rsort($comments_id);
            $html .= $T1 . '<ul class="recent-comment-list">' . NL;
            for($i = 0, $count = count($comments_id); $i < $total; ++$i) {
                if($i === $count) break;
                $comment = Get::comment($comments_id[$i]);
                $article = Get::articleAnchor($comment->post);
                $html .= $T2 . '<li class="recent-comment">' . NL;
                if($avatar_size !== false && $avatar_size > 0) {
                    $html .= $T3 . '<div class="recent-comment-avatar">' . NL;
                    $html .= $T4 . '<img alt="" src="' . $config->protocol . 'www.gravatar.com/avatar/' . md5($comment->email) . '?s=' . $avatar_size . '&amp;d=' . urlencode($d) . '" width="' . $avatar_size . '" height="' . $avatar_size . '"' . ES . NL;
                    $html .= $T3 . '</div>' . NL;
                }
                $html .= $T3 . '<div class="recent-comment-header">' . NL;
                if(trim($comment->url) === "" || $comment->url === '#') {
                    $html .= $T4 . '<span class="recent-comment-name">' . $comment->name . '</span>' . NL;
                } else {
                    $html .= $T4 . '<a class="recent-comment-name" href="' . $comment->url . '" rel="nofollow">' . $comment->name . '</a>' . NL;
                }
                $html .= $T3 . '</div>' . NL;
                $html .= $T3 . '<div class="recent-comment-body"><p>' . Converter::curt($comment->message, $summary, '&hellip;') . '</p></div>' . NL;
                $html .= $T3 . '<div class="recent-comment-footer">' . NL;
                $html .= $T4 . '<span class="recent-comment-time">' . NL;
                $html .= $T5 . '<time datetime="' . $comment->date->W3C . '">' . $comment->date->FORMAT_3 . '</time> <a title="' . ($article ? strip_tags($article->title) : $speak->notify_error_not_found) . '" href="' . $comment->permalink . '" rel="nofollow">#</a>' . NL;
                $html .= $T4 . '</span>' . NL;
                $html .= $T3 . '</div>' . NL;
                $html .= $T2 . '</li>' . NL;
            }
            $html .= $T1 . '</ul>' . NL;
        } else {
            $html .= Config::speak('notify_empty', strtolower($speak->comments));
        }
        $html .= '</div>' . O_END;
        $html  = Filter::apply('widget', $html);
        return Filter::apply('widget:recent.comment', Filter::apply('widget:recent', $html));
    }


    /**
     * Add a Custom Widget
     * -------------------
     *
     * [1]. Widget::add('my_custom_widget', function($a, $b, $c) { ... });
     *
     */

    public static function add($kin, $action) {
        $_kin = get_called_class() . '::' . $kin;
        if(isset(self::$o[$_kin])) {
            Guardian::abort('<code>Widget::' . $kin . '()</code> already exist.');
        }
        self::$o[$_kin] = $action;
    }


    /**
     * Custom Widget
     * -------------
     *
     * [1]. Widget::call('my_custom_widget', $a, $b, $c);
     *
     */

    public static function call($kin) {
        $_kin = get_called_class() . '::' . $kin;
        if( ! isset(self::$o[$_kin])) {
            Guardian::abort('<code>Widget::call(\'' . $kin . '\')</code> does not exist.');
        }
        $arguments = array_slice(func_get_args(), 1);
        $html = call_user_func_array(self::$o[$_kin], $arguments);
        $html = Filter::apply('widget', $html);
        return Filter::apply('widget:custom.' . Text::parse($kin, '->snake_case'), Filter::apply('widget:custom.' . $kin, Filter::apply('widget:custom', $html)));
    }


    /**
     * Alternate Method for Calling the Custom Widget
     * ----------------------------------------------
     *
     * [1]. Widget::my_custom_widget($a, $b, $c);
     *
     */

    public static function __callStatic($kin, $arguments = array()) {
        $_kin = get_called_class() . '::' . $kin;
        if( ! isset(self::$o[$_kin])) {
            Guardian::abort('<code>Widget::' . $kin . '()</code> does not exist.');
        }
        $html = call_user_func_array(self::$o[$_kin], $arguments);
        $html = Filter::apply('widget', $html);
        return Filter::apply('widget:custom.' . Text::parse($kin, '->snake_case'), Filter::apply('widget:custom.' . $kin, Filter::apply('widget:custom', $html)));
    }

}