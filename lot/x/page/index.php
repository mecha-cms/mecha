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
    $path = $url['path'] ?? "";
    $i = $url['i'] ?? "";
    $p = \trim($state->path ?? "", '/');
    $folder = \LOT . \D . 'page' . \D . $path;
    // Set proper `i` value in `$url` if we have some page with numeric file/folder name
    if ("" !== $i && \exist([
        $folder . \D . $i . '.archive',
        $folder . \D . $i . '.page'
    ], 1)) {
        $path .= '/' . $i;
        $folder .= \D . $i;
        $url->i = null;
        $url->path = '/' . $path;
        $i = "";
    }
    $if_0 = "" === $i && ("" === $path || $path === $p);
    $if_1 = \exist([
        // `.\lot\page\home-name.{archive,page}`
        \LOT . \D . 'page' . \D . $p . '.archive',
        \LOT . \D . 'page' . \D . $p . '.page'
    ], 1);
    $if_2 = \exist([
        // `.\lot\page\page-name\.{archive,page}`
        $folder . \D . '.archive',
        $folder . \D . '.page'
    ], 1);
    $if_3 = \exist([
        // `.\lot\page\page-name.{archive,page}`
        $folder . '.archive',
        $folder . '.page'
    ], 1);
    $if_4 = \glob(\LOT . \D . 'page' . \D . $p . \D . '*.page', \GLOB_NOSORT);
    $if_5 = \glob($folder . \D . '*.page', \GLOB_NOSORT);
    $folder = \dirname($folder);
    $if_6 = \exist([
        // `.\lot\page\parent-name.{archive,page}`
        $folder . '.archive',
        $folder . '.page',
        $folder . \D . '.archive',
        $folder . \D . '.page'
    ], 1);
    \State::set('is', [
        'error' => $is_error = "" === $path && !$if_1 || "" !== $path && !$if_3 ? 404 : false,
        'home' => $is_home = $if_0,
        'page' => $is_page = "" === $path && $if_1 || "" !== $path && ($if_2 || $if_3 && !$if_5),
        'pages' => $is_pages = "" !== $i || "" === $path && $if_4 || "" !== $path && !$if_2 && $if_5
    ]);
    $count = \count("" === $path ? $if_4 : $if_5);
    \State::set('has', [
        'next' => $is_pages && ($count > (($i ?: 1) * ($state->chunk ?? 5))),
        'page' => "" === $path && $if_1 || $if_3,
        'pages' => $count > 0,
        'parent' => false !== \strpos($path, '/'), // `foo/bar`
        'prev' => $is_pages && $i > 1,
        'i' => $is_pages && "" !== $i
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
    $GLOBALS['parent'] = new \Page;
    function route($route, $index, $query, $hash) {
        extract($GLOBALS, \EXTR_SKIP);
        $i = ($index ?? 1) - 1;
        $path = '/' . $route;
        if ($i < 1 && $path === $state->path && !$query) {
            \kick('/'); // Redirect to home page
        }
        // Default home page path
        $p = '/' === $path ? $state->path : $path;
        $folder = \rtrim(\LOT . \D . 'page' . \strtr($p, '/', \D), \D);
        if ($file = \exist([
            $folder . '.archive',
            $folder . '.page'
        ], 1)) {
            $page = new \Page($file);
            $pager = new \Pager\Page([], null, (object) [
                'link' => $route ? $url . "" : null
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
            if (0 === $pages->count() || \is_file($folder . \D . '.' . $page->x)) {
                \Hook::fire('layout', ['page' . $p . '/' . ($i + 1)]);
            }
            // Create pager for “pages” mode
            $pager = new \Pager\Pages($pages->get(), [$chunk, $i], (object) [
                'link' => false !== \strpos($route, '/') ? \dirname($url . $p) : $url . $p
            ]);
            // Disable parent link in root page
            if (!$route || false === \strpos($route, '/') && !$index) {
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
                \Hook::fire('layout', ['pages' . $p . '/' . ($i + 1)]);
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
        \Hook::fire('layout', ['404' . $p . '/' . ($i + 1)]);
    }
    \Hook::set('route.page', __NAMESPACE__ . "\\route", 10);
    \Hook::set('route', function(...$lot) {
        \Hook::fire('route.page', $lot);
    }, 20);
}