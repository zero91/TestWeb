<?php
// +----------------------------------------------------------------------
// | Author: Donald Cheung
// +----------------------------------------------------------------------

namespace Home\Model;
use Think\Model;

class UserModel extends Model {

    protected $_auto = array(
        array('login', 0, self::MODEL_INSERT),
        array('reg_time', NOW_TIME, self::MODEL_INSERT),
        array('reg_ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
        array('last_login_time', 0, self::MODEL_INSERT),
        array('last_login_ip', 0, self::MODEL_INSERT),
        array('status', 1, self::MODEL_INSERT),
    );

    //
    // @brief  login  登录指定用户
    //
    // @param  integer  $uid 用户ID
    //
    // @return boolean  ture-登录成功，false-登录失败
    //
    public function login($uid) {
        // 检测是否在当前应用注册
        $user = $this->field(true)->find($uid);
        if (!$user) { //未注册
            $info = A('User/User', 'Api')->info($uid, 4);
            $data = array('uid' => $uid, 'nickname' => $info['username'], 'status' => 1);
            $user = $this->create($data);
            if (!$this->add($user)) {
                $this->error = '前台用户信息注册失败，请重试！';
                return false;
            }
        } elseif ($user['status'] != 1) {
            $this->error = '用户未激活或已禁用！'; //应用级别禁用
            return false;
        }

        $this->autoLogin($user);
        //action_log('user_login', 'user', $uid, $uid);
        return true;
    }

    //
    // @brief  method  logout  注销当前用户
    //
    public function logout() {
        session('user_auth', null);
        session('user_auth_sign', null);
    }

    //
    // @brief  method  autoLogin  自动登录，更新用户登录信息
    //
    // @param  array  $user  用户信息
    //
    private function autoLogin($user) {
        // 更新登录信息
        $data = array(
            'uid'             => $user['uid'],
            'login'           => array('exp', '`login`+1'),
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(1),
        );
        $this->save($data);

        // 记录登录SESSION和COOKIES
        $auth = array(
            'uid'             => $user['uid'],
            'username'        => g_get_username($user['uid']),
            'nickname'        => $user['nickname'],
            'last_login_time' => $user['last_login_time'],
        );
        session('user_auth', $auth);
        session('user_auth_sign', g_data_auth_sign($auth));
    }
}
