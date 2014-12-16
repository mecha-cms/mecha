<?php

return array(

    // General
    'timezone' => 'Asia/Jakarta',
    'charset' => 'utf-8',
    'language' => 'en_US',
    'language_direction' => 'ltr',
    'shield' => 'normal',
    'per_page' => 7,
    'comments' => true,
    'comment_moderation' => false,
    'email_notification' => true,
    'resource_versioning' => true,
    'excerpt_length' => 300,
    'excerpt_tail' => '&hellip;',
    'widget_year_first' => true,
    'html_minifier' => true,
    'html_parser' => HTML_PARSER,

    // Header
    'title' => 'My Awesome Site',
    'title_separator' => ' &ndash; ',
    'slogan' => 'Site slogan goes here.',
    'description' => 'Site description goes here.',
    'keywords' => 'blog, diary, notes, personal',
    'spam_keywords' => "",

    // Authorization
    'author' => "",
    'author_profile_url' => "",
    'author_email' => "",

    // Index Page
    'index' => array(
        'title' => 'Article',
        'slug' => 'article',
        'per_page' => 7
    ),

    // Tag Page
    'tag' => array(
        'title' => 'Tagged',
        'slug' => 'tagged',
        'per_page' => 7
    ),

    // Archive Page
    'archive' => array(
        'title' => 'Archive',
        'slug' => 'archive',
        'per_page' => 7
    ),

    // Search Page
    'search' => array(
        'title' => 'Search Results for',
        'slug' => 'search',
        'per_page' => 7
    ),

    // Manager Page
    'manager' => array(
        'title' => 'Manager',
        'slug' => 'manager',
        'per_page' => 7
    ),

    // Defaults
    'defaults' => array(
        'article_title' => "",
        'article_content' => "",
        'article_custom_css' => "<style media=\"screen\">\n\n</style>",
        'article_custom_js' => "<script>\n\n</script>",
        'page_title' => "",
        'page_content' => "",
        'page_custom_css' => "<style media=\"screen\">\n\n</style>",
        'page_custom_js' => "<script>\n\n</script>"
    )

);