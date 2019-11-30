<?php

/**
 * Description: 后台数据文章管理相关操作.
 * Author: momo
 * Date: 2019-09-16
 * Copyright: momo
 */
namespace app\admin\controller;
use app\common\model\Articletype as TypeModel;
use app\common\model\Articles as ArtModel;
use MoCommon\Support\UploadFiles;

class Articles extends Base
{
    /**
     * 文章列表数据
     */
    public function artList()
    {
        $artModel = new ArtModel();
        $articles = $artModel->dataList(ArtModel::class, [],1);
        $articles = $artModel->artData($articles);
        $this->assign('articles', $articles);
        return $this->fetch('articles');
    }

    /**
     * 添加/编辑文章信息
     */
    public function addArt($id=0)
    {
        $artModel = new ArtModel();
        if ($id) {
            //获取编辑的详情数据信息
            $detail = $artModel->oneDetail(ArtModel::class,['id' => $id]);
        } else {
            $detail = $artModel->toArray();
        }
        //获取文章分类列表数据
        $typeModel=new TypeModel();
        $types=$typeModel->dataList(TypeModel::class);
        $this->assign("details", $detail);
        $this->assign("types", $types);
        return $this->fetch();
    }

    /**
     * 文章信息保存
     */
    public function doAddArt()
    {
        $artModel = new ArtModel();
        //是否存在
        $id = input('id', 0);
        $article_title = input('article_title', '');
        $is_top = input('is_top', 0);
        $is_publish = input('is_publish', 1);
        $article_img = $_FILES['article_img'];
        if(empty($article_img)){
            $article_img=input("article_img1","");
        }
        $insert = $_POST;
        $insert['is_top']=$is_top;
        $insert['is_publish']=$is_publish;
        $where = [
            ['article_title', '=', $article_title],
        ];
        if ($id) {
            $where[] = ['id', '<>', $id];
        }
        $data = $artModel->oneDetail(ArtModel::class,$where);
        if ($data) {
            $this->error('已经存在，请重新输入', '/admin/articles/add/' . $id);
        } else {
            if ($article_img['tmp_name']) {
                //有文件上传
                $filepath = env('root_path') . 'public/static/upload/articles/' . date('Ymd') . "/";
                $fileresult = UploadFiles::single_file_upload("", $article_img, $filepath, '');
                if ($fileresult['status'] != 0 && !empty($fileresult['data'])) {
                    $this->error($fileresult['msg']);
                }
                $fileurl = $fileresult['data'];
                $insert['article_img'] = '/static/upload/articles/' . date('Ymd') . '/' . $fileurl;
            }
            if ($id) {
                $data = $artModel->oneDetail(ArtModel::class,['id' => $id]);
                //查看原来的信息是否有图片，有的话删除原来的图片
                if ($data['article_img'] && $article_img['tmp_name']) {
                    //删除原来的图片文件
                    @unlink(env('root_path') . "public" . $data['article_img']);
                }

                $artModel->updateOne(ArtModel::class,$insert, ['id' => $id]);
            } else {
                $artModel->addOne(ArtModel::class,$insert);
            }
            $this->success('保存成功', '/admin/articles/list');
        }
    }

    /**
     * 删除文章
     * @param $id 文章id
     */
    public function delArt($id)
    {
        $artModel = new ArtModel();
        $data = $artModel->oneDetail(ArtModel::class,['id' => $id]);
        $artModel->deleteOne(ArtModel::class,['id' => $id]);
        //删除图片文件信息
        if ($data['article_img']) {
            //删除原来的图片文件
            @unlink(env('root_path') . "public" . $data['article_img']);
        }
        $this->success('删除成功', '/admin/articles/list');
    }
}
