namespace Admin\Model;


use Think\Model;


class <?php echo $name ?>Model extends BaseModel{

    protected $_validate = array(
        <?php foreach($fields as $fieldk){
           if($fieldk['Key']=='PRI' || $fieldk['Null']=='YES'){
                 continue;
           }
          echo "array('{$fieldk['Field']}','require','{$fieldk['name']}不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),\r\n";

        }
        ?>

    );
}