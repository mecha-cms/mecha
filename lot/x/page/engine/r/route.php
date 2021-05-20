<?php namespace x\page;

// Initialize layout variable(s)
$GLOBALS['page'] = new \Page;
$GLOBALS['pager'] = new \Pager\Pages;
$GLOBALS['pages'] = new \Pages;
$GLOBALS['parent'] = new \Page;

function route($any = "") {
    global $state, $url;
    $i = ($url['i'] ?? 1) - 1;
    // Prevent directory traversal attack <https://en.wikipedia.org/wiki/Directory_traversal_attack>
    $path = '/' . \str_replace('../', "", $any);
    if ($i < 1 && $path === $state->path && !$url->query) {
        \Guard::kick(""); // Redirect to home page
    }
    // Default home page path
    $p = '/' === $path ? $state->path : $path;
    $folder = \rtrim(\LOT . \DS . 'page' . \strtr($p, '/', \DS), \DS);
    if ($file = \File::exist([
        $folder . '.page',
        $folder . '.archive'
    ])) {
        $page = new \Page($file);
        $pager = new \Pager\Page([], null, (object) [
            'link' => $any ? $url . "" : null
        ]);
        $chunk = $page['chunk'] ?? 5;
        $deep = $page['deep'] ?? 0;
        $sort = $page['sort'] ?? [1, 'path'];
        $parent_path = \Path::D($path);
        $parent_folder = \Path::D($folder);
        $GLOBALS['page'] = $page;
        $GLOBALS['pager'] = $pager;
        $GLOBALS['t'][] = $page->title;
        \State::set([
            'chunk' => $chunk, // Inherit current page’s `chunk` property
            'deep' => $deep, // Inherit current page’s `deep` property
            'sort' => $sort // Inherit current page’s `sort` property
        ]);
        if ($parent_file = \File::exist([
            $parent_folder . '.page', // `.\lot\page\parent-name.page`
            $parent_folder . '.archive', // `.\lot\page\parent-name.archive`
            $parent_folder . \DS . '.page', // `.\lot\page\parent-name\.page`
            $parent_folder . \DS . '.archive' // `.\lot\page\parent-name\.archive`
        ])) {
            $parent_page = new \Page($parent_file);
            $parent_deep = $parent_page['deep'] ?? 0;
            $parent_sort = $parent_page['sort'] ?? [1, 'path'];
            $parent_pages = \Pages::from($parent_folder, 'page', $parent_deep)->sort($parent_sort);
            $pager = new \Pager\Page($parent_pages->get(), $page->path, $parent_page);
            $GLOBALS['pager'] = $pager;
            $GLOBALS['pages'] = $parent_pages;
            $GLOBALS['parent'] = $parent_page;
            \State::set([
                'has' => [
                    'next' => !!$pager->next,
                    'page' => true,
                    'pages' => false,
                    'parent' => !!$pager->parent,
                    'prev' => !!$pager->prev
                ],
                'is' => [
                    'page' => true,
                    'pages' => false
                ]
            ]);
        }
        $pages = \Pages::from($folder, 'page', $deep)->sort($sort); // (all)
        // No page(s) means “page” mode
        if (0 === $pages->count() || \is_file($folder . \DS . '.' . $page->x)) {
            $this->layout('page' . $p . '/' . ($i + 1));
        }
        // Create pager for “pages” mode
        $pager = new \Pager\Pages($pages->get(), [$chunk, $i], (object) [
            'link' => false !== \strpos($any, '/') ? \dirname($url . $p) : $url . $p
        ]);
        // Disable parent link in root page
        if (!$any || false === \strpos($any, '/') && !$url['i']) {
            $pager->parent = null;
        }
        $pages = $pages->chunk($chunk, $i); // (chunked)
        if ($pages->count() > 0) {
            \State::set([
                'has' => [
                    'next' => !!$pager->next,
                    'page' => true,
                    'pages' => true,
                    'parent' => !!$pager->parent,
                    'prev' => !!$pager->prev
                ],
                'is' => [
                    'page' => false,
                    'pages' => true
                ]
            ]);
            $GLOBALS['page'] = $page;
            $GLOBALS['pager'] = $pager;
            $GLOBALS['pages'] = $pages;
            $this->layout('pages' . $p . '/' . ($i + 1));
        }
    }
    \State::set([
        'has' => [
            'i' => false,
            'next' => false,
            'page' => false,
            'pages' => false,
            'parent' => false,
            'prev' => false
        ],
        'is' => [
            'error' => 404,
            'page' => true,
            'pages' => false
        ]
    ]);
    $GLOBALS['t'][] = i('Error');
    $this->layout('404' . $p . '/' . ($i + 1));
}

\Route::set(['*', ""], __NAMESPACE__ . "\\route", 20);
