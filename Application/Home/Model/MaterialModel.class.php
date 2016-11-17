<?php
// +----------------------------------------------------------------------
// | Author: Donald Cheung
// +----------------------------------------------------------------------
// | 资料模型层
// +----------------------------------------------------------------------

namespace Home\Model;
use Think\Model;

class MaterialModel extends Model {

    protected $_auto = array(
        array('ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

    protected $_validate = array(
        array('url', '1,512', -1, self::EXISTS_VALIDATE, 'length'),
        array('password', '0,32', -2, self::EXISTS_VALIDATE, 'length'),
        array('title', '1,64', -3, self::EXISTS_VALIDATE, 'length'),
    );
}
