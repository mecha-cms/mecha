<?php

return [
    'abbreviationData' => [
        'API' => 'Application Programming Interface',
        // Must comes before “AJAX” because it contains “XML” in the value
        'XML' => 'eXtensible Markup Language',
        'AJAX' => 'Asynchronous JavaScript and XML',
        'ASCII' => 'American Standard Code for Information Interchange',
        'CMS' => 'Content Management System',
        'CSS' => 'Cascading Style Sheet',
        'CPU' => 'Central Proccessing Unit',
        'FTP' => 'File Transfer Protocol',
        'HTML5' => 'Hyper Text Markup Language Version 5',
        'HTML' => 'Hyper Text Markup Language',
        'HTTP' => 'Hyper Text Transfer Protocol',
        'IE' => 'Internet Explorer',
        'IP' => 'Internet Protocol',
        'JPEG' => 'Joint Photographic Experts Group',
        'JS' => 'JavaScript',
        'JPG' => 'Joint Photographic Experts Group',
        'JSON' => 'JavaScript Object Notation',
        'RSS' => 'Rich Site Summary',
        'RTE' => 'Rich Text Editor',
        'SFTP' => 'SSH File Transfer Protocol',
        'SGML' => 'Standard Generalized Markup Language',
        'UA' => 'User Agent',
        'UI' => 'User Interface',
        'URL' => 'Uniform Resource Locator',
        'WYSIWYG' => 'What You See is What You Get',
        'YAML' => 'YAML Ain’t Markup Language'
    ],
    'figuresEnabled' => true,
    'figureAttributes' => ['class' => 'figure'],
    'footnoteAttributes' => ['class' => 'notes p'],
    'footnoteLinkAttributes' => function($number, $attributes, &$element, $name) {
        return [
            'class' => 'from',
            'href' => '#to:' . $name
        ];
    },
    'footnoteReferenceAttributes' => function($number, $attributes, &$element, $name, $index) {
        return ['id' => 'from:' . $name . '.' . $index];
    },
    'footnoteBackLinkAttributes' => function($number, $attributes, &$element, $name, $index) {
        return [
            'class' => 'to',
            'href' => '#from:' . $name . '.' . $index
        ];
    },
    'footnoteBackReferenceAttributes' => function($number, $attributes, &$element, $name, $total) {
        return [
            'class' => 'note',
            'id' => 'to:' . $name
        ];
    },
    'linkAttributes' => function($html, $attributes, &$element, $internal) {
        return $internal ? [] : [
            'rel' => 'nofollow',
            'target' => '_blank'
        ];
    },
    'tableAttributes' => ['class' => 'table'],
    'tableColumnAttributes' => function($html, $attributes, &$element, $align) {
        return [
            'class' => $align ? 'text-' . $align : null,
            'style' => null // Remove inline style(s)
        ];
    },
    'voidElementSuffix' => '>', // HTML5
];
