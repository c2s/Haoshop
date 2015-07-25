<extend name="Common:index"/>
<block name="list">
    <button  class="ajax_post" url="{:U('changeStatus')}">删除</button>
    <table cellpadding="3" cellspacing="1">
        <tr>
            <th style="width: 50px"><input type="checkbox" class="selectAll">序号 </th>
            <?php foreach($fields as $fieldx):
                  if($fieldx['name']===false) continue;
              ?>
            <th><?php echo $fieldx['name'] ?></th>
            <?php endForeach;?>
            <th>操作</th>
        </tr>
        <volist name="rows" id="row">
            <tr>
                <td class="first-cell"><input type="checkbox" name="id[]" value="{$row.id}" class="id">{$row.id} </td>
                <?php
                    foreach($fields as $fieldy):
                      if($fieldy['name']===false) continue;
                      if($fieldy['Field']=='status'){
                         echo '<td align="center"><a class="ajax_get" href="{:U(\'changeStatus\',array(\'id\'=>$row[\'id\'],\'status\'=>1-$row[\'status\']))}"><img src="__IMG__/{$row.status}.gif" alt=""/></a></td>';
                         continue;
                      }
                ?>
                <td class="first-cell">{$row.<?php echo $fieldy['Field']; ?>}</td>
                <?php endforeach;?>
                <td align="center">
                    <a href="{:U('edit',array('id'=>$row['id']))}" title="编辑">编辑</a> |
                    <a class="ajax_get" href="{:U('changeStatus',array('id'=>$row['id']))}" title="移除">移除</a>
                </td>
            </tr>
        </volist>
    </table>
</block>