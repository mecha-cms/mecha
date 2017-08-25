<?php

// Hard–coded data key(s) which the value must be standardized: `time`, `slug`
function _fn_get_page_worker($v, $n = null) {
    $n = $n ?: Path::N($v);
    $v = file_get_contents($v);
    if ($n === 'time') {
        $v = (new Date($v))->format();
    } else if ($n === 'slug') {
        $v = h($v);
    }
    return $v;
}

function fn_get_page($path, $key = null, $fail = false, $for = null) {
    if (!file_exists($path)) return false;
    $date = date(DATE_WISE, File::T($path, time()));
    $o = [
        'path' => $path,
        'time' => $date,
        'update' => $date,
        'slug' => Path::N($path),
        'state' => Path::X($path)
    ];
    $output = Page::open($path, array_replace([
        $for => null
    ], $o))->get($o);
    $data = Path::F($path);
    if (is_dir($data)) {
        if ($for === null) {
            foreach (g($data, 'data') as $v) {
                $n = Path::N($v);
                $output[$n] = e(_fn_get_page_worker($v, $n));
            }
        } else if ($v = File::exist($data . DS . $for . '.data')) {
            $output[$for] = e(_fn_get_page_worker($v, $for));
        }
    }
    return !isset($key) ? $output : (array_key_exists($key, $output) ? $output[$key] : $fail);
}

function fn_get_pages($folder = PAGE, $state = 'page', $sort = [-1, 'time'], $key = null) {
    $output = [];
    $by = is_array($sort) && isset($sort[1]) ? $sort[1] : null;
    if ($input = g($folder, $state)) {
        foreach ($input as $v) {
            $output[] = fn_get_page($v, null, false, $by);
        }
        $output = $o = Anemon::eat($output)->sort($sort)->vomit();
        if (isset($key)) {
            $o = [];
            foreach ($output as $v) {
                if (!array_key_exists($key, $v)) continue;
                $o[] = $v[$key];
            }
        }
        unset($output);
        return !empty($o) ? $o : false;
    }
    return false;
}

Get::plug('page', 'fn_get_page');
Get::plug('pages', 'fn_get_pages');