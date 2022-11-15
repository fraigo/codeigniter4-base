<?php

/**
 * @param string $groupKey
 * @param array $data
 * @return array 
 */
function mapArrayByKey($groupKey, $data){
    $result = [];
    foreach($data as $item){
        $result[$item[$groupKey]] = $item;
    }
    return $result;
}

/**
 * @param string $groupKey
 * @param string $valueKey
 * @param array $data
 * @return array
 */
function mapValuesByKey($groupKey, $valueKey, $data){
    $result = [];
    foreach($data as $item){
        $result[$item[$groupKey]] = $item[$valueKey];
    }
    return $result;
}

/**
 * @param string $groupKey
 * @param array $data
 * @param string $mapKey
 * @param string $valueKey
 */
function groupArrayByKey($groupKey, $data, $mapKey=null, $valueKey=null){
    $result = [];
    foreach($data as $item){
        $id = $item[$groupKey];
        if (!@$result[$id]){
            $result[$id] = [];
        }
        $result[$id][] = $item;
    }
    if ($mapKey!=null){
        foreach($result as $key=>$items){
            $result[$key] = mapValuesByKey($mapKey, $valueKey, $items);    
        }
    }
    return $result;
}