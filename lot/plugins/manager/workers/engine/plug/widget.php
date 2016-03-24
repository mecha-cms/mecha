<?php


/**
 * Widget Manager
 * --------------
 *
 * [1]. Widget::manager('MENU');
 * [2]. Widget::manager('BAR');
 *
 */

Widget::plug('manager', function($type = 'MENU') use($config, $speak) {
    if( ! Guardian::happy()) return "";
    $T1 = TAB;
    $kin = strtolower($type);
    $id = Config::get('widget_manager_' . $kin . '_id', 0) + 1;
    $html = O_BEGIN . '<div class="widget widget-manager widget-manager-' . $kin . '" id="widget-manager-' . $kin . '-' . $id . '">' . NL;
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
                    $_k = (strpos($v['icon'], '<') === false ? Jot::icon($v['icon'], 'fw') : $v['icon']) . ' <span class="label">' . $k . '</span>' . (isset($v['count']) && ($v['count'] === '&infin;' || (float) $v['count'] > 0) ? ' <span class="counter">' . $v['count'] . '</span>' : "");
                    $menus[$_k] = isset($v['url']) ? $v['url'] : null;
                }
            }
        }
        Menu::add('manager', $menus);
        $html .= Menu::manager('ul', $T1);
    }
    if($type === 'BAR') {
        $bars = array();
        if($_bars = Mecha::A(Config::get('manager_bar'))) {
            $_bars = Mecha::eat($_bars)->order('ASC', 'stack', true, 10)->vomit();
            foreach($_bars as $k => $v) {
                if(is_string($v)) {
                    $bar = $v;
                } else {
                    $t = ' data-tooltip="' . Text::parse(isset($v['description']) ? $v['description'] : $k, '->encoded_html') . '"';
                    if(isset($v['url'])) {
                        $url = Filter::colon('manager:url', Converter::url($v['url']));
                        $c = $config->url_current === $url ? ' ' . Widget::$config['classes']['current'] : "";
                        $bar = '<a class="item' . $c .'" href="' . $url . '"' . $t . '>';
                    } else {
                        $bar = '<span class="item a"' . $t . '>';
                    }
                    $bar .= isset($v['icon']) ? (strpos($v['icon'], '<') === false ? Jot::icon($v['icon']) : $v['icon']) : $k;
                    $bar .= ' <span class="label">' . $k . '</span>';
                    if(isset($v['count']) && ($v['count'] === '&infin;' || (float) $v['count'] > 0)) {
                        $bar .= ' <span class="counter">' . $v['count'] . '</span>';
                    }
                    $bar .= isset($v['url']) ? '</a>' : '</span>';
                }
                $bars[] = $bar;
            }
        }
        $html .= $T1 . implode(' ', $bars) . NL;
    }
    $html .= '</div>' . O_END;
    $html = Filter::apply('widget', $html);
    Config::set('widget_manager_' . $kin . '_id', $id);
    return Filter::apply('widget:manager.' . $kin, Filter::apply('widget:manager', $html));
});