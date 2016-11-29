<?php
// +----------------------------------------------------------------------
// | Author: Donald Cheung
// +----------------------------------------------------------------------
// | 资料操作相关控制类
// +----------------------------------------------------------------------

namespace Home\Controller;

class MaterialController extends HomeController {

    public function index() {
        $material_list = D('Material')->field(true)
                                      ->order('create_time DESC')
                                      ->limit(0, 20)
                                      ->select();

        $this->assign('material_list', $material_list);
        $this->display();
    }

    public function add() {
        //TODO 判断用户是否登录，若未登录，跳转到登录界面
        $this->display();
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    //
    // @brief  method  ajaxAdd  添加资料
    //
    // @request  type  POST
    //
    // @param  string  $title     资料主题
    // @param  string  $url       访问链接
    // @param  string  $password  访问密码
    // @param  array   $tags      资料标签
    // @param  string  $reason    推荐理由
    //
    // @ajaxReturn  成功 => array("success" => true, "id" => 新增资料ID号, 'forward' => 跳转链接)
    //              失败 => array("success" => false, "error" => 错误码, "msg" => 错误提示信息)
    //
    // @error  101  用户尚未登录
    // @error  102  发生未知错误
    // @error  103  url长度过长
    // @error  104  密码长度过长
    // @error  105  资料主题过长
    // @error  106  发生未知错误
    //
    public function ajaxAdd($title='', $url='', $password='', $tags='', $reason='') {
        $uid = g_is_login();
        if ($uid == 0) {
            $this->ajaxReturn(array("success" => false, "error" => 101, "msg" => "您尚未登录"));
        }

        $data = array(
            'uid' => $uid,
            'title' => $title,
            'url' => $url,
            'password' => $password,
            'reason' => $reason
        );
        if (empty($data['password'])) { unset($data['password']); }

        if (D('Material')->create($data)) {
            $id = D('Material')->add();
            if ($id > 0) {
                D('MaterialTag')->addTags($id, $tags);
                $this->ajaxReturn(array("success" => true, "id" => $id,
                                    'forward' => U('Material/index')));
                
            } else {
                $this->ajaxReturn(array("success" => false, "error" => 102,
                                        "msg" => "发生未知错误"));
            }
        } else {
            $res = array("success" => false);
            switch (D('Article')->getError()) {
            case -1:
                $res['error'] = 103;
                $res['msg'] = 'url长度不超过512个字符';
                break;
            case -2:
                $res['error'] = 104;
                $res['msg'] = '密码过长，不超过32个字符';
                break;
            case -3:
                $res['error'] = 105;
                $res['msg'] = '资料主题过长，不超过64个字符';
                break;
            default:
                $res['error'] = 106;
                $res['msg'] = '发生未知错误';
                break;
            }
            $this->ajaxReturn($res);
        }
    }
}
