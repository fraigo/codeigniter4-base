<?php

function mapArrayByKey($key, $data){
    $result = [];
    foreach($data as $item){
        $result[$item[$key]] = $item;
    }
    return $result;
}