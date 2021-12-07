<?php

namespace {
    \From::_('page', function(string $value, $eval = false) {
        if (0 !== \strpos($value = \n($value), \YAML\SOH . "\n")) {
            // Add empty header
            $value = \YAML\SOH . "\n" . \YAML\EOT . "\n\n" . $value;
        }
        $v = static::YAML($value, '  ', true, $eval);
        return $v[0] + ['content' => $v["\t"] ?? null];
    });
    \To::_('page', function(array $value) {
        $content = $value['content'] ?? null;
        unset($value['content']);
        $value = [
            0 => $value,
            "\t" => $content
        ];
        return static::YAML($value, '  ', true);
    });
    // Define page’s condition data as early as possible, so that other
    // extension(s) can use it without having to enter the `route` hook
    $path = \trim($url->path ?? "", '/');
    $route = \trim($state->route ?? "", '/');
    $folder = \LOT . \D . 'page' . \D . ($path ?: $route);
    $parent = \dirname($folder);
    $has_pages = \q(\g($folder, 'page'));
    $has_parent = \exist([
        $parent . '.archive',
        $parent . '.page',
        $parent . \D . '.archive',
        $parent . \D . '.page'
    ], 1);
    $is_home = "" === $path || $route === $path ? \exist([
        $folder . '.archive',
        $folder . '.page'
    ], 1) : false;
    $is_page = \exist([
        $folder . '.archive',
        $folder . '.page'
    ], 1);
    // Check if `pages` mode is disabled by a file like `.\lot\page\about\.page`
    $not_pages = \exist([
        $folder . \D . '.archive',
        $folder . \D . '.page'
    ], 1);
    \State::set('is', [
        'error' => $is_error = ("" === $path && !$is_home || "" !== $path && !$is_page) ? 404 : false,
        'home' => !!$is_home,
        'page' => $is_home || ($is_page && ($not_pages || !$has_pages)),
        'pages' => $has_pages && !$not_pages
    ]);
    \State::set('has', [
        'page' => $is_home || $is_page,
        'pages' => !!$has_pages,
        'parent' => $has_parent && false !== \strpos($path, '/')
    ]);
    \State::set([
        'are' => [],
        'can' => [],
        'not' => []
    ]);
}

namespace x\page {
    // Initialize layout variable(s)
    $GLOBALS['page'] = new \Page;
    $GLOBALS['pager'] = new \Pager\Pages;
    $GLOBALS['pages'] = new \Pages;
    function route($path, $query, $hash) {
        extract($GLOBALS, \EXTR_SKIP);
        $route = \trim($state->route ?? "", '/');
        $folder = \LOT . \D . 'page' . \D . \strtr($path ?: $route, '/', \D);
        if ($path && \preg_match('/^(.*?)\/([1-9]\d*)$/', $path, $m)) {
            [$any, $path, $i] = $m;
            if (\exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                $path .= '/' . $i;
                unset($i);
            } else {
                $folder = \dirname($folder);
            }
        }
        $i = ((int) ($i ?? 1)) - 1;
        if ($i < 1 && $route === $path && !$query) {
            \kick('/'); // Redirect to home page
        }
        if ($file = \exist([
            $folder . '.archive',
            $folder . '.page'
        ], 1)) {
            $page = new \Page($file);
            $pager = new \Pager\Page([], null, (object) [
                'link' => $path ? $url . "" : null
            ]);
            $chunk = $page['chunk'] ?? 5;
            $deep = $page['deep'] ?? 0;
            $sort = $page['sort'] ?? [1, 'path'];
            $parent_path = \dirname($path);
            $parent_folder = \dirname($folder);
            $GLOBALS['page'] = $page;
            $GLOBALS['pager'] = $pager;
            $GLOBALS['t'][] = $page->title;
            \State::set([
                'chunk' => $chunk, // Inherit current page’s `chunk` property
                'deep' => $deep, // Inherit current page’s `deep` property
                'sort' => $sort // Inherit current page’s `sort` property
            ]);
            if ($parent_file = \exist([
                $parent_folder . '.archive', // `.\lot\page\parent-name.archive`
                $parent_folder . '.page', // `.\lot\page\parent-name.page`
                $parent_folder . \D . '.archive', // `.\lot\page\parent-name\.archive`
                $parent_folder . \D . '.page' // `.\lot\page\parent-name\.page`
            ], 1)) {
                $parent_page = new \Page($parent_file);
                $parent_deep = $parent_page['deep'] ?? 0;
                $parent_sort = $parent_page['sort'] ?? [1, 'path'];
                $parent_pages = \Pages::from($parent_folder, 'page', $parent_deep)->sort($parent_sort);
                $pager = new \Pager\Page($parent_pages->get(), $page->path, $parent_page);
                $GLOBALS['pager'] = $pager;
                $GLOBALS['pages'] = $parent_pages;
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
            if (0 === $pages->count() || \is_file($folder . \D . '.' . $page->x)) {
                \Hook::fire('layout', ['page/' . ($path ?: $route) . '/' . ($i + 1)]);
            }
            // Create pager for “pages” mode
            $pager = new \Pager\Pages($pages->get(), [$chunk, $i], (object) [
                'link' => $url . '/' . $path
            ]);
            // Disable parent link in root page
            if (!$path || false === \strpos($path, '/') && $i < 1) {
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
                \Hook::fire('layout', ['pages/' . ($path ?: $route) . '/' . ($i + 1)]);
            }
        }
        \State::set([
            'has' => [
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
        \Hook::fire('layout', ['404/' . ($path ?: $route) . '/' . ($i + 1)]);
    }
    \Hook::set('route.page', __NAMESPACE__ . "\\route", 20);
    \Hook::set('route', function(...$lot) {
        \Hook::fire('route.page', $lot);
    }, 20);
}