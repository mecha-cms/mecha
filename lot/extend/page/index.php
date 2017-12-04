<?php

// Require the plug manually…
require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'get.php';

// Store page state to registry…
if ($state = Extend::state(__DIR__)) {
    Config::extend($state);
}

$path = $url->path;
$b = DS . Path::B($path);
$folder = PAGE . DS . $path;

$site->is = '404'; // default is `404`
$site->state = 'page'; // default is `page`

if (!$path || $path === $site->path) {
    $site->is = ""; // home page type is ``
} else if ($file = File::exist([
    $folder . '.page',
    $folder . '.archive',
    $folder . $b . '.page',
    $folder . $b . '.archive'
])) {
    $site->is = 'page';
    $site->state = Path::X($file);
    if (!File::exist($folder . $b . '.page') && Get::pages($folder, 'page')) {
        $site->is = 'pages';
    }
}

function fn_page_url($content, $lot = []) {
    if (!isset($lot['path'])) {
        return $content;
    }
    global $url;
    $s = Path::F($lot['path'], PAGE);
    return rtrim($url . '/' . ltrim(To::url($s), '/'), '/');
}

Hook::set('page.url', 'fn_page_url', 1);

Lot::set([
    'message' => Message::get(),
    'token' => Guardian::token()
]);

Route::set(['%*%/%i%', '%*%', ""], function($path = "", $step = null) {
    // Include global variable(s)…
    extract(Lot::get(null, []));
    // Prevent directory traversal attack <https://en.wikipedia.org/wiki/Directory_traversal_attack>
    $path = str_replace('../', "", urldecode($path));
    $path_f = ltrim($path === "" ? $site->path : $path, '/');
    Config::set('step', $step); // 1–based index…
    if ($step === 1 && !$url->query && $path === $site->path) {
        Message::info('kick', '<code>' . $url->current . '</code>');
        Guardian::kick(""); // Redirect to home page…
    }
    $folder = rtrim(PAGE . DS . To::path($path_f), DS);
    $name = Path::B($folder);
    $i = ($h = $step ?: 1) - 1; // 0-based index…
    // Horizontal elevator…
    $elevator = [
        'direction' => [
           '-1' => 'previous',
            '1' => 'next'
        ],
        'union' => [
           '-2' => [
                2 => ['rel' => null]
            ],
           '-1' => [
                1 => Elevator::WEST,
                2 => ['rel' => 'prev']
            ],
            '1' => [
                1 => Elevator::EAST,
                2 => ['rel' => 'next']
            ]
        ]
    ];
    // Placeholder…
    Lot::set([
        'pager' => new Elevator([], 1, 0, true, $elevator, $site->is),
        'page' => new Page
    ]);
    // --ditto
    $pages = $page = [];
    Config::set('page.title', new Anemon([$site->title], ' &#x00B7; '));
    if ($file = File::exist([
        // Check for page that has numeric file name…
        $folder . DS . $h . '.page', // `lot\page\page-slug\1.page`
        $folder . DS . $h . '.archive', // `lot\page\page-slug\1.archive`
        $folder . DS . $h . DS . $h . '.page', // `lot\page\page-slug\1\1.page`
        $folder . DS . $h . DS . $h . '.archive', // `lot\page\page-slug\1\1.archive`
        // Else…
        $folder . '.page', // `lot\page\page-slug.page`
        $folder . '.archive', // `lot\page\page-slug.archive`
        $folder . DS . $name . '.page', // `lot\page\page-slug\page-slug.page`
        $folder . DS . $name . '.archive' // `lot\page\page-slug\page-slug.archive`
    ])) { // File does exist, then …
        if ($path !== "") {
            $site->is = 'page';
        }
        // Load user function(s) from the current page folder if any, stacked from the parent page(s)
        $k = PAGE;
        foreach (explode('/', '/' . $path) as $v) {
            $k .= $v ? DS . $v : "";
            if ($fn = File::exist($k . DS . 'index.php')) include $fn;
            if ($fn = File::exist($k . DS . 'index__.php')) include $fn;
        }
        $page = new Page($file);
        $sort = $page->sort($site->sort);
        $chunk = $page->chunk($site->chunk);
        // Create elevator for single page mode
        $folder_parent = Path::D($folder);
        $path_parent = Path::D($path);
        $name_parent = Path::B($folder_parent);
        if ($file_parent = File::exist([
            $folder_parent . '.page',
            $folder_parent . '.archive',
            $folder_parent . DS . $name_parent . '.page',
            $folder_parent . DS . $name_parent . '.archive'
        ])) {
            $page_parent = new Page($file_parent);
            $sort_parent = $page_parent->sort($site->sort);
            $chunk_parent = $page_parent->chunk($site->chunk);
            $files_parent = Get::pages($folder_parent, 'page', $sort_parent, 'slug');
            // Inherit parent’s `sort` and `chunk` property where possible
            $sort = $page_parent->sort($sort);
            $chunk = $page_parent->chunk($chunk);
        } else {
            $files_parent = [];
        }
        Lot::set([
            'pager' => new Elevator($files_parent, null, $page->slug, $url . '/' . $path_parent, $elevator, $site->is),
            'page' => $page
        ]);
        Config::set('page.title', new Anemon([$page->title, $site->title], ' &#x00B7; '));
        $x = '.' . $page->state;
        if (!File::exist([
            $folder . DS . $name . $x,
            $folder . DS . $h . DS . $h . $x
        ])) {
            if ($step !== null && File::exist($folder . DS . $step . $x)) {
                // Has page with numeric file name!
            } else if ($files = Get::pages($folder, 'page', $sort, 'path')) {
                if ($query = l(Request::get($config->q, ""))) {
                    Config::set('page.title', new Anemon([$language->search . ': ' . $query, $page->title, $site->title], ' &#x00B7; '));
                    $query = explode(' ', $query);
                    Config::set('search', new Page(null, ['query' => $query], ['*', 'search']));
                    $files = array_filter($files, function($v) use($query) {
                        $v = Path::N($v);
                        foreach ($query as $q) {
                            if (strpos($v, $q) !== false) {
                                return true;
                            }
                        }
                        return false;
                    });
                }
                foreach (Anemon::eat($files)->chunk($chunk, $i) as $file) {
                    $pages[] = new Page($file);
                }
                if (empty($pages)) {
                    // Greater than the maximum step or less than `1`, abort!
                    $site->is = '404';
                    Shield::abort('404/' . $path_f);
                }
                if ($path !== "") {
                    $site->is = 'pages';
                }
                Lot::set([
                    'pages' => $pages,
                    'pager' => new Elevator($files, $chunk, $i, $url . '/' . $path_f, $elevator, $site->is)
                ]);
                Shield::attach('pages/' . $path_f);
            // Redirect to parent page if user tries to access the placeholder page…
            } else if ($name === $name_parent && File::exist($folder . $x)) {
                Message::info('kick', '<code>' . $url->current . '</code>');
                Guardian::kick($path_parent);
            }
        }
        Shield::attach('page/' . $path_f);
    }
}, 20);