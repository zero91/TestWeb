<?php
// +----------------------------------------------------------------------
// | Author: Donald Cheung
// +----------------------------------------------------------------------
// | 全局公用函数库
// +----------------------------------------------------------------------

//
// @brief  function g_send_mail  发送邮件
//
// @param  string  $to_username  收件人姓名
// @param  string  $to_email     收件人邮件地址
// @param  string  $subject      邮件主题
// @param  string  $content      邮件内容
// @param  string  $from         发件人，默认为空，表示(PRODUCT_NAME <PRODUCT_OWNER>)
//
// @return  boolean  邮件是否发送成功
//
function g_send_mail($to_username, $to_email, $subject, $content, $from='') {
    if (!$to_email || $to_email == 'null') {
        return false;
    }

    $message = <<<EOT
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>$subject</title>
    </head>
    <body>
    Hi, $to_username<br/><br/>
        $content<br><br/>
    <p style="color:grey;font-size:12px;">这封邮件由系统自动发送，请不要回复。<p>
    </body>
    </html>
EOT;

    $product_name = C('PRODUCT_NAME');
    $product_owner = C('PRODUCT_OWNER');

    if ($from == '') {
        $email_from = '=?utf8?B?' . base64_encode($product_name) . "?= <" . $product_owner. ">";
    } else {
        if (preg_match('/^(.+?) \<(.+?)\>$/', $from, $mats)) {
            $email_from = '=?utf8?B?' . base64_encode($mats[1]) . "?= <$mats[2]>";
        } else {
            $email_from = $from;
        }
    }

    if (preg_match('/^(.+?) \<(.+?)\>$/', $to_email, $mats)) {
        if ($mailusername) {
            $email_to = '=?utf8?B?' . base64_encode($mats[1]) . "?= <$mats[2]>";
        } else {
            $email_to = $mats[2];
        }
    } else {
        $email_to = $to_email;
    }

    $email_subject = '=?utf8?B?' . base64_encode(preg_replace("/[\r|\n]/", '', $subject)) . '?=';
    $email_message = chunk_split(base64_encode($message));

    $delimiter = "\r\n";
    $headers = "From: $email_from";
    $headers .= "{$delimiter}X-Priority: 3";
    $headers .= "{$delimiter}X-Mailer: {$product_name}";
    $headers .= "{$delimiter}MIME-Version: 1.0";
    $headers .= "{$delimiter}Content-type: text/html; charset=utf8";
    $headers .= "{$delimiter}Content-Transfer-Encoding: base64{$delimiter}";

    if (function_exists('mail') && @mail($email_to, $email_subject, $email_message, $headers)) {
        return true;
    }
    return false;
}

//
// @brief  function g_auth_encode  根据用户请求的客户端信息，加密一个字符串
//
// @param  string  $str       待加密字符串
// @param  string  $auth_key  加密密钥
// @param  integer $expire    有效时间（单位：秒），默认为0，表示永远有效
//
// @return  加密后的字符串
//
function g_auth_encode($str, $auth_key, $expire=0) {
    $key = substr(md5($_SERVER["HTTP_USER_AGENT"] . $auth_key), 8, 18); 
    $str = sprintf('%010d%s', $expire ? $expire + time() : 0, $str);

    $key_len = strlen($key);
    $str_len = strlen($str);

    $code = '';
    for ($i = 0; $i < $str_len; ++$i) {
        $code .= $str[$i] ^ $key[$i % $key_len];
    }    
    return base64_encode($code);
}

//
// @brief  function g_auth_decode  根据用户请求的客户端信息，解密一个字符串
//
// @param  string  $str       待解密字符串
// @param  string  $auth_key  解密密钥
//
// @return  解密后的字符串
//
function g_auth_decode($str, $auth_key) {
    $key = substr(md5($_SERVER["HTTP_USER_AGENT"] . $auth_key), 8, 18); 
    $str = base64_decode($str);

    $key_len = strlen($key);
    $str_len = strlen($str);

    $code = '';
    for ($i = 0; $i < $str_len; ++$i) {
        $code .= $str[$i] ^ $key[$i % $key_len];
    }

    $expire = substr($code, 0, 10);
    if ($expire > 0 && $expire < time()) {
        return '';
    }
    return substr($code, 10);
}

//
// @brief  function  get_username  根据用户ID获取用户名
//
// @param   integer  $uid  用户ID
//
// @return  string  用户名
//
function g_get_username($uid=0) {
    static $list;
    if (!($uid && is_numeric($uid))) { //获取当前登录用户名
        return session('user_auth.username');
    }

    // 获取缓存数据
    if (empty($list)) {
        $list = S('sys_active_user_list');
    }

    // 查找用户信息
    $key = "u{$uid}";
    if (isset($list[$key])) { //已缓存，直接使用
        return $list[$key];
    }
    
    //调用接口获取用户信息
    $info = A('User/User', 'Api')->info($uid, 4);
    if (is_array($info) && isset($info['username'])) {
        $name = $list[$key] = $info['username'];

        $count = count($list);
        $max = C('USER_MAX_CACHE');
        while ($count-- > $max) {
            array_shift($list);
        }
        S('sys_active_user_list', $list);
    } else {
        $name = '';
    }
    return $name;
}

//
// @brief   g_get_nickname  根据用户ID获取用户昵称
//
// @param   integer  $uid  用户ID
//
// @return  string  用户昵称
//
function g_get_nickname($uid=0) {
    static $list;
    if (!($uid && is_numeric($uid))) { //获取当前登录用户名
        return session('user_auth.nickname');
    }

    // 获取缓存数据
    if (empty($list)) {
        $list = S('sys_user_nickname_list');
    }

    // 查找用户信息
    $key = "u{$uid}";
    if (isset($list[$key])) { //已缓存，直接使用
        $name = $list[$key];
    }
    
    //调用接口获取用户信息
    $info = M('User')->field('nickname')->find($uid);
    if ($info !== false && $info['nickname']) {
        $nickname = $info['nickname'];
        $name = $list[$key] = $nickname;

        $count = count($list);
        $max = C('USER_MAX_CACHE');
        while ($count-- > $max) {
            array_shift($list);
        }
        S('sys_user_nickname_list', $list);
    } else {
        $name = '';
    }
    return $name;
}

//
// @brief  function  g_data_auth_sign  数据签名认证
//
// @param  array  $data 被认证的数据
//
// @return  string  签名
//
function g_data_auth_sign($data) {
    if(!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

//
// @brief   g_is_login  检测用户是否登录
//
// @return  integer  0-未登录; >0-当前登录用户ID
//
function g_is_login() {
    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    }
    return session('user_auth_sign') == g_data_auth_sign($user) ? $user['uid'] : 0;
}

//
// @brief  function  random  产生随机字符串
//
// @param  integer  $length  字符串长度
// @param  integer  $type    随机串候选集。0=>大小写英文字母和数字，1=>纯数字，2=>数字+大写字母
//
// @return string  长度为$length的随机字符串
//
function g_random($length=6, $type=0) {
    $hash = '';
    $chararr = array(
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz',
        '0123456789',
        '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'
    );
    $chars = $chararr[$type];
    $max = strlen($chars) - 1;
    PHP_VERSION < '4.2.0' && mt_srand((double) microtime() * 1000000);
    for ($i = 0; $i < $length; ++$i) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

