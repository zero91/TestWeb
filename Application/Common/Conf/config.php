<?php
return array(
    'DB_TYPE'            =>    'mysql',
    'DB_HOST'            =>    '127.0.0.1',
    'DB_NAME'            =>    'jiehuozhe', //需要新建一个数据库！名字叫
    'DB_USER'            =>    'jiehuozhe', //数据库用户名    
    'DB_PWD'             =>    'jiehuozhe', //数据库登录密码
    'DB_PORT'            =>    '3306',
    'DB_PREFIX'          =>    'jhz_',        //数据库表名前缀

    'PRODUCT_NAME'       =>    '解惑者', // 产品显示名称
    'PRODUCT_OWNER'      =>    'admin@jiehuozhe.com', // 产品OWNER邮件

    'USER_AUTH_KEY'      =>    'authId',
    'USER_ALLOW_REGISTER' =>   true,
    'USER_MAX_CACHE'     =>    1000, //最大缓存用户数

    'USER_AVATAR_UPLOAD_PATH' => '/Uploads/Avatar/',      // 头像上传存放地址，使用服务器绝对地址
    'USER_AVATAR_IMG_TYPE'    => array(".jpg", ".jpeg", ".gif", ".png"), // 头像允许的图片类型
    'USER_DEFAULT_AVATAR'  => '/Public/images/default-avatar.png', //默认头像，使用服务器绝对地址
);
?>
