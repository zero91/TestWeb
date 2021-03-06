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

//
// @brief  msubstr  字符串截取，支持中文和其他编码
//
// @param  string $str      需要转换的字符串
// @param  string $start    开始位置
// @param  string $length   截取长度
// @param  string $charset  编码格式
// @param  string $suffix   截断显示字符
//
// @return string
//
function g_substr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if (function_exists("mb_substr")) {
        $slice = mb_substr($str, $start, $length, $charset);
    } elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if ($slice == false) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return ($suffix && $slice != $str) ? $slice . '...' : $slice;
}

//
// @brief  function  fetch_img_tag  获取文本中图片链接地址列表
//
// @param  string  $text   文本
//
// @return array  链接地址列表
//
function g_fetch_image_tag($text) {
    preg_match_all('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i', $text, $matches);
    return $matches;
}

//
// @biref function  g_get_image_hander  初始化图片
//
// @return resource  如果成功，则返回资源标识符。失败，则返回false
//
function g_get_image_hander($url) {
    $size = @getimagesize($url);
    switch ($size['mime']) {
        case 'image/bmp' : $im = imagecreatefromwbmp($url); break;
        case 'image/jpeg': $im = imagecreatefromjpeg($url); break;
        case 'image/gif' : $im = imagecreatefromgif($url); break;
        case 'image/png' : $im = imagecreatefrompng($url); break;
        default: $im = false; break;
    }
    return $im;
}

//
// @brief  function  g_crop_image  裁剪图片
//
// @return  string  裁剪后得到的图片
//
function g_crop_image($src, $dst, $x, $y, $w, $h, $rm_src=false) {
    // 创建图片
    $src_pic = g_get_image_hander($src);

    $dst_pic = imagecreatetruecolor($w, $h);
    imagecopyresampled($dst_pic, $src_pic, 0, 0, $x, $y, $w, $h, $w, $h);
    imagejpeg($dst_pic, $dst);
    imagedestroy($src_pic);
    imagedestroy($dst_pic);
        
    // 删除已上传未裁切的图片
    if ($rm_src && file_exists($src)) {
        unlink($src);
    }
    // 返回新图片的位置
    return $dst;
}

//
// @brief  function  g_get_image_type  获取image的类型
//
// @return  string  图片类型，失败则返回false
//
function g_get_image_type($url) {
    $size = @getimagesize($url);
    switch ($size['mime']) {
        case 'image/bmp' : return 'bmp';
        case 'image/jpeg': return 'jpg';
        case 'image/gif' : return 'gif';
        case 'image/png' : return 'png';
        default: return false;
    }
}

function g_resize_image($src, $dst, $width, $height, $crop=0) {
    if (!list($w, $h) = getimagesize($src)) {
        return "Unsupported picture type!";
    }

    $img = g_get_image_hander($src);
    if (!$img) {
        return false;
    }

    // resize
    if ($crop) {
        if ($w < $width or $h < $height) {
            rename($src, $dst);
            return true;
        }
        $ratio = max($width / $w, $height / $h);
        $h = $height / $ratio;
        $x = ($w - $width / $ratio) / 2;
        $w = $width / $ratio;
    } else {
        if ($w < $width and $h < $height) {
            rename($src, $dst);
            return true;
        }
        $ratio = min($width / $w, $height / $h);
        $width = $w * $ratio;
        $height = $h * $ratio;
        $x = 0;
    }
    $new = imagecreatetruecolor($width, $height);

    $type = g_get_image_type($src);
    // preserve transparency
    if ($type == "gif" or $type == "png") {
        imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
        imagealphablending($new, false);
        imagesavealpha($new, true);
    }

    imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);
    switch ($type) {
        case 'bmp': imagewbmp($new, $dst); break;
        case 'gif': imagegif($new, $dst); break;
        case 'jpg': imagejpeg($new, $dst); break;
        case 'png': imagepng($new, $dst); break;
    }
    return true;
}
