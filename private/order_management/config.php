<?php

define('SHIPPING_METHODS_MAP', [
    'FIRST_SMALL' => [
        "postage_method" => "First Class (1 - 2 days)",
        "weight_min" => 0,
        "weight_max" => 9999,
        "rm_code" => "TOLP24"
    ],

    'SECOND' => [
        "postage_method" => "Second Class",
        "weight_min" => 0,
        "weight_max" => 9999,
        "rm_code" => "TOLP48"
    ],

    'EUROPE_SMALL' => [
        "postage_method" => "Europe",
        "weight_min" => 0,
        "weight_max" => 249,
        "rm_code" => "IEOLP",
    ],

    'EUROPE_LARGE' => [
        "postage_method" => "Europe",
        "weight_min" => 250,    
        "weight_max" => 9999,
        "rm_code" => "ITROLP",
    ],

    'REST_OF_WORLD_SMALL' => [
        "postage_method" => "Rest Of World",
        "weight_min" => 0,
        "weight_max" => 249,
        "rm_code" => "IEOLP",
    ],

    'REST_OF_WORLD_LARGE' => [
        "postage_method" => "Rest Of World",
        "weight_min" => 250,    
        "weight_max" => 9999,
        "rm_code" => "ITROLP",
    ]
]);

define('PACKAGE_FORMATS', [
    "LARGE_LETTER" => [
        "name" => "large letter",
        "weight_min" => 0,
        "weight_max" => 249,
    ],
    "SMALL_PARCEL" => [
        "name" => "small parcel",
        "weight_min" => 250,
        "weight_max" => 1999,
    ],
    "MEDIUM_PARCEL" => [
        "name" => "medium parcel",
        "weight_min" => 2000,
        "weight_max" => 9999,
    ]
]);