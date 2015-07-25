<extend name="Common:edit"/>
<block name="form">
    <form method="post" action="{:U()}">
        <table cellspacing="1" cellpadding="3" width="100%">
            <?php foreach($fields as $fieldz):
                if($fieldz['Key']=='PRI') continue;
             ?>
            <tr>
                <td class="label"><?php echo $fieldz['name'] ?></td>
                <td>
                    <?php
                       if(strpos($fieldz['Comment'],'textarea')!==false){
                             echo "<textarea  name=\"{$fieldz['Field']}\" cols=\"60\" rows=\"4\">{\${$fieldz['Field']}}</textarea>";
                       }elseif(strpos($fieldz['Comment'],'text')!==false){
                             echo "<input type=\"text\" name=\"{$fieldz['Field']}\" maxlength=\"60\" value=\"{\${$fieldz['Field']}}\"/>";
                       }elseif(strpos($fieldz['Comment'],'file')!==false){
                             echo "<input type=\"file\" name=\"{$fieldz['Field']}\" maxlength=\"60\" value=\"{\${$fieldz['Field']}}\"/>";
                       }elseif(strpos($fieldz['Comment'],'radio')!==false){
                             $str  = ltrim(strstr($fieldz['Comment'],'|'),'|');  //1=是,0=否
                             $arr = str2arr($str); //数组    array('1=是','0=否')
                             foreach($arr as $row){
                                 $values = str2arr($row,'=');
                                echo "<input  type=\"radio\" name=\"{$fieldz['Field']}\" value=\"{$values[0]}\"/> {$values[1]}";
                             }
                        }
                    ?>


                    <span class="require-field">*</span>
                </td>
            </tr>
            <?php endForeach; ?>
            <tr>
                <td colspan="2" align="center"><br />
                    <input type="hidden" name="id" value="{$id}"/>
                    <input type="submit" class="button ajax_post" value=" 确定 " />
                    <input type="reset" class="button" value=" 重置 " />
                </td>
            </tr>
        </table>
    </form>
</block>