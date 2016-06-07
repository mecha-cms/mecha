<?php

class Cell extends __ {

    protected static $attr_order = array(
        'src' => null,
        'alt' => null,
        'width' => null,
        'height' => null,
        'property' => null,
        'name' => null,
        'content' => null,
        'class' => null,
        'id' => null,
        'href' => null,
        'rel' => null,
        'target' => null,
        'type' => null,
        'action' => null,
        'method' => null,
        'enctype' => null,
        'value' => null,
        'placeholder' => null,
        'label' => null,
        'selected' => null,
        'checked' => null,
        'disabled' => null,
        'readonly' => null,
        'style' => null
    );

    protected static $tag = array();
    protected static $tag_indent = array();

    // Encode all HTML entit(y|ies)
    public static function protect($value) {
        if( ! is_string($value)) return $value;
        return Text::parse($value, '->encoded_html');
    }

    // Setup HTML attribute(s) ...
    public static function bond($array, $unit = "") {
        if( ! is_array($array)) {
            $attr = trim((string) $array);
            return strlen($attr) ? ' ' . $attr : ""; // no filter(s) applied ...
        }
        $output = "";
        $c = strtolower(get_called_class());
        $unit = $unit ? '.' . $unit : "";
        $array = Filter::apply($c . ':bond' . $unit, array_replace(self::$attr_order, $array));
        // HTML5 `data-*` attribute
        if(isset($array['data']) && is_array($array['data'])) {
            foreach($array['data'] as $k => $v) {
                if( ! is_null($v)) {
                    $array['data-' . $k] = $v;
                }
            }
            unset($array['data']);
        }
        foreach($array as $attr => $value) {
            if( ! is_null($value)) {
                if(is_array($value)) {
                    // Inline CSS via `style` attribute
                    if($attr === 'style') {
                        $css = "";
                        foreach($value as $k => $v) {
                            if( ! is_null($v)) {
                                $css .= $k . ': ' . str_replace('"', '&quot;', $v) . '; ';
                            }
                        }
                        $value = rtrim($css);
                    } else {
                        $value = implode(' ', array_unique($value));
                    }
                }
                $q = is_string($value) && strpos($value, '"') !== false ? "'" : '"';
                $output .= ' ' . ($value !== true ? $attr . '=' . $q . $value . $q : $attr);
            }
        }
        return $output;
    }

    // Base HTML tag constructor
    public static function unit($tag = 'html', $content = "", $attr = array(), $indent = 0) {
        $indent = $indent ? str_repeat(TAB, $indent) : "";
        $c = strtolower(get_called_class());
        if($content === false) {
            return Filter::apply($c . ':unit.' . $tag, $indent . '<' . $tag . self::bond($attr, $tag) . ES, $attr);
        }
        return Filter::apply($c . ':unit.' . $tag, $indent . '<' . $tag . self::bond($attr, $tag) . '>' . ( ! is_null($content) ? $content : "") . '</' . $tag . '>', $attr);
    }

    // Alias for `Cell::unit()`
    public static function unite() {
        return call_user_func_array('self::unit', func_get_args());
    }

    // Inverse version of `Cell::unit()`
    public static function apart($input, $q = '"') {
        $tag = array('<', '>', ' ', '/', '[\w\-.:]+');
        $attr = array($q, $q, '=', '[\w\-.:]+');
        return Converter::attr(trim($input), $tag, $attr, true);
    }

    // HTML comment
    public static function __($content = "", $indent = 0, $block = false) {
        $indent = $indent ? str_repeat(TAB, $indent) : "";
        if($block === true) {
            $block_start = str_repeat(NL, 2);
            $block_end = $block_start . $indent;
        } else {
            if($block === false) {
                $block = ' ';
            }
            $block_start = $block;
            $block_end = $block;
        }
        $c = strtolower(get_called_class());
        return Filter::apply($c . ':unit.__', $indent . '<!--' . $block_start . $content . $block_end . '-->');
    }

    // Base HTML tag open
    public static function begin($tag = 'html', $attr = array(), $indent = 0) {
        $indent = $indent ? str_repeat(TAB, $indent) : "";
        self::$tag[] = $tag;
        self::$tag_indent[] = $indent;
        $c = strtolower(get_called_class());
        return Filter::apply($c . ':begin.' . $tag, $indent . '<' . $tag . self::bond($attr, $tag) . '>', $attr);
    }

    // Base HTML tag close
    public static function end($tag = null, $indent = null) {
        if(is_null($tag)) {
            $tag = ! empty(self::$tag) ? array_pop(self::$tag) : false;
        }
        if(is_null($indent)) {
            $indent = ! empty(self::$tag_indent) ? array_pop(self::$tag_indent) : "";
        } else {
            $indent = $indent ? str_repeat(TAB, $indent) : "";
        }
        $c = strtolower(get_called_class());
        return Filter::apply($c . ':end.' . $tag, $tag ? $indent . '</' . $tag . '>' : "");
    }

    // Add new method with `Cell::add('foo')`
    public static function add($kin, $action) {
        self::plug($kin, $action);
    }

    // Call the added method with `Cell::foo()`
    public static function __callStatic($kin, $arguments = array()) {
        $c = get_called_class();
        if( ! isset(self::$_[$c][$kin])) {
            $arguments = array_merge(array($kin), $arguments);
            return call_user_func_array('self::unit', $arguments);
        }
        $s = parent::__callStatic($kin, $arguments);
        return Filter::apply(strtolower($c) . ':custom.' . $kin, $s, $arguments);
    }

}