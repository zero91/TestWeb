<?php
// +----------------------------------------------------------------------
// | Author: Donald Cheung
// +----------------------------------------------------------------------

namespace User\Model;
use Think\Model;

class UcenterUserModel extends Model {
    // 数据表前缀
    protected $tablePrefix = UC_TABLE_PREFIX;

    // 数据库连接
    protected $connection = UC_DB_DSN;

    // 用户模型自动验证
    protected $_validate = array(
        array('username', '1,32', -1, self::EXISTS_VALIDATE, 'length'), // 用户名长度不合法
        array('username', 'checkDenyUsername', -2, self::EXISTS_VALIDATE, 'callback'),
        array('username', '', -3, self::EXISTS_VALIDATE, 'unique'), //用户名被占用
        array('password', '6,32', -4, self::EXISTS_VALIDATE, 'length'), //密码长度不合法
        array('email', 'email', -5, self::EXISTS_VALIDATE), //邮箱格式不正确
        array('email', '1,64', -6, self::EXISTS_VALIDATE, 'length'), //邮箱长度不合法
        array('email', 'checkDenyEmail', -7, self::EXISTS_VALIDATE, 'callback'), //邮箱禁止注册
        array('email', '', -8, self::EXISTS_VALIDATE, 'unique'), //邮箱被占用
        array('mobile', 'number', -9, self::EXISTS_VALIDATE), //手机格式不正确 TODO
        array('mobile', 'checkDenyMobile', -10, self::EXISTS_VALIDATE, 'callback'), //手机禁止注册
        array('mobile', '', -11, self::EXISTS_VALIDATE, 'unique'), //手机号被占用
    );

    protected $_auto = array(
        array('password', 'encryptPassword', self::MODEL_BOTH, 'callback'),
        array('password', '', self::MODEL_BOTH, 'ignore'),
        array('regtime', NOW_TIME, self::MODEL_INSERT),
        array('regip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
        array('update_time', NOW_TIME),
        array('status', 'getStatus', self::MODEL_BOTH, 'callback'),
    );

    //
    // @brief  method  register  注册一个新用户
    //
    // @param  string  $username 用户名
    // @param  string  $password 用户密码
    // @param  string  $email    用户邮箱
    // @param  string  $mobile   用户手机号码
    //
    // @return integer  (1) 用户ID - 注册成功(>0)
    //                  (2) 错误编号 - 注册失败(<0)
    //
    // @error  -100  未知错误
    // @error  其它错误ID见自动验证注释
    //
    public function register($username, $password, $email, $mobile) {
        $data = array(
            'username' => $username,
            'password' => $password,
            'email'    => $email,
            'mobile'   => $mobile,
        );

        if (empty($data['mobile'])) {
            unset($data['mobile']);
        }

        if ($this->create($data)) {
            $id = $this->add();
            return $id ? $id : -100; // -100 -- 未知错误，大于0 -- 注册成功
        } else {
            return $this->getError(); // 错误详情见自动验证注释
        }
    }

    //
    // @brief  method  login  用户登录认证
    //
    // @param  string  $username  用户名
    // @param  string  $password  用户密码
    // @param  integer $type      用户名类型 （1-用户名，2-邮箱，3-手机，4-ID）
    //
    // @return integer  (1) 用户ID - 登录成功(>0)
    //                  (2) 错误编号 - 登录失败(<0)
    //
    // @error  -1  参数错误
    // @error  -2  密码错误
    // @error  -3  用户不存在或者被禁用
    //
    public function login($username, $password, $type=1) {
        $map = array();
        switch ($type) {
            case 1: $map['username'] = $username; break;
            case 2: $map['email'] = $username; break;
            case 3: $map['mobile'] = $username; break;
            case 4: $map['id'] = $username; break;
            default: return -1; //参数错误
        }

        // 获取用户数据
        $user = $this->where($map)->find();
        if (is_array($user) && $user['status'] > 0) {
            if ($this->encryptPassword($password) === $user['password']) {
                $this->updateLogin($user['id']); //更新用户登录信息
                return $user['id']; //登录成功，返回用户ID
            } else {
                return -2; //密码错误
            }
        } else {
            return -3; //用户不存在或被禁用
        }
    }

    //
    // @brief  method  info  获取用户信息
    //
    // @param  string  $username  用户名
    // @param  integer $type      用户名类型 （1-用户名，2-邮箱，3-手机，4-ID）
    //
    // @return (1) array - 用户信息
    //         (2) integer - 错误编号
    //
    // @error  -100  参数错误
    // @error  -101  用户不存在或被禁用
    //
    public function info($username, $type=1) {
        $map = array();
        switch ($type) {
            case 1: $map['username'] = $username; break;
            case 2: $map['email'] = $username; break;
            case 3: $map['mobile'] = $username; break;
            case 4: $map['id'] = $username; break;
            default: return -100; //参数错误
        }

        $user = $this->where($map)->field(true)->find();
        if (is_array($user) && $user['status'] > 0) {
            return $user;
        } else {
            return -101; //用户不存在或被禁用
        }
    }

    //
    // @brief  method  checkField  检测用户信息
    //
    // @param  string  $field  用户名
    // @param  integer $type   用户名类型(1-用户名，2-用户邮箱，3-用户电话)
    //
    // @return integer  (1) 0 - 成功
    //                  (2) <0 - 错误编号
    //
    public function checkField($field, $type=1) {
        $data = array();
        switch ($type) {
            case 1: $data['username'] = $field; break;
            case 2: $data['email'] = $field; break;
            case 3: $data['mobile'] = $field; break;
            default: return -100; //参数错误
        }
        return $this->create($data) ? 0 : $this->getError();
    }

    //
    // @brief  method  updateUserFields  更新用户信息
    //
    // @param  integer  $username  用户名
    // @param  string   $password  密码，用来验证
    // @param  array    $data      修改的字段数组
    // @param  integer  $type      用户名类型 （1-用户名，2-邮箱，3-手机，4-ID）
    //
    // @return  (1) true - 修改成功，false - 修改失败
    //          (2) integer - 错误编号
    //
    // @error  -100  参数错误
    // @error  -101  身份未验证通过，密码不正确
    // @error  其他错误编号见自动验证注释
    //
    public function updateUserFields($username, $password, $data, $type=1, $authorize=true) {
        $map = array();
        switch ($type) {
            case 1: $map['username'] = $username; break;
            case 2: $map['email'] = $username; break;
            case 3: $map['mobile'] = $username; break;
            case 4: $map['id'] = $username; break;
            default: return -100; //参数错误
        }

        // 更新前检查用户密码
        if ($authorize && !$this->verifyUser($username, $password, $type)) {
            return -101; // 密码不正确
        }

        $user_info = $this->info($username, $type);
        foreach ($user_info as $key => $value) {
            if (array_key_exists($key, $data) && $data[$key] == $value) {
                unset($data[$key]);
            }
        }
        if (empty($data)) {
            return true;
        }

        // 更新用户信息
        $data = $this->create($data);
        if ($data) {
            return $this->where($map)->save($data);
        } else {
            return $this->getError(); // 错误详情见自动验证注释
        }
    }

    // ------------------------------ 保护方法-------------------------------------

    //
    // @brief  method  encryptPassword  加密密码字符串
    //
    // @param  string  $password  密码字符串
    //
    // @return string  若原始密码为空，则返回空字符串；否则返回加密后的字符串
    //
    protected function encryptPassword($password) {
        return $password ? g_ucenter_md5($password, UC_AUTH_KEY) : "";
    }

    //
    // @brief  method  getStatus  根据配置指定用户状态
    //
    // @return integer 用户状态
    //
    protected function getStatus() {
        return true; //TODO: 暂不限制，下一个版本完善
    }

    //
    // @brief  method  checkDenyMember  检测用户名是不是被禁止注册
    //
    // @param  string  $username 用户名
    //
    // @return boolean  (1) ture - 未禁用
    //                  (2) false - 禁止注册
    //
    protected function checkDenyUsername($username) {
        return true; //TODO: 暂不限制，下一个版本完善
    }

    //
    // @brief  method  checkDenyEmail  检测邮箱是不是被禁止注册
    //
    // @param  string  $email 邮箱
    //
    // @return boolean  (1) ture - 未禁用
    //                  (2) false - 禁止注册
    //
    protected function checkDenyEmail($email) {
        return true; //TODO: 暂不限制，下一个版本完善
    }

    // 
    // @brief  method  checkDenyMobile  检测手机是不是被禁止注册
    //
    // @param  string  $mobile 手机
    //
    // @return boolean  (1) ture - 未禁用
    //                  (2) false - 禁止注册
    //
    protected function checkDenyMobile($mobile) {
        return true; //TODO: 暂不限制，下一个版本完善
    }

    //
    // @brief  method  updateLogin  更新用户登录信息
    //
    // @param  integer $uid 用户ID
    //
    protected function updateLogin($id) {
        $data = array(
            'id'              => $id,
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(1),
        );
        $this->save($data);
    }

    //
    // @brief  method  verifyUser  验证用户密码
    //
    // @param  integer  $username     用户名
    // @param  string   $password_in  密码
    // @param  integer  $type         用户名类型 （1-用户名，2-邮箱，3-手机，4-ID）
    //
    // @return boolean  (1) true - 验证成功
    //                  (2) false - 验证失败
    //
    protected function verifyUser($username, $password_in, $type=1) {
        if (!UC_UPDATE_NEED_PASSWORD) {
            return true;
        }

        $map = array();
        switch ($type) {
            case 1: $map['username'] = $username; break;
            case 2: $map['email'] = $username; break;
            case 3: $map['mobile'] = $username; break;
            case 4: $map['id'] = $username; break;
            default: return false;
        }

        $password = $this->where($map)->getField('password');
        if ($this->encryptPassword($password_in) === $password) {
            return true;
        }
        return false;
    }
}
