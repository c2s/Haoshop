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

/**
 * 自定义加密方法
 *
 * @param $str1  需要加密的字符串
 * @param $salt 盐
 */
function mymd5($str1,$salt=''){
  return md5(md5($str1).$salt);
}

/**
 * 对验证码进行验证..
 * @param $captcha
 * @return bool
 */
function checkCaptcha($captcha){
    $verify = new \Think\Verify();
    return $verify->check($captcha);
}

/**
 * 登录方法(在session中保存登录信息)
 */
function login($userinfo=''){
    if($userinfo){
        session('userinfo',$userinfo);
    }else{
        return session('userinfo');
    }
}

/**
 * 判断用户是否登录
 * @return bool
 */
function isLogin(){
    return login()?true:false;
}

/**
 * 注销的函数
 */
function logout(){
    session('userinfo',null);
    session('permission_urls',null);
    session('permission_ids',null);

    cookie('uk',null);
    cookie('uid',null);
}


/**
 * 该方法用来处理session中保存的权限url
 * @param string $urls
 * @return mixed
 */
function savePermissionURL($urls=''){
    if($urls){
        session('permission_urls',$urls);
    }else{
        return session('permission_urls');
    }
}/**
 * 该方法用来处理session中保存的权限id
 * @param string $urls
 * @return mixed
 */
function savePermissionId($ids=''){
    if($ids){
        session('permission_ids',$ids);
    }else{
        return session('permission_ids');
    }
}



function  getRedis(){
    $redis = new \Redis();

    //从配置中得到redis的主机和短裤
    $host = C('REDIS_HOST') ? C('REDIS_HOST'): '127.0.0.1';
    $port =  C('REDIS_PORT') ? C('REDIS_PORT')  : 6379;
    $redis->connect($host,$port);
    return $redis;
}


/**
 * 发送邮件的方法
 * @param $receiver 接收人
 * @param $title   邮件标题
 * @param $content   邮件内容
 */
function sendMail($receiver,$title,$content){
    //>>1.开启一个线程发送邮件
    $sendMailThread = new SendMailThread($receiver,$title,$content);
    $sendMailThread->start();
//    $sendMailThread->detach();
}


/**
 * 发送邮件的线程
 * Class SendMailThread
 */
class SendMailThread extends Thread{
    private $receiver;
    private $title;
    private $content;
    public function __construct($receiver,$title,$content){
        $this->receiver = $receiver;
        $this->title = $title;
        $this->content = $content;
    }

    public function run(){

        //>>1.加载发送邮件的类PHPMailer
            //   /使用.代替    .使用#代替
            vendor('PHPMailer.PHPMailerAutoload');
            $mail = new PHPMailer();

            $mail->SMTPDebug = 3; // 是否开启调试                             // Enable verbose debug output
            $mail->CharSet = 'utf-8';
            $mail->isSMTP(); //使用的为发送邮件协议                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.126.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'itsource520@126.com';                 // SMTP username
            $mail->Password = 'qqitsource520';                           // SMTP password
    //    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    //    $mail->Port = 587;                                    // TCP port to connect to


            $mail->From = 'itsource520@126.com'; //发件人的地址
            $mail->FromName = 'itsource';  //发件人的名字
            $mail->addAddress($this->receiver);     // Add a recipient

    //    $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
    //    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
            $mail->isHTML(true);                                  // Set email format to HTML

            $mail->Subject = $this->title;
            $mail->Body    = $this->content;
            $mail->AltBody = $this->content;

            $mail->send();
    }
}
