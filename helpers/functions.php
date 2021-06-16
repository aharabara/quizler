<?php

function shuffle_assoc(array $list): array
{
    $keys = array_keys($list);
    shuffle($keys);
    $random = [];
    foreach ($keys as $key)
        $random[$key] = $list[$key];
    return $random;
}
