$(function(){
    ////////////////列表中复选框的状态--开始///////////////////////
    $('.selectAll').click(function(){
        $('.id').prop('checked',$(this).prop('checked'));
    });
    $('.id').click(function(){
        //判断class=id 的复选框的不选中状态的个数
        $('.selectAll').prop('checked',$('.id:not(:checked)').length==0);
    });
    ////////////////列表中复选框的状态--结束///////////////////////

    ////////////////通用的ajax--开始///////////////////////

    //页面加载完毕之后向带有class=ajax_get的标签上添加处理函数
    $('.ajax_get').click(function(){
        var target =  $(this).attr('href');
        $.get(target,function(data){
            //提示框
            updateAlert(data);
        });
        return false;//取消默认操作
    });


    //当页面中的表中代码class='ajax_post',发送post请求
    $('.ajax_post').click(function(){
        //>>1.准备请求地址
        var form = $(this).closest('form');
        var target = form.length!=0?form.attr('action'):$(this).attr('url');
        //>>2.准备参数
        var params = form.length!=0?form.serialize():$('.id').serialize();
        if(params.length==0){
            return false;
        }
        //>>3.响应回来时,回调函数执行..
        $.post(target,params,function(data){
            updateAlert(data);
        });
        //>>4.取消默认请求
        return false;
    });

    /**
     * 根据data的值弹出提示框
     * @param data
     */
    window.updateAlert = function(data){
        //显示提示内容
        $('.alert-content').html(data.info);
        //展示出来
        $('#top-alert').show();
        if(data.status){
            //更改颜色
            $('#top-alert').removeClass('alert-error').addClass('alert-success')

            //展示1秒钟之后跳转
            setTimeout(function(){
                location.href=data.url;
            },1000);
        }else{
            $('#top-alert').removeClass('alert-success').addClass('alert-error');

            if(data.url===undefined){
                //没有url属性就自动隐藏
                setTimeout(function(){
                   $('#top-alert').hide();
                },1000);
            }else{
                //有url属性让其刷新
                setTimeout(function(){
                    //自身刷新
                    location.reload();
                },1000);
            }


        }
    }

    ////////////////通用的ajax--结束///////////////////////
});