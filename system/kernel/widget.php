<?php

class Widget extends Weapon {


    /**
     * Widget Manager
     * --------------
     *
     * [1]. echo Widget::manager();
     *
     */

    public static function manager() {
        $config = Config::get();
        $speak = Config::speak();
        if( ! Guardian::happy()) return "";
        $total = $config->total_comments;
        $destination = SYSTEM . DS . 'log' . DS . 'comments.total.txt';
        $n = $total > 0 ? '<span class="counter">' . $total . '</span>' : "";
        if($file = File::exist($destination)) {
            $old = (int) File::open($file)->read();
            $n = ($total > $old) ? '<span class="counter">' . ($total - $old) . '</span>' : "";
        } else {
            File::write($total)->saveTo($destination, 0600);
        }
        $menus = array(
            '<i class="fa fa-fw fa-cogs"></i> ' . $speak->config => '/' . $config->manager->slug . '/config',
            '<i class="fa fa-fw fa-file-text"></i> ' . $speak->article => '/' . $config->manager->slug . '/article',
            '<i class="fa fa-fw fa-file"></i> ' . $speak->page => '/' . $config->manager->slug . '/page',
            '<i class="fa fa-fw fa-comments"></i> ' . $speak->comment . $n => '/' . $config->manager->slug . '/comment',
            '<i class="fa fa-fw fa-tags"></i> ' . $speak->tag => '/' . $config->manager->slug . '/tag',
            '<i class="fa fa-fw fa-bars"></i> ' . $speak->menu => '/' . $config->manager->slug . '/menu',
            '<i class="fa fa-fw fa-briefcase"></i> ' . $speak->asset => '/' . $config->manager->slug . '/asset',
            '<i class="fa fa-fw fa-th-list"></i> ' . $speak->field => '/' . $config->manager->slug . '/field',
            '<i class="fa fa-fw fa-coffee"></i> ' . $speak->shortcode => '/' . $config->manager->slug . '/shortcode',
            '<i class="fa fa-fw fa-shield"></i> ' . $speak->shield => '/' . $config->manager->slug . '/shield',
            '<i class="fa fa-fw fa-magic"></i> ' . $speak->plugin => '/' . $config->manager->slug . '/plugin',
            '<i class="fa fa-fw fa-clock-o"></i> ' . $speak->cache => '/' . $config->manager->slug . '/cache',
            '<i class="fa fa-fw fa-life-ring"></i> ' . $speak->backup => '/' . $config->manager->slug . '/backup'
        );
        if($config->page_type == 'article') {
            $menus['<i class="fa fa-fw fa-pencil"></i> ' . $speak->edit . ' ' . $speak->manager->this_article] = '/' . $config->manager->slug . '/article/repair/id:' . $config->page->id;
            $menus['<i class="fa fa-fw fa-trash-o"></i> ' . $speak->delete . ' ' . $speak->manager->this_article] = '/' . $config->manager->slug . '/article/kill/id:' . $config->page->id;
        }
        if($config->page_type == 'page') {
            $menus['<i class="fa fa-fw fa-pencil"></i> ' . $speak->edit . ' ' . $speak->manager->this_page] = '/' . $config->manager->slug . '/page/repair/id:' . $config->page->id;
            $menus['<i class="fa fa-fw fa-trash-o"></i> ' . $speak->delete . ' ' . $speak->manager->this_page] = '/' . $config->manager->slug . '/page/kill/id:' . $config->page->id;
        }

        /**
         * ========================================================================
         *  ADD MORE MANAGER MENU
         *
         *  Inject more menu item to the manager menu (for your plugin maybe?)
         * ========================================================================
         *
         * -- CODE: ---------------------------------------------------------------
         *
         *    Config::merge('manager_menu', array(
         *        '<i class="fa fa-fw fa-icon-name"></i> Menu Name' => '/page',
         *        '<i class="fa fa-fw fa-icon-name"></i> Menu Name' => '/page',
         *        ...
         *    ));
         *
         * ------------------------------------------------------------------------
         *
         */

        if($more_menus = Mecha::A(Config::get('manager_menu'))) {
            $menus = $menus + array('{{separator}}' => "") + $more_menus;
        }

        Filter::add('manager:list.item', function($menu) {
            return preg_replace('#<li.*?><a .*?>\{\{separator\}\}<\/a><\/li>#', '<li class="separator"></li>', $menu);
        }, 10);

        $html  = '<div class="widget widget-manager widget-manager-menu">';
        $html .= Menu::get($menus, 'ul', 'manager:');
        $html .= '</div>';
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
        $months_array = (array) $speak->months;
        $archives = array();
        if( ! $files = Get::articles($sort)) {
            return '<div class="widget widget-archive">' . Config::speak('notify_empty', array(strtolower($speak->posts))) . '</div>';
        }
        if($type == 'HIERARCHY') {
            $i = 0;
            foreach($files as $file_name) {
                list($year, $month, $day) = explode('-', basename($file_name, '.txt'));
                $archives[$year][$month][] = $day;
            }
            $html  = '<div class="widget widget-archive widget-archive-hierarchy" id="widget-archive-hierarchy">';
            $html .= '<ul>';
            foreach($archives as $year => $months) {
                if(is_array($months)) {
                    $posts_count_per_year = 0;
                    if(empty($query)) {
                        $expand = $i === 0;
                    } else {
                        $expand = (int) substr($query, 0, 4) == (int) $year ? true : false;
                    }
                    foreach($months as $month) {
                        $posts_count_per_year += count($month);
                    }
                    $html .= '<li class="archive-date ' . ($expand ? 'expanded' : 'collapsed') . ($query == $year ? ' selected' : "") . '"><a href="javascript:;" class="toggle"><span class="zippy' . ($expand ? ' toggle-open' : "") . '">' . ($expand ? '&#9660;' : '&#9658;') . '</span></a> <a href="' . $config->url . '/' . $config->archive->slug . '/' . $year . '">' . $year . '</a><span class="counter">' . $posts_count_per_year . '</span>';
                    $html .= '<ul class="' . ($expand ? 'expanded' : 'collapsed') . '">';
                    foreach($months as $month => $days) {
                        if(is_array($days)) {
                            $html .= '<li' . ($query == $year . '-' . $month ? ' class="selected"' : "") . '><a href="' . $config->url . '/' . $config->archive->slug . '/' . $year . '-' . $month . '">' . ($year_first ? $year . ' ' . $months_array[(int) $month - 1] : $months_array[(int) $month - 1] . ' ' . $year) . '</a><span class="counter">' . count($days) . '</span></li>';
                        }
                    }
                    $html .= '</ul>';
                    $html .= '</li>';
                }
                $i++;
            }
            $html .= '</ul>';
            $html .= '</div>';
            $html  = Filter::apply('widget', $html);
            return Filter::apply('widget:archive.hierarchy', Filter::apply('widget:archive', $html));
        }
        if($type == 'LIST' || $type == 'DROPDOWN') {
            foreach($files as $file_name) {
                $archives[] = substr(basename($file_name, '.txt'), 0, 7);
            }
            $counter = array_count_values($archives);
            $archives = array_unique($archives);
            $i = 0;
            if($type == 'LIST') {
                $html  = '<div class="widget widget-archive widget-archive-list">';
                $html .= '<ul>';
                foreach($archives as $archive) {
                    list($year, $month) = explode('-', $archive);
                    $html .= '<li' . ($query == $year . '-' . $month ? ' class="selected"' : "") . '><a href="' . $config->url . '/' . $config->archive->slug . '/' . $archive . '">' . ($year_first ? $year . ' ' . $months_array[(int) $month - 1] : $months_array[(int) $month - 1] . ' ' . $year) . '</a><span class="counter">' . $counter[$archive] . '</span></li>';
                    $i++;
                }
                $html .= '</ul>';
                $html .= '</div>';
                $html  = Filter::apply('widget', $html);
                return Filter::apply('widget:archive.list', Filter::apply('widget:archive', $html));
            } else {
                $html  = '<div class="widget widget-archive widget-archive-dropdown" id="widget-archive-dropdown">';
                $html .= '<select>';
                foreach($archives as $archive) {
                    list($year, $month) = explode('-', $archive);
                    $html .= '<option value="' . $config->url . '/' . $config->archive->slug . '/' . $archive . '"' . ($query == $year . '-' . $month ? ' selected' : "") . '>' . ($year_first ? $year . ' ' . $months_array[(int) $month - 1] : $months_array[(int) $month - 1] . ' ' . $year) . ' (' . $counter[$archive] . ')</option>';
                }
                $html .= '</select>';
                $html .= '</div>';
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
            return '<div class="widget widget-tag">' . Config::speak('notify_empty', array(strtolower($speak->posts))) . '</div>';
        }
        foreach(Get::extract($files) as $file) {
            foreach($file['kind'] as $kind) {
                $counters[] = $kind;
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
            return '<div class="widget widget-tag">' . Config::speak('notify_empty', array(strtolower($speak->tags))) . '</div>';
        }
        $tags = Mecha::eat($tags)->order($order, $sorter)->vomit();
        if($type == 'LIST') {
            $html  = '<div class="widget widget-tag widget-tag-list">';
            $html .= '<ul>';
            foreach($tags as $tag) {
                $html .= '<li' . ($config->tag_query == $tag['slug'] ? ' class="selected"' : "") . '><a href="' . $config->url . '/' . $config->tag->slug . '/' . $tag['slug'] . '" rel="tag">' . $tag['name'] . '</a><span class="counter">' . $tag['count'] . '</span></li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
            $html  = Filter::apply('widget', $html);
            return Filter::apply('widget:tag.list', Filter::apply('widget:tag', $html));
        }
        if($type == 'CLOUD') {
            $tags_counter = array();
            foreach($tags as $tag) {
                $tags_counter[] = $tag['count'];
            }
            $highest_count = max($tags_counter);
            $html = '<div class="widget widget-tag widget-tag-cloud">';
            for($i = 0, $count = count($tags); $i < $count; ++$i) {
                $normalized = $tags[$i]['count'] / $highest_count;
                $size = ceil($normalized * $max_level);
                $html .= '<span class="tag-size tag-size-' . $size . ($config->tag_query == $tags[$i]['slug'] ? ' selected' : "") . '"><a href="' . $config->url . '/' . $config->tag->slug . '/' . $tags[$i]['slug'] . '" rel="tag">' . $tags[$i]['name'] . '</a><span class="counter">' . $tags[$i]['count'] . '</span></span>';
            }
            $html .= '</div>';
            $html  = Filter::apply('widget', $html);
            return Filter::apply('widget:tag.cloud', Filter::apply('widget:tag', $html));
        }
    }


    /**
     * Widget Search Box
     * -----------------
     *
     * [1]. Widget::search();
     * [2]. Widget::search('Search query...');
     * [3]. Widget::search('Search query...', '<i class="icon icon-search"></i>');
     *
     */

    public static function search($placeholder = "", $submit = "") {
        $config = Config::get();
        $speak = Config::speak();
        $html  = '<div class="widget widget-search">';
        $html .= '<form action="' . $config->url . '/' . $config->search->slug . '" method="post">';
        $html .= '<input type="text" name="q" value="' . $config->search_query . '"' . ( ! empty($placeholder) ? ' placeholder="' . $placeholder . '"' : "") . ' autocomplete="off">';
        $html .= '<button type="submit">' . (empty($submit) ? $speak->search : $submit) . '</button>';
        $html .= '</form>';
        $html .= '</div>';
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
        if( ! $files = Get::articles('DESC')) {
            return '<div class="widget widget-' . $class . '">' . Config::speak('notify_empty', array(strtolower($speak->posts))) . '</div>';
        }
        if($class == 'random') {
            $files = Mecha::eat($files)->shake()->vomit();
        }
        $html  = '<div class="widget widget-' . $class . ' widget-' . $class . '-post">';
        $html .= '<ul>';
        for($i = 0, $count = count($files); $i < $total; ++$i) {
            if($i === $count) break;
            $article = Get::articleAnchor($files[$i]);
            $html .= '<li' . ($config->url_current == $article->url ? ' class="selected"' : "") . '><a href="' . $article->url . '">' . $article->title . '</a></li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
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
            if( ! $files = Get::articles('DESC', 'kind:' . implode(',', (array) $config->page->kind))) {
                return '<div class="widget widget-related widget-related-post">' . Config::speak('notify_empty', array(strtolower($speak->posts))) . '</div>';
            }
            if(count($files) <= 1) {
                return self::randomPost($total);
            }
            $files = Mecha::eat($files)->shake()->vomit();
            $html  = '<div class="widget widget-related widget-related-post">';
            $html .= '<ul>';
            for($i = 0, $count = count($files); $i < $total; ++$i) {
                if($i === $count) break;
                if($files[$i] != $config->page->file_path) {
                    $article = Get::articleAnchor($files[$i]);
                    $html .= '<li><a href="' . $article->url . '">' . $article->title . '</a></li>';
                }
            }
            $html .= '</ul>';
            $html .= '</div>';
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

    public static function recentComment($total = 7, $avatar_size = 50, $summary = 100) {
        $config = Config::get();
        $speak = Config::speak();
        $html = '<div class="widget widget-recent widget-recent-comment">';
        if($comments = Get::comments()) {
            $html .= '<ul>';
            foreach($comments as $comment) {
                $comment = Get::comment($comment['id']);
                $article = Get::articleAnchor($comment->post);
                $html .= '<li class="recent-comment-item">';
                if($avatar_size !== false && $avatar_size > 0) {
                    $html .= '<div class="recent-comment-avatar">';
                    $html .= '<img alt="' . $comment->name . '" src="' . $config->protocol . 'www.gravatar.com/avatar/' . md5($comment->email) . '?s=' . $avatar_size . '&amp;d=monsterid" width="' . $avatar_size . '" height="' . $avatar_size . '">';
                    $html .= '</div>';
                }
                $html .= '<div class="recent-comment-header">';
                if($comment->url != '#') {
                    $html .= '<a class="recent-comment-name" href="' . $comment->url . '" rel="nofollow">' . $comment->name . '</a>';
                } else {
                    $html .= '<span class="recent-comment-name">' . $comment->name . '</span>';
                }
                $html .= '</div>';
                $html .= '<div class="recent-comment-body">' . Get::summary($comment->message, $summary, '&hellip;') . '</div>';
                $html .= '<div class="recent-comment-footer">';
                $html .= '<span class="recent-comment-time">';
                $html .= '<time datetime="' . Date::format($comment->time, 'c') . '">' . Date::format($comment->time, 'Y/m/d H:i:s') . '</time>';
                $html .= ' <a title="' . ($article ? $speak->permalink . ' ' . strtolower($speak->to) . ' &ldquo;' . $article->title . '&rdquo;' : $speak->notify_error_not_found) . '" href="' . $comment->permalink . '" rel="nofollow">#</a>';
                $html .= '</span>';
                $html .= '</div>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= Config::speak('notify_empty', array(strtolower($speak->comments)));
        }
        $html .= '</div>';
        $html  = Filter::apply('widget', $html);
        return Filter::apply('widget:recent.comment', Filter::apply('widget:recent', $html));
    }


    /**
     * Custom Widget
     * -------------
     *
     * [1]. Widget::call('my_custom_widget');
     *
     */

    public static function call($name, $arguments = array()) {
        $html = self::fire($name, $arguments, true);
        $html = Filter::apply('widget', $html);
        return Filter::apply('widget:custom.' . $name, Filter::apply('widget:custom', $html));
    }

}