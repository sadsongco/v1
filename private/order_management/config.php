<?php

class SHIPPING_METHODS_MAP {
    const FIRST = [
        "postage_method" => "First Class (1 - 2 days)",
        "weight_min" => 0,
        "weight_max" => 9999,
        "rm_code" => "OLP1"
    ];

    const SECOND = [
        "postage_method" => "Second Class",
        "weight_min" => 0,
        "weight_max" => 9999,
        "rm_code" => "OLP2"
    ];

    const EUROPE_SMALL = [
        "postage_method" => "Europe",
        "weight_min" => 0,
        "weight_max" => 249,
        "rm_code" => "IEOLP",
    ];

    const EUROPE_LARGE = [
        "postage_method" => "Europe",
        "weight_min" => 250,    
        "weight_max" => 9999,
        "rm_code" => "ISIOLP",
    ];

    const REST_OF_WORLD_SMALL = [
        "postage_method" => "Rest of World",
        "weight_min" => 0,
        "weight_max" => 249,
        "rm_code" => "IEOLP",
    ];

    const REST_OF_WORLD_LARGE = [
        "postage_method" => "Rest of World",
        "weight_min" => 250,    
        "weight_max" => 9999,
        "rm_code" => "ISIOLP",
    ];
}
