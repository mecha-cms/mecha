<?php

class Widget extends Weapon {

    /**
     * Widget Manager
     */
    public static function manager() {
        $config = Config::get();
        $speak = Config::speak();
        if( ! Guardian::happy()) return "";
        $total = $config->total_comments;
        if(Session::get('mecha_total_comments_diff') === "") {
            $n = $total > 0 ? '<span class="counter">' . $total . '</span>' : "";
            Session::set('mecha_total_comments_diff', $total);
        } else {
            if($total > (int) Session::get('mecha_total_comments_diff')) {
                $n = '<span class="counter">' . ($total - (int) Session::get('mecha_total_comments_diff')) . '</span>';
            } else {
                $n = "";
            }
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
            '<i class="fa fa-fw fa-magic"></i> ' . $speak->plugin => '/' . $config->manager->slug . '/plugin',
            '<i class="fa fa-fw fa-clock-o"></i> ' . $speak->cache => '/' . $config->manager->slug . '/cache'
        );
        if($config->page_type == 'article') {
            $menus['<i class="fa fa-fw fa-pencil"></i> ' . $speak->edit . ' ' . $speak->manager->this_article] = '/' . $config->manager->slug . '/article/repair/' . $config->page->id;
            $menus['<i class="fa fa-fw fa-trash-o"></i> ' . $speak->delete . ' ' . $speak->manager->this_article] = '/' . $config->manager->slug . '/article/kill/' . $config->page->id;
        }
        if($config->page_type == 'page') {
            $menus['<i class="fa fa-fw fa-pencil"></i> ' . $speak->edit . ' ' . $speak->manager->this_page] = '/' . $config->manager->slug . '/page/repair/' . $config->page->id;
            $menus['<i class="fa fa-fw fa-trash-o"></i> ' . $speak->delete . ' ' . $speak->manager->this_page] = '/' . $config->manager->slug . '/page/kill/' . $config->page->id;
        }

        /**
         * ========================================================================
         *  ADD MORE ADMIN MENU
         *
         *  Inject more menu item to admin menu widget (for your plugin maybe?)
         * ========================================================================
         *
         * -- CODE: ---------------------------------------------------------------
         *
         *    Config::merge('admin_menu', array(
         *        '<i class="fa fa-fw fa-icon-name"></i> Menu Name' => '/page',
         *        '<i class="fa fa-fw fa-icon-name"></i> Menu Name' => '/page',
         *        ...
         *    ));
         *
         * ------------------------------------------------------------------------
         *
         */

        if($more_menu = Mecha::A(Config::get('admin_menu'))) {
            $menus = $menus + $more_menu;
        }

        $html  = '<div class="widget widget-admin widget-admin-menu">';
        $html .= Menu::get($menus);
        $html .= '</div>';
        return Filter::apply('widget_admin_menu', $html);
    }

    /**
     * Widget Archive
     *
     * [1]. Widget::archive('HIERARCHY');
     * [2]. Widget::archive('HIERARCHY', 'ASC');
     *
     */
    public static function archive($type = 'HIERARCHY', $sort = 'DESC') {
        $config = Config::get();
        $year_first = $config->widget_year_first;
        $query = $config->archive_query;
        $months_array = (array) Config::speak('months');
        $archives = array();
        if( ! $files = Get::articles($sort)) return "";
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
                    if(empty($query) || ! isset($query)) {
                        $expand = $i === 0;
                    } else {
                        $expand = (int) substr($query, 0, 4) == (int) $year ? true : false;
                    }
                    foreach($months as $month) {
                        $posts_count_per_year += count($month);
                    }
                    $html .= '<li class="archive-date ' . ($expand ? 'expanded' : 'collapsed') . '"><a href="javascript:;" class="toggle"><span class="zippy' . ($expand ? ' toggle-open' : "") . '">' . ($expand ? '&#9660;' : '&#9658;') . '</span></a> <a href="' . $config->url . '/' . $config->archive->slug . '/' . $year . '">' . $year . '</a><span class="counter">' . $posts_count_per_year . '</span>';
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
            return Filter::apply('widget_archive_HIERARCHY', $html);
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
                return Filter::apply('widget_archive_LIST', $html);
            } else {
                $html  = '<div class="widget widget-archive widget-archive-dropdown" id="widget-archive-dropdown">';
                $html .= '<select>';
                foreach($archives as $archive) {
                    list($year, $month) = explode('-', $archive);
                    $html .= '<option value="' . $config->url . '/' . $config->archive->slug . '/' . $archive . '"' . ($query == $year . '-' . $month ? ' selected' : "") . '>' . ($year_first ? $year . ' ' . $months_array[(int) $month - 1] : $months_array[(int) $month - 1] . ' ' . $year) . ' (' . $counter[$archive] . ')</option>';
                }
                $html .= '</select>';
                $html .= '</div>';
                return Filter::apply('widget_archive_DROPDOWN', $html);
            }
        }
    }

    /**
     * Widget Tag
     *
     * [1]. Widget::tag('LIST');
     * [2]. Widget::tag('LIST', 'ASC');
     * [3]. Widget::tag('CLOUD', 'ASC', 'count');
     * [4]. Widget::tag('CLOUD', 'ASC', 'name', 7);
     *
     */
    public static function tag($type = 'LIST', $order = 'ASC', $sorter = 'name', $max_level = 6) {
        $config = Config::get();
        $counters = array();
        $tags = array();
        if( ! $files = Get::articles()) return "";
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
        if(empty($tags)) return "";
        $tags = Mecha::eat($tags)->order($order, $sorter)->vomit();
        if($type == 'LIST') {
            $lists = array();
            $html  = '<div class="widget widget-tag widget-tag-list">';
            $html .= '<ul>';
            foreach($tags as $tag) {
                $html .= '<li' . ($config->tag_query == $tag['slug'] ? ' class="selected"' : "") . '><a href="' . $config->url . '/' . $config->tag->slug . '/' . $tag['slug'] . '" rel="tag">' . $tag['name'] . '</a><span class="counter">' . $tag['count'] . '</span></li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
            return Filter::apply('widget_tag_LIST', $html);
        }
        if($type == 'CLOUD') {
            $tags_counter = array();
            foreach($tags as $tag) {
                $tags_counter[] = $tag['count'];
            }
            $highest_count = max($tags_counter);
            $html  = '<div class="widget widget-tag widget-tag-cloud">';
            for($i = 0, $counts = count($tags); $i < $counts; ++$i) {
                $normalized = $tags[$i]['count'] / $highest_count;
                $size = ceil($normalized * $max_level);
                $html .= '<span class="tag-size tag-size-' . $size . ($config->tag_query == $tags[$i]['slug'] ? ' selected' : "") . '"><a href="' . $config->url . '/' . $config->tag->slug . '/' . $tags[$i]['slug'] . '">' . $tags[$i]['name'] . '</a><span class="counter">' . $tags[$i]['count'] . '</span></span>';
            }
            $html .= '</div>';
            return Filter::apply('widget_tag_CLOUD', $html);
        }
    }

    /**
     * Widget Search Box
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
        return Filter::apply('widget_search', $html);
    }

    /**
     * Widget Recent Post
     *
     * [1]. Widget::recentPost();
     * [2]. Widget::recentPost(5);
     *
     */
    public static function recentPost($total = 7, $class = 'recent') {
        if( ! $files = Get::articles('DESC')) return "";
        if($class == 'random') {
            $files = Mecha::eat($files)->shake()->vomit();
        }
        $html  = '<div class="widget widget-' . $class . ' widget-' . $class . '-post">';
        $html .= '<ul>';
        for($i = 0, $count = count($files); $i < $total; ++$i) {
            if($i == $count) break;
            $article = Get::article($files[$i], array('content', 'css', 'js', 'comments'));
            $html .= '<li' . (Config::get('url_current') == $article->url ? ' class="selected"' : "") . '><a href="' . $article->url . '">' . $article->title . '</a></li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
        return Filter::apply('widget_' . $class . '_post', $html);
    }

    /**
     * Widget Random Post
     *
     * [1]. Widget::randomPost();
     * [1]. Widget::randomPost(5);
     *
     */
    public static function randomPost($total = 7) {
        return self::recentPost($total, 'random');
    }

    /**
     * This is just an alias for `Weapon::fire()` !!!
     */
    public function call($name, $arguments = array()) {
        self::fire($name, $arguments, true);
    }

}