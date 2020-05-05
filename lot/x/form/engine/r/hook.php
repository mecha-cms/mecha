<?php namespace _\lot\x\form;

function set($content) {
    // Convert `foo[bar][baz]` to `form.foo.bar.baz`
    $keys = function(string $in) {
        return 'form.' . \str_replace(['.', '[', ']', \P], [\P, '.', "", "\\."], $in);
    };
    if (false !== \strpos($content, '<input ')) {
        $content = \preg_replace_callback('/<input(?:\s[^>]*)?>/', function($m) use($keys) {
            $input = new \HTML($m[0]);
            if (!$name = $input['name']) {
                return $m[0];
            }
            $type = $input['type'];
            if ('hidden' === $type || 'password' === $type) {
                // Disable form session on `password` and `hidden` input
                return $m[0];
            }
            $name = $keys($name);
            $value = \Session::get($name);
            if ('checkbox' === $type || 'radio' === $type) {
                if (isset($value)) {
                    $input['checked'] = \s($value) === \s($input['value']);
                }
            } else {
                $input['value'] = $value ?? $input['value'];
            }
            return $input;
        }, $content);
    }
    if (false !== \strpos($content, '<select ')) {
        $content = \preg_replace_callback('/<select(?:\s[^>]*)?>[\s\S]*?<\/select>/', function($m) use($keys) {
            $select = new \HTML($m[0]);
            if (!$name = $select['name']) {
                return $m[0];
            }
            $name = $keys($name);
            $value = \Session::get($name);
            $select[1] = \preg_replace_callback('/<option(?:\s[^>]*)?>[\s\S]*?<\/option>/', function($m) use($value) {
                $option = new \HTML($m[0]);
                if (isset($value)) {
                    $option['selected'] = \s($value) === \s($option['value'] ?? $option[1]);
                }
                return $option;
            }, $select[1]);
            return $select;
        }, $content);
    }
    if (false !== \strpos($content, '<textarea ')) {
        $content = \preg_replace_callback('/<textarea(?:\s[^>]*)?>[\s\S]*?<\/textarea>/', function($m) use($keys) {
            $textarea = new \HTML($m[0]);
            if (!$name = $textarea['name']) {
                return $m[0];
            }
            $name = $keys($name);
            $value = \Session::get($name);
            $textarea[1] = \is_string($value) ? \htmlspecialchars($value) : $textarea[1];
            return $textarea;
        }, $content);
    }
    return $content;
}

function let() {
    \Session::let('form');
}

\Hook::set('content', __NAMESPACE__ . "\\set", 0);
\Hook::set('let', __NAMESPACE__ . "\\let", 20);
