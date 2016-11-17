<?php
namespace Home\Controller;

class IndexController extends HomeController {
    public function index() {
        $latest_article_list = D('Article')->field(true)
                                           ->order('create_time DESC')
                                           ->limit(0, 5)
                                           ->select();
        // TODO 推荐功能
        //$recommend_article = D('Article')->where(array('id' => 44))->find();
        $recommend_article = D('Article')->where(array('id' => 68))->find();
        $image_tags = g_fetch_image_tag($recommend_article['content']);
        if (count($image_tags[2]) > 0) {
            $recommend_article['image'] = $image_tags[2][0];
        } else {
            $recommend_article['image'] = C('DEFAULT_ARTICLE_IMAGE');
        }

        foreach ($latest_article_list as &$article) {
            $image_tags = g_fetch_image_tag($article['content']);
            if (count($image_tags[2]) > 0) {
                $article['image'] = $image_tags[2][0];
            } else {
                $article['image'] = C('DEFAULT_ARTICLE_IMAGE');
            }
        }

        $recommend_article['content'] = g_substr(strip_tags($recommend_article['content']), 0, 400);

        $this->assign('latest_article_list', $latest_article_list);
        $this->assign('recommend_article', $recommend_article);

        $this->display();
    }
}
