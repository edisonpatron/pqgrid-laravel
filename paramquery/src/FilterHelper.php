<?php


namespace SoulMinato\Paramquery;


class FilterHelper
{
    public static function _contain($dataIndx, &$fcrule, &$param, $value){
        $fcrule[] = $dataIndx . " like CONCAT('%', ?, '%')";
        $param[] = $value;
    }
    public static function _notcontain($dataIndx, &$fcrule, &$param, $value){
        $fcrule[] = $dataIndx . " not like CONCAT('%', ?, '%')";
        $param[] = $value;
    }
    public static function _begin($dataIndx, &$fcrule, &$param, $value){
        $fcrule[] = $dataIndx . " like CONCAT( ?, '%')";
        $param[] = $value;
    }
    public static function _notbegin($dataIndx, &$fcrule, &$param, $value){
        $fcrule[] = $dataIndx . " not like CONCAT( ?, '%')";
        $param[] = $value;
    }
    public static function _end($dataIndx, &$fcrule, &$param, $value){
        $fcrule[] = $dataIndx . " like CONCAT('%', ?)";
        $param[] = $value;
    }
    public static function _notend($dataIndx, &$fcrule, &$param, $value){
        $fcrule[] = $dataIndx . " not like CONCAT('%', ?)";
        $param[] = $value;
    }
    public static function _equal($dataIndx, &$fcrule, &$param, $value){
        $fcrule[] = $dataIndx . " = ?";
        $param[] = $value;
    }
    public static function _notequal($dataIndx, &$fcrule, &$param, $value){
        $fcrule[] = $dataIndx . " != ?";
        $param[] = $value;
    }
    public static function _empty($dataIndx, &$fcrule){
        $fcrule[] = "ifnull(" . $dataIndx . ",'')=''";
    }
    public static function _notempty($dataIndx, &$fcrule){
        $fcrule[] = "ifnull(" . $dataIndx . ",'')!=''";
    }
    public static function _less($dataIndx, &$fcrule, &$param, $value){
        $fcrule[] = $dataIndx . " < ?";
        $param[] = $value;
    }
    public static function _lte($dataIndx, &$fcrule, &$param, $value){
        $fcrule[] = $dataIndx . " <= ?";
        $param[] = $value;
    }
    public static function _great($dataIndx, &$fcrule, &$param, $value){
        $fcrule[] = $dataIndx . " > ?";
        $param[] = $value;
    }
    public static function _gte($dataIndx, &$fcrule, &$param, $value){
        $fcrule[] = $dataIndx . " >= ?";
        $param[] = $value;
    }
    public static function _between($dataIndx, &$fcrule, &$param, $value, $value2){
        $fcrule[] = "(" . $dataIndx . " >= ? and ".$dataIndx." <= ? )";
        $param[] = $value;
        $param[] = $value2;
    }
    public static function _range($dataIndx, &$fcrule, &$param, $value){
        $arrValue = $value;
        $fcRange = array();
        foreach ($value as $val){
            if ($val == ""){
                //continue;
                FilterHelper::_empty($dataIndx, $fcRange);
            }
            else{
                $fcRange[] = $dataIndx."= ?";
                $param[] = $val;
            }
        }
        $fcrule[] = (sizeof($fcRange)>0)? "(". join(" OR ", $fcRange) .")": "";
    }

    public static function deSerializeFilter($pq_filter, $columnQuery, $aliasColumns)
    {
        //$filterObj = json_decode($pq_filter);//when stringify is true;
        $filterObj = json_decode($pq_filter);
        $mode =  $filterObj->mode;
        $rules = $filterObj->data;

        $frule = array();
        $param = array();

        foreach ($rules as $rule){

            $dataIndx = $columnQuery[array_search($rule->dataIndx, $aliasColumns)];

            if (ColumnHelper::isValidColumn($dataIndx) == false){
                throw new Exception("Invalid column name");
            }
            $dataType = $rule->dataType;

            if(property_exists($rule, "crules")){
                $crules = $rule->crules;
            }
            else{
                $crules = array();
                $crules[] = $rule;
            }

            $fcrule = array();

            foreach($crules as $crule){

                $value = $crule->value;
                $value2 = property_exists($crule, "value2")? $crule->value2: "";
                if($dataType == "bool"){
                    $value = ($value == "true")? 1: 0;
                    $value2 = ($value2 == "true")? 1: 0;
                }
                $condition = $crule->condition;
                FilterHelper::{"_".$condition}($dataIndx, $fcrule, $param, $value, $value2);

            }//end of crules loop.

            $frule[] = (sizeof($fcrule) > 1)? "(" . join(" ".$rule->mode." ", $fcrule) . ")": $fcrule[0];

        }//end of rules loop.

        $query = (sizeof($frule) > 0)? join(" ".$mode." ", $frule): "";
        return (object)[
          'query' => $query,
          'param' => $param
        ];
    }
}
