<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/2/28
 * Time: 20:39
 */
namespace app\index\controller;
use think\Controller;
use phpmailer\PHPMailer;
use phpmailer\Exception;
use phpmailer\SMTP;
use ku\Verify;
use think\Cache;
class Email extends Controller{

    protected $_hostEmail = array(
        'email'=>'913294974@qq.com',
        'name'=>'slp课堂派',
//        'name'=>'集美大学',
        'password'=>'lbdwwjnstqvzbcaj'
    );

    public function index(){

    }

    //注册邮箱验证码发送
    public function regist(){
        $request = $this->request;
        $email = $request->param('email','','string');
        if(!Verify::isEmail($email)){
            return json(array('status'=>false,'msg'=>'邮箱格式不正确'));
        }
        $code = mt_rand(100000,999999);
        $cacheRes = Cache::set($email,$code,200);
        if(!$cacheRes){
            return json(array('status'=>false,'msg'=>'验证码存储错误'));
        }
        $subject = 'slp课堂派注册';
        $body = '验证码：'.$code;
        $res = $this->sendEmail($email,$subject,$body);
        if($res){
            return json(array('status'=>true,'msg'=>'发送成功'));
        }else{
            return json(array('status'=>false,'msg'=>'发送失败'));
        }
    }

//    public function play(){
//        $request = $this->request;
//        $email = $request->param('email','','string');
//        if(!Verify::isEmail($email)){
//            return json(array('status'=>false,'msg'=>'邮箱格式不正确'));
//        }
//        $subject = '集美大学处罚通知';
//        $body = '范进雄同学，由于你在注册时间，连续夜不归宿，扣除学分2学分，如有问题联系教学科老师:'.'13023913185';
//        $res = $this->sendEmail($email,$subject,$body);
//        if($res){
//            return json(array('status'=>true,'msg'=>'发送成功'));
//        }else{
//            return json(array('status'=>false,'msg'=>'发送失败'));
//        }
//    }

    /**
     * 邮箱发送
     * @param $email      //收件邮箱
     * @param $subject //标题
     * @param $body //内容
     * @return bool
     */
    public function sendEmail($email,$subject,$body){
        //实例化
        $mail=new PHPMailer(true);
        try{
            //邮件调试模式
            $mail->SMTPDebug = 0;
            //设置邮件使用SMTP
            $mail->isSMTP();
            // 设置邮件程序以使用SMTP
            $mail->Host = 'smtp.qq.com';
            // 设置邮件内容的编码
            $mail->CharSet='UTF-8';
            // 启用SMTP验证
            $mail->SMTPAuth = true;
            // SMTP username
            $mail->Username = $this->_hostEmail['email'];
            // SMTP password
            $mail->Password = $this->_hostEmail['password'];
            // 启用TLS加密，`ssl`也被接受
            //            $mail->SMTPSecure = 'tls';
            // 连接的TCP端口
            //            $mail->Port = 587;
            //设置发件人
            $mail->setFrom($this->_hostEmail['email'], $this->_hostEmail['name']);
            //  添加收件人1
            $mail->addAddress($email, 'dear');     // Add a recipient
//            $mail->addAddress('913294974@qq.com', 'dear');
            //            $mail->addAddress('ellen@example.com');               // Name is optional
            //            收件人回复的邮箱
            $mail->addReplyTo($this->_hostEmail['email'], $this->_hostEmail['name']);
            //            抄送
            //            $mail->addCC('cc@example.com');
            //            $mail->addBCC('bcc@example.com');
            //附件
            //            $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //            $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
            //Content
            // 将电子邮件格式设置为HTML
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
            $mail->isSMTP();
            return true;
        }catch (Exception $e){
            return false;
        }
    }

}
