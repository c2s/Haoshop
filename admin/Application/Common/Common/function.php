<?php


/**
 * 显示$model中的错误信息
 * @param $model
 */
function showModelError($model){
    $errors = $model->getError();
    $html = '<ul>';
    if(is_array($errors)){
        foreach($errors as $error){
            $html.="<li>$error</li>";
        }
    }else{
        $html.="<li>$errors</li>";
    }
    $html .= '</ul>';
    return $html;
}


/**
 * 根据seq 分割
 * @param $str
 * @param string $seq
 */
function str2arr($str,$seq=','){
    return  explode($seq,$str);
}

/***
 * 根据seq将数组连接起来
 * @param $arr
 * @param string $seq
 */
function arr2str($arr,$seq=','){
    return implode($seq,$arr);
}

/**
 * 用来生成下拉框
 * @param $name   生成下拉框的名字
 * @param $rows   生成下拉框需要的数据列表
 * @param $defaultValue   生成下拉框默认选中的值
 * @param string $valueField   使用数据列表中的哪个字段作为值
 * @param string $textField    使用数据列表中的哪个字段作为文本
 */
function arr2select($name,$rows,$defaultValue,$valueField='id',$textField='name'){
        $selectHTML = "<select name='{$name}' class='{$name}'>";
        $selectHTML.="<option value=''>--请选择--</option>";
       foreach($rows as $row){
           //默认选中
            $selected = '';
            if($row[$valueField]==$defaultValue){
                $selected.='selected';
            }
           //生成下拉框
            $selectHTML.="<option value='{$row[$valueField]}' {$selected}>{$row[$textField]}</option>";
       }
      $selectHTML.="</select>";
    echo $selectHTML;
}


if(!function_exists('array_column')){
    /**
     * 将rows二维数组对应的key上的值全部取出
     * @param $rows
     * @param $key
     */
    function array_column($rows,$key){
        $tmp = array();
        foreach($rows as $row){
            $tmp[] = $row[$key];
        }
        return $tmp;
    }
}