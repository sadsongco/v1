<?php

function return_bytes($val)
{
    preg_match('/(?<value>\d+)(?<option>.?)/i', trim($val), $matches);
    $inc = array(
        'g' => 1073741824, // (1024 * 1024 * 1024)
        'm' => 1048576, // (1024 * 1024)
        'k' => 1024
    );
    
    $value = (int) $matches['value'];
    $key = strtolower(trim($matches['option']));
    if (isset($inc[$key])) {
        $value *= $inc[$key];
    }

    return $value;
}