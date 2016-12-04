<?php
// +----------------------------------------------------------------------
// | Author: Donald Cheung
// +----------------------------------------------------------------------
// | 用户标签模型层
// +----------------------------------------------------------------------

namespace Home\Model;
use Think\Model;

class UserTagModel extends Model {

    protected $_auto = array(
    );

    protected $_validate = array(
        array('tag', '1,64', -1, self::EXISTS_VALIDATE, 'length'),
    );

    //
    // @brief  method  addTags  为指定用户添加兴趣标签
    //
    // @param  integer  $uid         用户ID
    // @param  array    $tags        兴趣标签列表
    // @param  boolean  $remove_old  是否删除之前的标签
    //
    // @return  integer  更新成功的数量
    //
    public function addTags($uid, $tags, $remove_old=true) {
        if (empty($tags)) {
            return 0;
        }

        if ($remove_old) {
            $this->where(array('uid' => $uid))->delete();
        }

        $tags_data = array();
        foreach (array_unique(array_map(trim, $tags)) as $tag) {
            if (empty($tag)) {
                continue;
            }
            $tags_data[] = array("uid" => $uid, "tag" => $tag);
        }
        return $this->addAll($tags_data);
    }
}
