<?php
// +----------------------------------------------------------------------
// | Author: Donald Cheung
// +----------------------------------------------------------------------

namespace User\Api;
use User\Api\Api;
use User\Model\UcenterUserModel;

class UserApi extends Api {

    //
    // @brief  method  _init  构造方法，实例化操作模型
    //
    protected function _init() {
        $this->model = new UcenterUserModel();
    }

    //
    // @brief  method  register  注册一个新用户
    //
    // @param  string $username  用户名
    // @param  string $password  用户密码
    // @param  string $email     用户邮箱
    // @param  string $mobile    用户手机号码
    //
    // @return integer  注册成功-用户ID，注册失败-错误编号
    //
    public function register($username, $password, $email, $mobile='') {
        return $this->model->register($username, $password, $email, $mobile);
    }

    //
    // @brief  method  login  用户登录认证
    //
    // @param  string  $username  用户名
    // @param  string  $password  用户密码
    // @param  integer $type      用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
    //
    // @return integer  登录成功-用户ID，登录失败-错误编号
    //
    public function login($username, $password, $type=1) {
        return $this->model->login($username, $password, $type);
    }

    //
    // @brief  method  info  获取用户信息
    //
    // @param  string  $username  用户名
    // @param  integer $type      用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
    //
    // @return (1) array - 用户信息
    //         (2) integer - 错误编号
    //
    public function info($username, $type=1) {
        return $this->model->info($username, $type);
    }

    //
    // @brief  method  checkUsername  检测用户名
    //
    // @param  string  $username  用户名
    //
    // @return integer  (1) 0 - 验证通过
    //                  (2) <0 - 错误编号
    //
    public function checkUsername($username) {
        return $this->model->checkField($username, 1);
    }

    //
    // @brief   method  checkEmail  检测邮箱
    //
    // @param   string  $email  邮箱
    //
    // @return  integer  (1) 0 - 验证通过
    //                   (2) <0 - 错误编号
    //
    public function checkEmail($email) {
        return $this->model->checkField($email, 2);
    }

    //
    // @brief  method  checkMobile   检测手机
    //
    // @param  string  $mobile  手机
    //
    // @return  integer  (1) 0 - 验证通过
    //                   (2) <0 - 错误编号
    //
    public function checkMobile($mobile) {
        return $this->model->checkField($mobile, 3);
    }

    //
    // @brief  method  updateInfo  更新用户信息
    //
    // @param  integer  $username  用户名
    // @param  string   $password  密码，用来验证
    // @param  array    $data      修改的字段数组
    // @param  integer  $type      用户名类型 （1-用户名，2-邮箱，3-手机，4-ID）
    //
    // @return array    (1) success=true - 修改成功
    //                  (2) success=false - 修改失败, error - 错误编码
    //
    public function updateInfo($username, $password, $data, $type=1, $authorize=true) {
        $res = $this->model->updateUserFields($username, $password, $data, $type, $authorize);
        if ($res === true || $res > 0) {
            return array("success" => true);
        } else {
            return array('success' => false, 'error' => $res);
        }
    }
}
