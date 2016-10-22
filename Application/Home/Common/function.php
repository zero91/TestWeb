<?php
// +----------------------------------------------------------------------
// | Author: Donald Cheung
// +----------------------------------------------------------------------
// | Brief: Home下的公用函数
// +----------------------------------------------------------------------

//
// @brief  function  g_check_captcha  检测验证码是否正确
//
// @param  string   $captcha  验证码
// @param  integer  $id       验证码ID
// @param  integer  $reset    验证后是否重置验证码
//
// @return boolean   验证码是否正确
//
function g_check_captcha($captcha, $id=1, $reset=true) {
    $config = array(
        //"fontSize" => 36,
        "reset" => $reset
    );
    $verify = new \Think\Verify($config);
    return $verify->check($captcha, $id);
}

