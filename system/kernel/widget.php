<?php

class Widget {

    public static $macros = array();

    public static $ids = array(
        'manager-menu' => 1,
        'archive-hierarchy' => 1,
        'archive-list' => 1,
        'archive-dropdown' => 1,
        'tag-list' => 1,
        'tag-cloud' => 1,
        'tag-dropdown' => 1,
        'search' => 1,
        'recent-post' => 1,
        'recent-comment' => 1,
        'random-post' => 1,
        'related-post' => 1
    );


    /**
     * Widget Manager
     * --------------
     *
     * [1]. Widget::manager();
     *
     */

    public static function manager() {
        $config = Config::get();
        $speak = Config::speak();
        if( ! Guardian::happy()) return "";
        $total = $config->total_comments_backend;
        $destination = SYSTEM . DS . 'log' . DS . 'comments.total.log';
        $n = $total > 0 ? '<span class="counter">' . $total . '</span>' : "";
        if($file = File::exist($destination)) {
            $old = (int) File::open($file)->read();
            $n = ($total > $old) ? '<span class="counter">' . ($total - $old) . '</span>' : "";
        } else {
            File::write($total)->saveTo($destination, 0600);
        }
        $menus = array(
            '<i class="fa fa-fw fa-cogs"></i> <span>' . $speak->config . '</span>' => '/' . $config->manager->slug . '/config',
            '<i class="fa fa-fw fa-file-text"></i> <span>' . $speak->article . '</span>' => '/' . $config->manager->slug . '/article',
            '<i class="fa fa-fw fa-file"></i> <span>' . $speak->page . '</span>' => '/' . $config->manager->slug . '/page',
            '<i class="fa fa-fw fa-comments"></i> <span>' . $speak->comment . $n . '</span>' => '/' . $config->manager->slug . '/comment',
            '<i class="fa fa-fw fa-tags"></i> <span>' . $speak->tag . '</span>' => '/' . $config->manager->slug . '/tag',
            '<i class="fa fa-fw fa-bars"></i> <span>' . $speak->menu . '</span>' => '/' . $config->manager->slug . '/menu',
            '<i class="fa fa-fw fa-briefcase"></i> <span>' . $speak->asset . '</span>' => '/' . $config->manager->slug . '/asset',
            '<i class="fa fa-fw fa-th-list"></i> <span>' . $speak->field . '</span>' => '/' . $config->manager->slug . '/field',
            '<i class="fa fa-fw fa-coffee"></i> <span>' . $speak->shortcode . '</span>' => '/' . $config->manager->slug . '/shortcode',
            '<i class="fa fa-fw fa-shield"></i> <span>' . $speak->shield . '</span>' => '/' . $config->manager->slug . '/shield',
            '<i class="fa fa-fw fa-plug"></i> <span>' . $speak->plugin . '</span>' => '/' . $config->manager->slug . '/plugin',
            '<i class="fa fa-fw fa-clock-o"></i> <span>' . $speak->cache . '</span>' => '/' . $config->manager->slug . '/cache',
            '<i class="fa fa-fw fa-life-ring"></i> <span>' . $speak->backup . '</span>' => '/' . $config->manager->slug . '/backup'
        );
        if($config->page_type == 'article') {
            $menus['<i class="fa fa-fw fa-pencil"></i> <span>' . Config::speak('manager._this_article', array($speak->edit)) . '</span>'] = '/' . $config->manager->slug . '/article/repair/id:' . $config->article->id;
            $menus['<i class="fa fa-fw fa-trash"></i> <span>' . Config::speak('manager._this_article', array($speak->delete)) . '</span>'] = '/' . $config->manager->slug . '/article/kill/id:' . $config->article->id;
        }
        if($config->page_type == 'page') {
            $menus['<i class="fa fa-fw fa-pencil"></i> <span>' . Config::speak('manager._this_page', array($speak->edit)) . '</span>'] = '/' . $config->manager->slug . '/page/repair/id:' . $config->page->id;
            $menus['<i class="fa fa-fw fa-trash"></i> <span>' . Config::speak('manager._this_page', array($speak->delete)) . '</span>'] = '/' . $config->manager->slug . '/page/kill/id:' . $config->page->id;
        }

        /**
         * =================================================================================
         *  ADD MORE MANAGER MENU
         *
         *  Inject more menu item to the manager menu (for your plugin maybe?)
         * =================================================================================
         *
         * -- CODE: ------------------------------------------------------------------------
         *
         *    Config::merge('manager_menu', array(
         *        '<i class="fa fa-fw fa-icon-name"></i> <span>Menu Name</span>' => '/page',
         *        '<i class="fa fa-fw fa-icon-name"></i> <span>Menu Name</span>' => '/page',
         *        ...
         *    ));
         *
         * ---------------------------------------------------------------------------------
         *
         */

        if($more_menus = Mecha::A(Config::get('manager_menu'))) {
            $menus = $menus + array('<menu:separator>' => "") + $more_menus;
        }

        Filter::add('manager:list.item', function($menu) {
            return preg_replace('#<li.*?><a .*?><menu:separator><\/a><\/li>#', '<li class="separator"></li>', $menu);
        }, 10);

        $html  = O_BEGIN . '<div class="widget widget-manager widget-manager-menu" id="widget-manager-menu-' . self::$ids['manager-menu'] . '">' . NL;
        self::$ids['manager-menu']++;
        $html .= Menu::get($menus, 'ul', 'manager:');
        $html .= '</div>' . O_END;
        $html  = Filter::apply('widget', $html);
        return Filter::apply('widget:manager.menu', Filter::apply('widget:manager', $html));
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
        $config = Config::get();
        $speak = Config::speak();
        $year_first = $config->widget_year_first;
        $query = $config->archive_query;
        $months_array = explode(',', $speak->months);
        $archives = array();
        if( ! $files = Get::articles($sort)) {
            return O_BEGIN . '<div class="widget widget-archive">' . Config::speak('notify_empty', array(strtolower($speak->posts))) . '</div>' . O_END;
        }
        if($type == 'HIERARCHY') {
            $i = 0;
            foreach($files as $file) {
                list($year, $month, $day) = explode('-', basename($file, '.txt'));
                $archives[$year][$month][] = $file;
            }
            $html  = O_BEGIN . '<div class="widget widget-archive widget-archive-hierarchy" id="widget-archive-hierarchy-' . self::$ids['archive-hierarchy'] . '">' . NL;
            self::$ids['archive-hierarchy']++;
            $html .= TAB . '<ul>' . NL;
            foreach($archives as $year => $months) {
                if(is_array($months)) {
                    $posts_count_per_year = 0;
                    $expand = empty($query) ? $i === 0 : (int) substr($query, 0, 4) === (int) $year;
                    foreach($months as $month) {
                        $posts_count_per_year += count($month);
                    }
                    $html .= str_repeat(TAB, 2) . '<li class="archive-date ' . ($expand ? 'expanded' : 'collapsed') . ($query == $year ? ' selected' : "") . '">' . NL . str_repeat(TAB, 3) . '<a href="javascript:;" class="toggle"><span class="zippy toggle-' . ($expand ? 'open' : 'close') . '">' . ($expand ? '&#9660;' : '&#9658;') . '</span></a> <a href="' . $config->url . '/' . $config->archive->slug . '/' . $year . '">' . $year . '</a><span class="counter">' . $posts_count_per_year . '</span>' . NL;
                    $html .= str_repeat(TAB, 3) . '<ul class="' . ($expand ? 'expanded' : 'collapsed') . '">' . NL;
                    foreach($months as $month => $days) {
                        if(is_array($days)) {
                            $html .= str_repeat(TAB, 4) . '<li' . ($query == $year . '-' . $month ? ' class="selected"' : "") . '><a href="' . $config->url . '/' . $config->archive->slug . '/' . $year . '-' . $month . '">' . ($year_first ? $year . ' ' . $months_array[(int) $month - 1] : $months_array[(int) $month - 1] . ' ' . $year) . '</a><span class="counter">' . count($days) . '</span></li>' . NL;
                        }
                    }
                    $html .= str_repeat(TAB, 3) . '</ul>' . NL;
                    $html .= str_repeat(TAB, 2) . '</li>' . NL;
                }
                $i++;
            }
            $html .= TAB . '</ul>' . NL;
            $html .= '</div>' . O_END;
            $html  = Filter::apply('widget', $html);
            return Filter::apply('widget:archive.hierarchy', Filter::apply('widget:archive', $html));
        }
        if($type == 'LIST' || $type == 'DROPDOWN') {
            foreach($files as $name) {
                $archives[] = substr(basename($name, '.txt'), 0, 7);
            }
            $counter = array_count_values($archives);
            $archives = array_unique($archives);
            $i = 0;
            if($type == 'LIST') {
                $html  = O_BEGIN . '<div class="widget widget-archive widget-archive-list" id="widget-archive-list-' . self::$ids['archive-list'] . '">' . NL;
                self::$ids['archive-list']++;
                $html .= TAB . '<ul>' . NL;
                foreach($archives as $archive) {
                    list($year, $month) = explode('-', $archive);
                    $html .= str_repeat(TAB, 2) . '<li' . ($query == $year . '-' . $month ? ' class="selected"' : "") . '><a href="' . $config->url . '/' . $config->archive->slug . '/' . $archive . '">' . ($year_first ? $year . ' ' . $months_array[(int) $month - 1] : $months_array[(int) $month - 1] . ' ' . $year) . '</a><span class="counter">' . $counter[$archive] . '</span></li>' . NL;
                    $i++;
                }
                $html .= TAB . '</ul>' . NL;
                $html .= '</div>' . O_END;
                $html  = Filter::apply('widget', $html);
                return Filter::apply('widget:archive.list', Filter::apply('widget:archive', $html));
            } else {
                $html  = O_BEGIN . '<div class="widget widget-archive widget-archive-dropdown" id="widget-archive-dropdown-' . self::$ids['archive-dropdown'] . '">' . NL;
                self::$ids['archive-dropdown']++;
                $html .= TAB . '<select>' . NL . ($query === "" ? str_repeat(TAB, 2) . '<option disabled selected>' . $speak->select . '&hellip;</option>' . NL : "");
                foreach($archives as $archive) {
                    list($year, $month) = explode('-', $archive);
                    $html .= str_repeat(TAB, 2) . '<option value="' . $config->url . '/' . $config->archive->slug . '/' . $archive . '"' . ($query == $year . '-' . $month ? ' selected' : "") . '>' . ($year_first ? $year . ' ' . $months_array[(int) $month - 1] : $months_array[(int) $month - 1] . ' ' . $year) . ' (' . $counter[$archive] . ')</option>' . NL;
                }
                $html .= TAB . '</select>' . NL;
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
        $config = Config::get();
        $speak = Config::speak();
        $counters = array();
        $tags = array();
        if( ! $files = Get::articles()) {
            return O_BEGIN . '<div class="widget widget-tag">' . Config::speak('notify_empty', array(strtolower($speak->posts))) . '</div>' . O_END;
        }
        foreach($files as $file) {
            list($_time, $_kind, $_name) = explode('_', basename($file), 3);
            foreach(explode(',', $_kind) as $kind) {
                $counters[] = (int) $kind;
            }
        }
        $i = 0;
        foreach(array_count_values($counters) as $id => $count) {
            $tag = Get::rawTagsBy($id);
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
            return O_BEGIN . '<div class="widget widget-tag">' . Config::speak('notify_empty', array(strtolower($speak->tags))) . '</div>' . O_END;
        }
        $tags = Mecha::eat($tags)->order($order, $sorter)->vomit();
        if($type == 'LIST') {
            $html  = O_BEGIN . '<div class="widget widget-tag widget-tag-list" id="widget-tag-list-' . self::$ids['tag-list'] . '">' . NL;
            self::$ids['tag-list']++;
            $html .= TAB . '<ul>' . NL;
            foreach($tags as $tag) {
                $html .= str_repeat(TAB, 2) . '<li' . ($config->tag_query == $tag['slug'] ? ' class="selected"' : "") . '><a href="' . $config->url . '/' . $config->tag->slug . '/' . $tag['slug'] . '" rel="tag">' . $tag['name'] . '</a><span class="counter">' . $tag['count'] . '</span></li>' . NL;
            }
            $html .= TAB . '</ul>' . NL;
            $html .= '</div>' . O_END;
            $html  = Filter::apply('widget', $html);
            return Filter::apply('widget:tag.list', Filter::apply('widget:tag', $html));
        }
        if($type == 'CLOUD') {
            $tags_counter = array();
            foreach($tags as $tag) {
                $tags_counter[] = $tag['count'];
            }
            $highest_count = max($tags_counter);
            $html = O_BEGIN . '<div class="widget widget-tag widget-tag-cloud" id="widget-tag-cloud-' . self::$ids['tag-cloud']. '">' . NL . TAB;
            self::$ids['tag-cloud']++;
            for($i = 0, $count = count($tags); $i < $count; ++$i) {
                $normalized = $tags[$i]['count'] / $highest_count;
                $size = ceil($normalized * $max_level);
                $html .= '<span class="tag-size tag-size-' . $size . ($config->tag_query == $tags[$i]['slug'] ? ' selected' : "") . '"><a href="' . $config->url . '/' . $config->tag->slug . '/' . $tags[$i]['slug'] . '" rel="tag">' . $tags[$i]['name'] . '</a><span class="counter">' . $tags[$i]['count'] . '</span></span>';
            }
            $html .= NL . '</div>' . O_END;
            $html  = Filter::apply('widget', $html);
            return Filter::apply('widget:tag.cloud', Filter::apply('widget:tag', $html));
        }
        if($type == 'DROPDOWN') {
            $html  = O_BEGIN . '<div class="widget widget-tag widget-tag-dropdown" id="widget-tag-dropdown-' . self::$ids['tag-dropdown'] . '">' . NL;
            self::$ids['tag-dropdown']++;
            $html .= TAB . '<select>' . NL . ($config->tag_query === "" ? str_repeat(TAB, 2) . '<option disabled selected>' . $speak->select . '&hellip;</option>' . NL : "");
            foreach($tags as $tag) {
                $html .= str_repeat(TAB, 2) . '<option value="' . $config->url . '/' . $config->tag->slug . '/' . $tag['slug'] . '"' . ($config->tag_query == $tag['slug'] ? ' selected' : "") . '>' . $tag['name'] . ' (' . $tag['count'] . ')</option>' . NL;
            }
            $html .= TAB . '</select>' . NL;
            $html .= '</div>' . O_END;
            $html  = Filter::apply('widget', $html);
            return Filter::apply('widget:tag.list', Filter::apply('widget:tag', $html));
        }
    }


    /**
     * Widget Search Box
     * -----------------
     *
     * [1]. Widget::search();
     * [2]. Widget::search('search query...');
     * [3]. Widget::search('search query...', '<i class="icon icon-search"></i>');
     *
     */

    public static function search($placeholder = "", $submit = "") {
        $config = Config::get();
        $speak = Config::speak();
        $html  = O_BEGIN . '<div class="widget widget-search" id="widget-search-' . self::$ids['search'] . '">' . NL;
        self::$ids['search']++;
        $html .= TAB . '<form action="' . $config->url . '/' . $config->search->slug . '" method="post">' . NL;
        $html .= str_repeat(TAB, 2) . '<input type="text" name="q" value="' . $config->search_query . '"' . ( ! empty($placeholder) ? ' placeholder="' . $placeholder . '"' : "") . ' autocomplete="off"' . ES . '<button type="submit">' . (empty($submit) ? $speak->search : $submit) . '</button>' . NL;
        $html .= TAB . '</form>' . NL;
        $html .= '</div>' . O_END;
        $html  = Filter::apply('widget', $html);
        return Filter::apply('widget:search', $html);
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
        $config = Config::get();
        $speak = Config::speak();
        if( ! $files = Get::articles()) {
            return O_BEGIN . '<div class="widget widget-' . $class . '">' . Config::speak('notify_empty', array(strtolower($speak->posts))) . '</div>' . O_END;
        }
        if($class == 'random') {
            $files = Mecha::eat($files)->shake()->vomit();
        }
        $html  = O_BEGIN . '<div class="widget widget-' . $class . ' widget-' . $class . '-post" id="widget-' . $class . '-post-' . self::$ids[$class . '-post'] . '">' . NL;
        self::$ids[$class . '-post']++;
        $html .= TAB . '<ul>' . NL;
        for($i = 0, $count = count($files); $i < $total; ++$i) {
            if($i === $count) break;
            $article = Get::articleAnchor($files[$i]);
            $html .= str_repeat(TAB, 2) . '<li' . ($config->url_current == $article->url ? ' class="selected"' : "") . '><a href="' . $article->url . '">' . $article->title . '</a></li>' . NL;
        }
        $html .= TAB . '</ul>' . NL;
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
     * [2]. Widget::relatedPost(10);
     *
     */

    public static function relatedPost($total = 7) {
        $config = Config::get();
        if($config->page_type != 'article') {
            return self::randomPost($total);
        } else {
            if( ! $files = Get::articles('DESC', 'kind:' . implode(',', (array) $config->article->kind))) {
                return O_BEGIN . '<div class="widget widget-related widget-related-post">' . Config::speak('notify_empty', array(strtolower($speak->posts))) . '</div>' . O_END;
            }
            if(count($files) <= 1) {
                return self::randomPost($total);
            }
            $files = Mecha::eat($files)->shake()->vomit();
            $html  = O_BEGIN . '<div class="widget widget-related widget-related-post" id="widget-related-post-' . self::$ids['related-post'] . '">' . NL;
            self::$ids['related-post']++;
            $html .= TAB . '<ul>' . NL;
            for($i = 0, $count = count($files); $i < $total; ++$i) {
                if($i === $count) break;
                if($files[$i] != $config->article->path) {
                    $article = Get::articleAnchor($files[$i]);
                    $html .= str_repeat(TAB, 2) . '<li><a href="' . $article->url . '">' . $article->title . '</a></li>' . NL;
                }
            }
            $html .= TAB . '</ul>' . NL;
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
     * [2]. Widget::recentComment(10);
     *
     */

    public static function recentComment($total = 7, $avatar_size = 50, $summary = 100, $d = 'monsterid') {
        $config = Config::get();
        $speak = Config::speak();
        $html = O_BEGIN . '<div class="widget widget-recent widget-recent-comment" id="widget-recent-comment-' . self::$ids['recent-comment'] . '">' . NL;
        self::$ids['recent-comment']++;
        if($comments = Get::commentsExtract(null, 'DESC', 'time')) {
            $html .= TAB . '<ul>' . NL;
            for($i = 0, $count = count($comments); $i < $total; ++$i) {
                if($i === $count) break;
                $comment = Get::comment($comments[$i]['path']);
                $article = Get::articleAnchor($comment->post);
                $html .= str_repeat(TAB, 2) . '<li class="recent-comment-item">' . NL;
                if($avatar_size !== false && $avatar_size > 0) {
                    $html .= str_repeat(TAB, 3) . '<div class="recent-comment-avatar">' . NL;
                    $html .= str_repeat(TAB, 4) . '<img alt="' . $comment->name . '" src="' . $config->protocol . 'www.gravatar.com/avatar/' . md5($comment->email) . '?s=' . $avatar_size . '&amp;d=' . $d . '" width="' . $avatar_size . '" height="' . $avatar_size . '"' . ES . NL;
                    $html .= str_repeat(TAB, 3) . '</div>' . NL;
                }
                $html .= str_repeat(TAB, 3) . '<div class="recent-comment-header">' . NL;
                if(trim($comment->url) === "" || $comment->url == '#') {
                    $html .= str_repeat(TAB, 4) . '<span class="recent-comment-name">' . $comment->name . '</span>' . NL;
                } else {
                    $html .= str_repeat(TAB, 4) . '<a class="recent-comment-name" href="' . $comment->url . '" rel="nofollow">' . $comment->name . '</a>' . NL;
                }
                $html .= str_repeat(TAB, 3) . '</div>' . NL;
                $html .= str_repeat(TAB, 3) . '<div class="recent-comment-body"><p>' . Get::summary($comment->message, $summary, '&hellip;') . '</p></div>' . NL;
                $html .= str_repeat(TAB, 3) . '<div class="recent-comment-footer">' . NL;
                $html .= str_repeat(TAB, 4) . '<span class="recent-comment-time">' . NL;
                $html .= str_repeat(TAB, 5) . '<time datetime="' . $comment->date->W3C . '">' . $comment->date->FORMAT_3 . '</time> <a title="' . ($article ? $speak->permalink . ' ' . strtolower($speak->to) . ' &rarr; ' . strip_tags($article->title) : $speak->notify_error_not_found) . '" href="' . $comment->permalink . '" rel="nofollow">#</a>' . NL;
                $html .= str_repeat(TAB, 4) . '</span>' . NL;
                $html .= str_repeat(TAB, 3) . '</div>' . NL;
                $html .= str_repeat(TAB, 2) . '</li>' . NL;
            }
            $html .= TAB . '</ul>' . NL;
        } else {
            $html .= Config::speak('notify_empty', array(strtolower($speak->comments)));
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

    public static function add($name, $function) {
        if(isset(self::$macros[$name])) Guardian::abort(Config::speak('notify_exist', array('<code>Widget::' . $name . '()</code>')));
        self::$macros[$name] = $function;
    }


    /**
     * Call the Custom Widget
     * ----------------------
     *
     * [1]. Widget::call('my_custom_widget', $a, $b, $c);
     *
     */

    public static function call($name) {
        if( ! isset(self::$macros[$name])) Guardian::abort(Config::speak('notify_not_exist', array('<code>Widget::call(\'' . $name . '\')</code>')));
        $arguments = array_slice(func_get_args(), 1);
        $html = call_user_func_array(self::$macros[$name], $arguments);
        $html = Filter::apply('widget', $html);
        return Filter::apply('widget:custom.' . $name, Filter::apply('widget:custom', $html));
    }


    /**
     * Alternative Method for Calling the Custom Widget
     * ------------------------------------------------
     *
     * [1]. Widget::my_custom_widget($a, $b, $c);
     *
     */

    public static function __callStatic($method, $arguments = array()) {
        if( ! isset(self::$macros[$method])) Guardian::abort(Config::speak('notify_not_exist', array('<code>Widget::' . $method . '()</code>')));
        $html = call_user_func_array(self::$macros[$method], $arguments);
        $html = Filter::apply('widget', $html);
        return Filter::apply('widget:custom.' . $method, Filter::apply('widget:custom', $html));
    }

}