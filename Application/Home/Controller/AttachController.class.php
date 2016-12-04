<?php
// +----------------------------------------------------------------------
// | Author: Donald Cheung
// | Date: 2016-10-31
// +----------------------------------------------------------------------
// | 上传文件等操作类
// +----------------------------------------------------------------------

namespace Home\Controller;

class AttachController extends HomeController {

    //
    // @brief method  uploadImage  ueditor  编辑器上传图片处理
    //
    public function uploadImage() {
        $img_info = $this->upload(C('EDITOR_UPLOAD'));
        $res = array(
            'url'      => $img_info['fullpath'],
            'title'    => htmlspecialchars($_POST['pictitle'], ENT_QUOTES),
            'original' => $img_info[C('EDITOR_UPLOAD.fieldKey')]['name'],
            'state'    => $img_info ? 'SUCCESS' : session('upload_error')
        );
        $this->ajaxReturn($res);
    }

    //
    // @brief method  uploadFile  ueditor编辑器上传附件处理
    //
    public function uploadFile() {
        $file_info = $this->upload(C('DOWNLOAD_UPLOAD'));
        $res = array(
            'url'      => $file_info['fullpath'],
            'title'    => htmlspecialchars($_POST['pictitle'], ENT_QUOTES),
            'original' => $file_info[C('DOWNLOAD_UPLOAD.fieldKey')]['name'],
            'state'    => $file_info ? 'SUCCESS' : session('upload_error')
        );
        $this->ajaxReturn($res);
    }

    //
    // @brief  method  upload  根据传入的配置，上传并保存文件，返回上传文件的信息
    //
    // @param  array  $setting  文件上传类的基本配置，包括路径、文件大小限制、后缀名等显示
    //
    // @return  array  文件保存路径等信息
    //
    private function upload($setting) {
        session('upload_error', null);
        $uploader = new \Think\Upload($setting, 'Local');
        $info = $uploader->upload($_FILES);
        if ($info) {
            $key = $setting['fieldKey'];
            $url = $setting['rootPath'] . $info[$key]['savepath'] . $info[$key]['savename'];
            $url = str_replace('./', '/', $url);
            $info['fullpath'] = __ROOT__ . $url;
        }
        session('upload_error', $uploader->getError());
        return $info;
    }

    //
    // @brief  method uploadAvatar  用户上传头像
    //
    // @return  array  头像保存地址、宽度、高度、标题等信息
    //
    public function uploadAvatar() {
        $img_info = $this->upload(C('AVATAR_UPLOAD'));
        $image_size = getimagesize(WEB_ROOT . $img_info['fullpath']);
        $res = array(
            'url'      => $img_info['fullpath'],
            'title'    => htmlspecialchars($_POST['pictitle'], ENT_QUOTES),
            'original' => $img_info[C('AVATAR_UPLOAD.fieldKey')]['name'],
            'width'  => $image_size[0],
            'height' => $image_size[1],
            'state'    => $img_info ? 'SUCCESS' : session('upload_error')
        );
        $this->ajaxReturn($res);
    }
}
