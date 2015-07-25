$(function(){
    $.get('/index.php?s=Member/getUserInfo',function(data){  //data是登录的用户名
        if(data){
            //data有值得情况下,说明是用户名
            $('#userinfo').html('您好，欢迎'+data+'来到京西！ [<a href="/index.php?s=Member/logout">注销</a>]');

        }else{
            //data有值得情况下,说明是用户名
            $('#userinfo').html('您好，欢迎来到京西！  [<a href="/index.php?s=Member/login">登录</a>] [<a href="/index.php?s=Member/register">免费注册</a>]');

        }
    });
});