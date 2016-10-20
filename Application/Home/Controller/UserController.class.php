<?php
namespace Home\Controller;
use User\Api\UserApi;

class UserController extends HomeController {
    public function index() {
        $this->display();
    }

    public function register() {
        if (!C('USER_ALLOW_REGISTER')) {
            $this->error('注册已关闭');
        }
        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __ROOT__;
        $this->assign('forward', $forward);

        $this->assign('title', "注册");
        $this->display();
    }

    public function login() {
        $this->assign('title', "登录");

        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __ROOT__;
        $this->assign('forward', $forward);

        $this->display();
    }


    public function logout() {
        if (is_login()) {
            D('User')->logout();
            $this->success('退出成功！', U('Index/index'));
        } else {
            $this->redirect('Index/index');
        }
    }  

    //
    // @brief  method  captcha  返回验证码图片
    //
    public function captcha() {
        $verify = new \Think\Verify();
        $verify->entry($this->verify_id);
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    //
    // @brief  method  ajaxCheckCaptcha  验证验证码是否正确
    // @request  GET/POST
    // @param  string  $captcha  验证码
    //
    // @ajaxReturn  正确 => array("success" => true),
    //              失败 => array("success" => false, "error" => 错误码, "msg" => 错误提示信息)
    //
    // @error  101  验证码书写错误
    //
    public function ajaxCheckCaptcha($captcha="") {
        if (!g_check_captcha($captcha, $this->verify_id, false)) {
            $this->ajaxReturn(array("success" => false, "error" => 101, "msg" => "验证码错误"));
        } else {
            $this->ajaxReturn(array("success" => true));
        }
    }

    //
    // @brief  method  ajaxCheckEmail  检查用户是否可以使用该邮件
    // @request  GET/POST
    // @param  string  $email  邮箱字符串
    //
    // @ajaxReturn  正确 => array("success" => true),
    //              失败 => array("success" => false, "error" => 错误码, "msg" => 错误提示信息)
    //
    // @error  101  邮箱不能为空
    // @error  102  邮箱格式不正确
    // @error  103  邮箱长度不合法
    // @error  104  邮箱禁止注册
    // @error  105  邮箱已被占用
    // @error  106  未知错误
    //
    public function ajaxCheckEmail($email="") {
        if (empty($email)) {
            $this->ajaxReturn(array("success" => false, "error" => 101, "msg" => "邮箱不能为空"));
        }

        $check_res = A('User/User', 'Api')->checkEmail($email);
        if ($check_res == 0) {
            $this->ajaxReturn(array("success" => true));
        } else {
            $res = array();
            $res['success'] = false;
            switch($check_res) {
                case -5: $res['error'] = 102; $res['msg'] = "邮箱格式不正确"; break;
                case -6: $res['error'] = 103; $res['msg'] = "邮箱长度不能超过64"; break;
                case -7: $res['error'] = 104; $res['msg'] = "该邮箱已被禁止"; break;
                case -8: $res['error'] = 105; $res['msg'] = "邮箱已被占用"; break;
                default: $res['error'] = 106; $res['msg'] = "发生未知错误"; break;
            }
            $this->ajaxReturn($res);
        }
    }

    //
    // @brief  ajaxRegister  注册
    // @request  POST
    //
    // @param  string  $email     邮箱
    // @param  string  $password  密码
    // @param  string  $captcha   验证码
    //
    // @ajaxReturn   成功 - array("success" => true)
    //               失败 - array("success" => false, "error" => 错误码, "msg" => 错误提示信息)
    //
    // @error  101  系统暂不开放注册
    // @error  102  验证码错误
    // @error  103  参数错误
    // @error  104  非法用户名
    // @error  105  密码长度必须在6-30个字符之间
    // @error  106  非法邮箱
    // @error  107  非法手机号
    // @error  108  发生未知错误
    //
    public function ajaxRegister($email='',
                                 $password='',
                                 $captcha='') {
        if (!C('USER_ALLOW_REGISTER')) {
            $this->ajaxReturn(
                    array("success" => false, "error" => 101, "msg" => "系统暂不开放注册"));
        }

        if (C('CODE_REGISTER') && !g_check_captcha($captcha)) {
            $this->ajaxReturn(array("success" => false, "error" => 102, "msg" => "验证码错误"));
        }

        //TODO  当前用户已登录
        //TODO  当前IP已经超过当日最大注册数目
        if (empty($email) || empty($password)) {
            $this->ajaxReturn(array("success" => false, "error" => 103, "msg" => "参数错误"));
        }

        $uid = A('User/User', 'Api')->register($email, $password, $email); //默认用户名为注册邮箱
        if ($uid > 0) {
            //TODO: 发送验证邮件
            $this->ajaxReturn(array("success" => true));

        } else {
            $res = array("success" => false);
            switch ($uid) {
                case -1:
                case -2:
                case -3:
                    $res['error'] = 104;
                    $res['msg'] = "非法用户名";
                    break;
                case -4: 
                    $res['error'] = 105;
                    $res['msg'] = "密码长度必须在6-30个字符之间";
                    break;
                case -5:
                case -6:
                case -7: 
                case -8:
                    $res['error'] = 106;
                    $res['msg'] = "非法邮箱地址";
                    break;
                case -9:
                case -10:
                case -11:
                    $res['error'] = 107;
                    $res['msg'] = "非法手机号";
                    break;
                default: $res['error'] = 108;
                    $res['msg'] = "发生未知错误";
                    break;
            }
            $this->ajaxReturn($res);
        }
    }

    private $verify_id = 1;
}
