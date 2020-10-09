<?php

/**
 * gets the first key of an array (PHP 7 >= 7.3.0).
 *
 * @return mixed
 */
function array_key_first(array $arr)
{
    foreach ($arr as $key => $unused) {
        return $key;
    }

    return null;
}
