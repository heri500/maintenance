<?php

/**
 * @param null $tableName
 * @param null $userAccess
 * @param null $fieldArray
 * @param null $fieldOrder
 * @param null $fieldCondition
 * @param null $fieldConditionValue
 * @param null $leftJoinArray
 * @param null $conditionArray
 * @param null $range
 *
 * @return $this|null|\SelectQuery|\SelectQueryInterface
 */
function get_table_data_by_field($tableName = null, $userAccess = null, $fieldArray = null, $fieldOrder = null, $fieldCondition = null, $fieldConditionValue = null, $leftJoinArray = null, $conditionArray = null, $range = null){
    $query = null;
    if (!is_null($tableName) && !is_null($fieldArray) && !is_null($userAccess)){
        if (user_access($userAccess)) {
            $strField = implode(',', $fieldArray);
            $query = db_select($tableName,'ta');
            if ($leftJoinArray !== null && count($leftJoinArray)){
                for ($i = 0;$i < count($leftJoinArray);$i++){
                    $leftJoinData = $leftJoinArray[$i];
                    if (isset($leftJoinData['sourcealias'])){
                        $query->leftJoin($leftJoinData['tablename'], $leftJoinData['tablealias'], $leftJoinData['sourcealias'].'.'.$leftJoinData['field_join'].' = '.$leftJoinData['tablealias'].'.'.$leftJoinData['field_source']);
                    }else{
                        $query->leftJoin($leftJoinData['tablename'], $leftJoinData['tablealias'], 'ta.'.$leftJoinData['field_join'].' = '.$leftJoinData['tablealias'].'.'.$leftJoinData['field_source']);
                    }
                }
            }
            $query = $query->fields('ta', $fieldArray);
            if (!is_null($leftJoinArray) && count($leftJoinArray)){
                for ($i = 0;$i < count($leftJoinArray);$i++){
                    $leftJoinData = $leftJoinArray[$i];
                    $query = $query->fields($leftJoinData['tablealias'], $leftJoinData['fieldArray']);
                }
            }
            if (!is_null($fieldOrder)){
                if (!is_array($fieldOrder)){
                    $explodeFieldOrder = explode('.', $fieldOrder);
                    if (count($explodeFieldOrder)){
                        $query = $query->orderBy($fieldOrder);
                    }else{
                        $query = $query->orderBy('ta.'.$fieldOrder);
                    }
                }else{
                    for ($i = 0;$i < count($fieldOrder);$i++){
                        if (isset($fieldOrder[$i]['operator'])){
                            $explodeFieldOrder = explode('.', $fieldOrder[$i]['fieldname']);
                            if (count($explodeFieldOrder)){
                                $query = $query->orderBy($fieldOrder[$i]['fieldname'], $fieldOrder[$i]['operator']);
                            }else{
                                $query = $query->orderBy('ta.'.$fieldOrder[$i]['fieldname'], $fieldOrder[$i]['operator']);
                            }
                        }else{
                            $explodeFieldOrder = explode('.', $fieldOrder[$i]['fieldname']);
                            if (count($explodeFieldOrder)){
                                $query = $query->orderBy($fieldOrder[$i]['fieldname']);
                            }else{
                                $query = $query->orderBy('ta.'.$fieldOrder[$i]['fieldname']);
                            }
                        }
                    }
                }
            }
            if (!is_null($fieldCondition) && !is_null($fieldConditionValue)){
                $query = $query->condition('ta.'.$fieldCondition, $fieldConditionValue);
            }
            if (!is_null($conditionArray) && count($conditionArray)){
                $db_or = db_or();
                $db_and = db_and();
                for ($i = 0;$i < count($conditionArray);$i++){
                    if (isset($conditionArray[$i]->connector)){
                        if ($conditionArray[$i]->connector == 'OR'){
                            $conditionField = $conditionArray[$i]->fieldName;
                            $conditionValue = $conditionArray[$i]->value;
                            if (isset($conditionArray[$i]->operator) && !empty($conditionArray[$i]->operator)){
                                $conditionOperator = $conditionArray[$i]->operator;
                            }else{
                                $conditionOperator = '=';
                            }
                            $db_or->condition($conditionField, $conditionValue,$conditionOperator);
                            $query = $query->condition($db_or);
                        }else{
                            $conditionField = $conditionArray[$i]->fieldName;
                            $conditionValue = $conditionArray[$i]->value;
                            if (isset($conditionArray[$i]->operator) && !empty($conditionArray[$i]->operator)){
                                $conditionOperator = $conditionArray[$i]->operator;
                            }else{
                                $conditionOperator = '=';
                            }
                            $db_and->condition($conditionField, $conditionValue,$conditionOperator);
                            $query = $query->condition($db_and);
                        }
                    }else{
                        $conditionField = $conditionArray[$i]->fieldName;
                        $conditionValue = $conditionArray[$i]->value;
                        if (isset($conditionArray[$i]->operator) && !empty($conditionArray[$i]->operator)){
                            $conditionOperator = $conditionArray[$i]->operator;
                        }else{
                            $conditionOperator = '=';
                        }
                        $db_and->condition($conditionField, $conditionValue,$conditionOperator);
                        $query = $query->condition($db_and);
                    }
                }
            }
            if (is_array($range) && !is_null($range)){
                $query = $query->range($range['min'], $range['max']);
            }
            $query = $query->execute()
                ->fetchAll();
        }
    }
    return $query;
}

/**
 * @param null      $tableName
 * @param null      $userAccess
 * @param null      $fieldDataArray
 * @param null      $fieldPrimary
 * @param null      $fieldPrimaryValue
 * @param bool|true $useCreatedChanged
 *
 * @return null
 * @throws \Exception
 */
function save_table_data($tableName = null, $userAccess = null, $fieldDataArray = null, $fieldPrimary = null, $fieldPrimaryValue = null, $useCreatedChanged = true){
    if (!is_null($tableName) && !is_null($fieldDataArray) && !is_null($userAccess)){
        if (is_null($fieldPrimary) || is_null($fieldPrimaryValue)){
            //new data insert
            if (!(is_null($fieldPrimary) && is_null($fieldPrimaryValue))){
                $fieldDataArray[$fieldPrimary] = getRandomString();
            }
            if ($useCreatedChanged && (!isset($fieldDataArray['created']) || !isset($fieldDataArray['changed']))){
                $fieldDataArray['created'] = time();
                $fieldDataArray['changed'] = time();
            }
            $query = db_insert($tableName)
                ->fields($fieldDataArray)
                ->execute();
        }else{
            //update data
            $query = db_update($tableName)
                ->fields($fieldDataArray)
                ->condition($fieldPrimary, $fieldPrimaryValue)
                ->execute();
            $fieldDataArray[$fieldPrimary] = $fieldPrimaryValue;
        }
    }
    return $fieldDataArray;
}

/**
 * @param null $tableName
 * @param null $userAccess
 * @param null $conditionArray
 *
 * @return bool|\DatabaseStatementInterface
 */
function delete_table_data($tableName = null, $userAccess = null, $conditionArray = null){
    $query = false;
    if (!is_null($tableName) && !is_null($userAccess) && !is_null($conditionArray)){
        $queryCondition = db_and();
        for ($i = 0;$i < count($conditionArray);$i++){
            $conditionData = $conditionArray[$i];
            $operatorCondition = '=';
            if (isset($conditionData->fieldOperator) && !is_null($conditionData->fieldOperator)){
                $operatorCondition = $conditionData->fieldOperator;
            }
            if ($conditionData->status == 'or'){
                $queryCondition = $queryCondition->condition(db_or()->condition($conditionData->fieldName, $conditionData->fieldValue, $operatorCondition));
            }else{
                $queryCondition = $queryCondition->condition(db_and()->condition($conditionData->fieldName, $conditionData->fieldValue, $operatorCondition));
            }
        }
        if (user_access($userAccess)){
            $query = db_delete($tableName)
                ->condition($queryCondition)
                ->execute();
        }
    }
    return $query;
}

/**
 * @param $strDateFrom
 * @param $strDateTo
 * @return array
 */
function create_date_range_array($strDateFrom, $strDateTo, $unixTime = false)
{
    // takes two dates formatted as YYYY-MM-DD and creates an
    // inclusive array of the dates between the from and to dates.

    // could test validity of dates here but I'm already doing
    // that in the main script

    $aryRange=array();

    $iDateFrom=mktime(0,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
    $iDateTo=mktime(0,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

    if ($iDateTo>=$iDateFrom)
    {
        if ($unixTime){
            array_push($aryRange, $iDateFrom);
        }else {
            array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry
        }
        while ($iDateFrom<$iDateTo)
        {
            $iDateFrom+=86400; // add 24 hours
            if ($unixTime){
                array_push($aryRange, $iDateFrom);
            }else {
                array_push($aryRange, date('Y-m-d', $iDateFrom));
            }
        }
    }
    return $aryRange;
}

/**
 * @return bool
 */
function include_date_picker_function(){
    drupal_add_library('system', 'ui.datepicker');
    drupal_add_js("(function ($) { $('.datepicker').datepicker({ dateFormat: 'yy-mm-dd' }); })(jQuery);", array('type' => 'inline', 'scope' => 'footer', 'weight' => 5));
    return true;
}

/**
 * @param $mon
 * @param $year
 * @return mixed
 */
function get_last_day($mon, $year) {
    $daysinmonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $days = $daysinmonth[$mon-1];
    if ($mon == 2 && ($year % 4) == 0 && (($year % 100) != 0 ||
            ($year % 400) == 0)) $days++;
    //if ($mon == 2 && ($year % 4) == 0 && ($year % 1000) != 0) $days++;
    $lastday = $days;
    return $lastday;
}

/**
 * @param int $length
 *
 * @return string
 */
function get_random_string($length=22)
{
    $key = '';
    $keys = array_merge(range(0, 9));
    for ($i = 0; $i < $length; $i++) {
        mt_srand((double)microtime() * 10000000);
        $key .= $keys[array_rand($keys)];
    }
    return $key;
}

/**
 * @param null $date1
 * @param null $date2
 * @return bool|DateInterval|int|null
 */
function date_different($date1 = null, $date2 = null)
{
    $interval = null;
    if (!empty($date1) && !empty($date2)) {
        if ($date1 != $date2) {
            $date1 = new DateTime($date1);
            $date2 = new DateTime($date2);
            $interval = $date1->diff($date2);
        } else {
            $interval = 0;
        }
    }
    return $interval;
}

function month_array($IdxMonth = null){
    $ArrMonth = array(
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
        'September', 'Oktober', 'November', 'Desember'
    );
    if ($IdxMonth !== null){
        return $ArrMonth[$IdxMonth];
    }else{
        return $ArrMonth;
    }
}