<?php

require_once("../../../secure/scripts/ut_o_connect.php");
require '../../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader('templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader('templates/partials')
));

function p_2($input) {
    echo "<pre>"; print_r($input); echo "</pre>";
}

include("get-resource.php");

$resource_sections = [];
$handle = opendir(__DIR__ . "/../resource_dirs");
while ($sub_dir = readdir($handle)) {
    if (substr($sub_dir, 0, 1) == ".") continue;
    $resource_sections[] = $sub_dir;
}

closedir($handle);

$sections = [];
$resources = [];
foreach ($resource_sections AS $resource_section) {
    $sections[] = ["section_id"=>$resource_section, "disp_name"=>ucwords(str_replace("_", " ", $resource_section))];
    $resources[] = getResource($resource_section);
}
// p_2($resources);
echo $m->render('resourcePage',["sections"=>$sections, "resources"=>$resources]);
// echo $m->render('resourceSection', $resources);