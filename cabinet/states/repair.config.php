<?php

return array(

    // General
    'timezone' => 'Asia/Jakarta',
    'charset' => 'UTF-8',
    'language' => 'en_US',
    'language_direction' => 'LTR',
    'shield' => 'normal',
    'per_page' => 7,
    'comments' => true,
    'email_notification' => true,
    'resource_versioning' => true,
    'excerpt_length' => 300,
    'excerpt_tail' => '&hellip;',
    'widget_year_first' => true,

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
    'tag' => array(        'title' => 'Tagged',        'slug' => 'tagged',
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
        'title' => 'Searh Results for',
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
        'page_title' => "",
        'page_content' => "",
        'page_custom_css' => "",
        'page_custom_js' => ""
    )
);