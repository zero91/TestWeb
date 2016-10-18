<?php
// +----------------------------------------------------------------------
// | Author: Donald Cheung
// +----------------------------------------------------------------------

//
// @brief  function  g_ucenter_md5  系统非常规MD5加密方法
//
// @param  string  $str  要加密的字符串
//
// @return string  加密后的字符串
//
function g_ucenter_md5($str, $key='DonaldCheung') {
    return $str === '' ? '' : md5(sha1($str) . $key);
}

//
// @brief  function  g_ucenter_encrypt  系统加密方法（与g_ucenter_decrypt配对使用）
//
// @param  string  $data    待加密的字符串
// @param  string  $key     加密密钥
// @param  int     $expire  过期时间 (单位:秒)
//
// @return string  加密后的字符串
//
function g_ucenter_encrypt($data, $key, $expire=0) {
    $data = base64_encode($data);
    $key = md5($key);

    $data_len = strlen($data);
    $key_len = strlen($key);

    $str = sprintf('%010d', $expire ? $expire + time() : 0);
    for ($i = 0; $i < $data_len; ++$i) {
        $str .= chr((ord($data[$i]) + ord($key[$i % $key_len])) % 256);
    }
    return str_replace('=', '', base64_encode($str));
}

//
// @brief  function  g_ucenter_decrypt  系统解密方法（与g_ucenter_encrypt配对使用）
//
// @param  string  $data 要解密的字符串
// @param  string  $key  加密密钥
//
// @return string  解密后的字符串
//
function g_ucenter_decrypt($data, $key) {
    $data = base64_decode($data);
    $key = md5($key);

    $expire = substr($data, 0, 10);
    if ($expire > 0 && $expire < time()) {
        return '';
    }

    $data = substr($data, 10);
    $data_len = strlen($data);
    $key_len = strlen($key);
    $str = '';
    for ($i = 0; $i < $data_len; ++$i) {
        $str .= chr((ord($data[$i]) + 256 - ord($key[$i % $key_len])) % 256);
    }
    return base64_decode($str);
}

?>
