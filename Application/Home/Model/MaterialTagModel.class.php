<?php
// +----------------------------------------------------------------------
// | Author: Donald Cheung
// +----------------------------------------------------------------------
// | 资料模型层
// +----------------------------------------------------------------------

namespace Home\Model;
use Think\Model;

class MaterialTagModel extends Model {

    protected $_auto = array(
    );

    protected $_validate = array(
        array('tag', '1,64', -1, self::EXISTS_VALIDATE, 'length'),
    );

    //
    // @brief  method  addTags  为指定资料添加标签
    //
    // @param  integer  $mid   资料ID
    // @param  array    $tags  文章标签列表
    //
    // @return  integer  更新成功的数量
    //
    public function addTags($mid, $tags) {
        if (empty($tags)) {
            return 0;
        }

        // TODO 更新的时候，是否删除以前的标签
        $tags_data = array();
        foreach (array_unique(array_map(trim, $tags)) as $tag) {
            if (empty($tag)) {
                continue;
            }
            $tags_data[] = array("mid" => $mid, "tag" => $tag);
        }
        return $this->addAll($tags_data);
    }
}
