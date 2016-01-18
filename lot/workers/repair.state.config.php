<?php

return array(

    // General
    'timezone' => 'Asia/Jakarta',
    'charset' => 'utf-8',
    'language' => 'en_US',
    'language_direction' => 'ltr',
    'shield' => 'normal',
    'per_page' => 7,
    'comments' => array(
        'allow' => true,
        'moderation' => false
    ),
    'excerpt' => array(
        'length' => 300,
        'prefix' => "",
        'suffix' => '&hellip;',
        'id' => 'more:%d'
    ),
    'html_parser' => array(
        'type' => array('HTML' => 'HTML'),
        'active' => 'HTML'
    ),
    'widget_include_css' => true,
    'widget_include_js' => true,

    // Header
    'title' => 'My Awesome Site',
    'title_separator' => ' &ndash; ',
    'slogan' => 'Site slogan goes here.',
    'description' => 'Site description goes here.',
    'keywords' => 'blog, diary, notes, personal',
    'keywords_spam' => "",

    // Authorization
    'author' => array(
        'name' => Guardian::get('author'),
        'email' => Guardian::get('email'),
        'url' => ""
    ),

    // Index Page
    'index' => array(
        'title' => 'Article',
        'slug' => 'article',
        'per_page' => 7
    ),

    // Tag Page
    'tag' => array(
        'title' => 'Tagged in %s',
        'slug' => 'tag',
        'per_page' => 7
    ),

    // Archive Page
    'archive' => array(
        'title' => 'Archive %s',
        'slug' => 'archive',
        'per_page' => 7
    ),

    // Search Page
    'search' => array(
        'title' => 'Search for &ldquo;%s&rdquo;',
        'slug' => 'search',
        'per_page' => 7
    ),

    // Manager Page
    'manager' => array(
        'title' => "",
        'slug' => 'manager',
        'per_page' => 7
    ),

    // Default(s)
    'defaults' => array(
        'article_title' => "",
        'article_content' => "",
        'article_css' => "<style media=\"screen\">\n\n</style>",
        'article_js' => "<script>\n\n</script>",
        'page_title' => "",
        'page_content' => "",
        'page_css' => "<style media=\"screen\">\n\n</style>",
        'page_js' => "<script>\n\n</script>"
    )

);