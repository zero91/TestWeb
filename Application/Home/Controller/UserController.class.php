<?php
// +----------------------------------------------------------------------
// | Author: Donald Cheung
// +----------------------------------------------------------------------

namespace Home\Controller;

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
        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __ROOT__;
        $this->assign('forward', $forward);

        $this->assign('title', "登录");
        $this->display();
    }

    public function logout() {
        if (g_is_login() > 0) {
            D('User')->logout();
            $this->success('退出成功！', U('Index/index'));
        } else {
            $this->redirect('Index/index');
        }
    }  

    //
    // @brief  method  verifyEmail  用户邮箱验证
    //
    public function verifyEmail($auth="") {
        //TODO 待进一步完善
        $email = g_auth_decode(urldecode($auth), C('USER_AUTH_KEY'));

        $user_info = A('User/User', 'Api')->info($email, 2);
        if (is_array($user_info)) {
            if ($user_info['authstr'] == $auth) {
                echo "Accpeted<br/>";
            }

        } else {
            echo "Wrong Answer<br/>";
        }

        echo $user_info['authstr'] . "<br/>";
        echo $auth . "<br/>";
    }

    //
    // @brief  method  profile  个人基本信息页
    //
    public function profile() {
        $this->assign('title', "个人信息");
        $uid = g_is_login();
        if ($uid > 0) {
            $user = A('User/User', 'Api')->info($uid);
            $this->assign("user", $user);
            $this->display();
        } else {
            $this->redirect('User/login');
        }
    }

    //
    // @brief method  password  更改账户密码
    //
    public function password() {
        if (g_is_login()) {
            $this->display();
        } else {
            $this->redirect('User/login');
        }
    }

    // 用户上传头像界面
    public function avatar() {
        if (g_is_login()) {
            $this->display();
        } else {
            $this->redirect('User/login');
        }
    }

    public function editimg($success=false) {
        $this->assign("success", $success);
        $this->display();
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

        if (C('CODE_REGISTER') && !g_check_captcha($captcha, $this->verify_id, true)) {
            $this->ajaxReturn(array("success" => false, "error" => 102, "msg" => "验证码错误"));
        }

        //TODO  当前用户已登录
        //TODO  当前IP已经超过当日最大注册数目
        if (empty($email) || empty($password)) {
            $this->ajaxReturn(array("success" => false, "error" => 103, "msg" => "参数错误"));
        }

        $uid = A('User/User', 'Api')->register($email, $password, $email); //默认用户名为注册邮箱
        if ($uid > 0) {
            $authstr = g_auth_encode($email, C('USER_AUTH_KEY'), 24 * 3600); //24小时内有效
            A('User/User', 'Api')->updateInfo($email, $password, array("authstr" => $authstr), 2);

            $verify_url = WEB_DOMAIN . U('User/verifyEmail') . '?auth=' . urlencode($authstr);

            $subject = "欢迎注册" . C('PRODUCT_NAME') . "，请验证您的邮箱";
            $message = '';
            $message .= '<p>请点击以下链接，激活账号（24小时内有效）：</p>';
            $message .= '<a swaped="true" target="_blank" href="' . $verify_url . '">';
            $message .= $verify_url . '</a>';
            $message .= '<p>如果您没有进行注册操作，请忽略此邮件。</p>';
            g_send_mail($email, $email, $subject, $message);
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

    //
    // @brief  method  ajaxLogin  登录
    // @request  POST
    //
    // @param  string  $username  用户名
    // @param  string  $password  登录密码
    // @param  string  $captcha   验证码
    //
    // @ajaxReturn  成功 - array("success" => true)
    //              失败 - array("success" => false, "error" => 错误码, 'msg' => 错误提示信息)
    //
    // @error  101  验证码错误
    // @error  102  已登录
    // @error  103  应用注册失败
    // @error  104  密码错误
    // @error  105  用户不存在或被禁用
    // @error  106  发生未知错误
    //
    public function ajaxLogin($username="", $password="", $captcha="") {
        if (C('CODE_LOGIN') && !g_check_captcha($captcha, $this->verify_id, true)) {
            $this->ajaxReturn(array('success' => false, 'error' => 101, 'msg' => '验证码错误'));
        }

        //TODO: cookie有效时间的处理

        $uid = A('User/User', 'Api')->login($username, $password, 1);
        if ($uid > 0) {
            if (g_is_login() > 0) {
                $this->ajaxReturn(array('success' => false, 'error' => 102, 'msg' => '你已登录'));
            }

            if (D('User')->login($uid)) {
                $this->ajaxReturn(array("success" => true));
            } else {
                $this->ajaxReturn(array('success' => false, 'error' => 103,
                                        'msg' => D('User')->getError()));
            }
        } else {
            $res = array('success' => false);
            switch ($uid) {
                case -2: $res['error'] = 104; $res['msg'] = '密码错误'; break;
                case -3: $res['error'] = 105; $res['msg'] = '用户不存在或被禁用'; break;
                default: $res['error'] = 106; $res['msg'] = '发生未知错误'; break;
            }
            $this->ajaxReturn($res);
        }
    }

    //
    // @brief  method  ajaxLogout  用户登出
    //
    // @request  POST
    //
    // @ajaxReturn  成功 - array("success" => true)
    //              失败 - array("success" => false, "error" => 错误码, "msg" => 用户提示信息)
    //
    // @error  101  用户尚未登录
    //
    public function ajaxLogout() {
        if (g_is_login() > 0) {
            D('User')->logout();
            $this->ajaxReturn(array("success" => true));
        } else {
            $this->ajaxReturn(array("success" => false, "error" => 101, "msg" => "您尚未登录"));
        }
    }

    //
    // @brief  method  ajaxUpdatePassword  更改密码
    //
    // @request  POST
    //
    // @param  string  $cur_password  新密码
    // @param  string  $new_password  老密码
    // @param  stirng  $captcha       验证码
    //
    // @ajaxReturn  正确 => array("success" => true, "forward" => 跳转链接),
    //              失败 => array("success" => false, "error" => 错误码, "msg" => 错误提示信息)
    //
    // @error  101  用户尚未登录
    // @error  102  新密码长度不合法
    // @error  103  新密码与旧密码相同
    // @error  104  旧密码不对
    // @error  105  新密码不合法
    // @error  106  更改错误，可能是其它位置的地方发生错误
    //
    public function ajaxUpdatePassword($cur_password, $new_password, $captcha='') {
        $uid = g_is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101, "msg" => "您尚未登录"));
        }

        if (!g_check_captcha($captcha, $this->verify_id, true)) {
            $this->ajaxReturn(array("success" => false, "error" => 102, "msg" => "验证码错误"));
        }

        $ret = A('User/User', 'Api')->updateInfo(
                                    $uid, $cur_password, array("password" => $new_password), 4);
        if ($ret['success']) {
            D('User')->logout();
            $this->ajaxReturn(array("success" => true, "forward" => U('User/login')));

        } else {
            $res = array("success" => false);
            switch ($ret['error']) {
                case 0:
                    $res['error'] = 103;
                    $res['msg'] = '新旧密码不能相同';
                    break;
                case -101:
                    $res['error'] = 104;
                    $res['msg'] = '旧密码不对';
                    break;
                case -4:
                    $res['error'] = 105;
                    $res['msg'] = '新密码不合法';
                    break;
                default:
                    $res['error'] = 106;
                    $res['msg'] = '更改错误';
                    break;
            }
            $this->ajaxReturn($res);
        }
    }

    private $verify_id = 1;
}
