<?php

return [
    // Resolve link in element’s content
    'content' => [
        'script' => "\\x\\link\\content\\script",
        'style' => "\\x\\link\\content\\style"
    ],
    // Resolve link in element’s attribute
    'data' => [
        'a' => [
            'href' => 1,
            'ping' => 1
        ],
        'area' => [
            'href' => 1,
            'ping' => 1
        ],
        'audio' => ['src' => 1],
        'base' => ['href' => 1],
        'button' => ['formaction' => 1],
        'embed' => ['src' => 1],
        'form' => ['action' => 1],
        'iframe' => ['src' => 1],
        'img' => [
            'src' => 1,
            'srcset' => "\\x\\link\\data\\img\\srcset"
        ],
        'input' => [
            'formaction' => 1,
            'src' => 1 // `<input type="image">`
        ],
        'link' => [
            'href' => 1,
            'imagesrcset' => "\\x\\link\\data\\img\\srcset"
        ],
        'object' => ['data' => 1],
        'param' => ['value' => 1],
        'picture' => ['srcset' => "\\x\\link\\data\\img\\srcset"],
        'script' => ['src' => 1],
        'source' => [
            'src' => 1,
            'srcset' => "\\x\\link\\data\\img\\srcset"
        ],
        'track' => ['src' => 1],
        'use' => [ // `<svg>`
            'href' => "\\x\\link\\data\\svg\\href",
            'xlink:href' => "\\x\\link\\data\\svg\\href"
        ],
        'video' => [
            'src' => 1,
            'poster' => 1
        ]
    ]
];