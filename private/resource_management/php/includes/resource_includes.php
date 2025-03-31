<?php

include_once(base_path("php/includes/p_2.php"));

require base_path('lib/mustache.php-main/src/Mustache/Autoloader.php');
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader('../templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader('../templates/partials')
));

function base_path($path) {
    return __DIR__.'/../../../../'.$path;
}

$txt_map = [
    "soundcloud_playlists" => [
        "name",
        "id",
        "secret"
    ],
    "youtube_videos" => [
        "name",
        "id",
        "secret",
        "url"
    ],
    "press_shots" => [
        "credit"
    ]
];

$update_map = [
    "soundcloud_playlists" => [
        "name",
        "id",
        "secret"
    ],
    "youtube_videos" => [
        "name",
        "id",
        "secret",
        "url"
    ],
    "press_shots" => [
        "file",
        "credit"
    ]
];

$resize_resources = [
    "artwork",
    "press_shots"
];

$parent_dir = base_path("resources/resource_dirs/");

define("MAX_IMAGE_WIDTH", 900);
define("IMAGE_THUMBNAIL_WIDTH", 200);