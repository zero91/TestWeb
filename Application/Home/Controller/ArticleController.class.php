<?php
// +----------------------------------------------------------------------
// | Author: Donald Cheung
// +----------------------------------------------------------------------
// | 文章操作相关控制类
// +----------------------------------------------------------------------

namespace Home\Controller;

class ArticleController extends HomeController {
    public function index() {
        $this->display();
    }

    public function view($id) {
        $article = D('Article')->where(array("id" => $id))->find();

        $this->assign("article_title", $article['title']);
        $this->assign("article_content", $article["content"]);

        $this->display();
    }

    public function post() {
        $this->assign('title', '发表文章');
        $this->display();
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================
 
    //
    // @brief  method  ajaxPost  发表文章
    // @request  POST
    //
    // @param  string  $title     文章标题
    // @param  string  $content   文章内容
    // @param  string  $captcha   验证码
    // @param  string  $tags      文章标签
    // @param  string  $source    文章来源，默认为空
    // @param  string  $author    文章作者，默认为空
    //
    // @ajaxReturn   成功 - array("success" => true, "id" => 新增文章ID号)
    //               失败 - array("success" => false, "error" => 错误码, "msg" => 错误提示信息)
    //
    // @error  101  验证码错误
    // @error  102  用户尚未登录
    // @error  103  数据库操作失败，提示用户发生未知错误
    // @error  104  原文作者用户名太长
    // @error  105  原文来源地址过长
    // @error  106  文章标题过长
    // @error  107  未知其它错误
    //
    public function ajaxPost($title, $content, $captcha, $tags, $source="", $author="") {
        if (!g_check_captcha($captcha, 1, true)) {
            $this->ajaxReturn(array("success" => false, "error" => 101, "msg" => "验证码错误"));
        }

        $uid = g_is_login();
        if ($uid == 0) {
            $this->ajaxReturn(array("success" => false, "error" => 102, "msg" => "您尚未登录"));
        }

        $data = array(
            "uid" => $uid,
            "nickname" => g_get_nickname(),
            "author" => $author,
            "source" => $source,
            "title" => $title,
            "content" => $content,
        );

        if (empty($author)) { unset($data['author']); }
        if (empty($source)) { unset($data['source']); }

        if (D('Article')->create($data)) {
            $id = D('Article')->add();
            if ($id > 0) {
                D('ArticleTag')->addTags($id, $tags);
                $this->ajaxReturn(array("success" => true, "id" => $id,
                                'forward' => U('Article/view?id=' . $id)));
            } else {
                $this->ajaxReturn(array("success" => false, "error" => 103,
                                        "msg" => "发生未知错误"));
            }
        } else {
            $res = array("success" => false);
            switch (D('Article')->getError()) {
            case -1:
                $res['error'] = 104;
                $res['msg'] = '原文作者用户名过长，不超过32个字符';
                break;
            case -2:
                $res['error'] = 105;
                $res['msg'] = '原文来源地址过长，不超过512个字符';
                break;
            case -3:
                $res['error'] = 106;
                $res['msg'] = '文章标题过长，不超过128个字符';
                break;
            default:
                $res['error'] = 107;
                $res['msg'] = '发生未知错误';
                break;
            }
            $this->ajaxReturn($res);
        }
    }

    //
    // @brief  method  ajaxPost  发表文章
    // @request  POST
    //
    // @param  string  $title     文章标题
    // @param  string  $content   文章内容
    // @param  string  $captcha   验证码
    // @param  string  $source    文章来源，默认为空
    // @param  string  $author    文章作者，默认为空
    //
    // @ajaxReturn   成功 - array("success" => true, "id" => 新增文章ID号)
    //               失败 - array("success" => false, "error" => 错误码, "msg" => 错误提示信息)
    //
    // @error  101  验证码错误
    // @error  102  用户尚未登录
    // @error  103  数据库操作失败，提示用户发生未知错误
    // @error  104  原文作者用户名太长
    // @error  105  原文来源地址过长
    // @error  106  文章标题过长
    // @error  107  未知其它错误
    //
    public function ajaxFetch($id) {
        $article = D('Article')->where(array("id" => $id))->find();
        if (is_array($article)) {
            $this->ajaxReturn(array_merge(array("success" => true), $article));
        } else {
            $this->ajaxReturn(array("success" => false, "error" => 101));
        }
    }
}

