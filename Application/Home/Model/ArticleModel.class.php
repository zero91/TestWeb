<?php
// +----------------------------------------------------------------------
// | Author: Donald Cheung
// +----------------------------------------------------------------------
// | 文章模型层
// +----------------------------------------------------------------------

namespace Home\Model;
use Think\Model;

class ArticleModel extends Model {

    protected $_auto = array(
        array('ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

    protected $_validate = array(
        array('author', '1,32', -1, self::EXISTS_VALIDATE, 'length'),
        array('source', '1,512', -2, self::EXISTS_VALIDATE, 'length'),
        array('title', '1,128', -3, self::EXISTS_VALIDATE, 'length'),
    );
}
