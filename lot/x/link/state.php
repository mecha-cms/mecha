<?php

return [
    'alter' => [
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
            'srcset' => "\\x\\link\\f\\source_set"
        ],
        'input' => [
            'formaction' => 1,
            'src' => 1 // `<input type="image">`
        ],
        'link' => [
            'href' => 1,
            'imagesrcset' => "\\x\\link\\f\\image_source_set"
        ],
        'object' => ['data' => 1],
        'param' => ['value' => 1],
        'picture' => ['srcset' => "\\x\\link\\f\\source_set"],
        'script' => ['src' => 1],
        'source' => [
            'src' => 1,
            'srcset' => "\\x\\link\\f\\source_set"
        ],
        'track' => ['src' => 1],
        'video' => [
            'src' => 1,
            'poster' => 1
        ]
    ]
];