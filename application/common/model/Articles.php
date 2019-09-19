<?php
/**
 * Description: 文章类文件.
 * Author: momo
 * Date: 2019-09-12
 * Copyright: momo
 */

namespace app\common\model;


use app\common\model\ArticleType as TypeModel;

class Articles extends Base
{
    public function getArticleTypeAttr($value)
    {
        $status = [0 => '原创', 1 => '转载', 2 => '翻译'];
        return ['val' => $value, 'text' => $status[$value]];
    }
    public function __construct($data = [])
    {
        $data=[
            'id'=>0,
            'article_title'=>'',
            'type_id'=>0,
            'read_num'=>0,
            'is_publish'=>1,
            'is_top'=>0,
            'article_type'=>0,
            'article_msg'=>'',
            'article_digest'=>'',
            'article_tag'=>'',
            'article_img'=>'',
            'create_time'=>0,
            'update_time'=>0,
            'publish_time'=>0,
            'publish_time'=>0,
        ];
        parent::__construct($data);
    }

    //获取数据列表
    public function artList($where=[])
    {

        $tablename = strtolower(self::getName());
        $data = db($tablename)
            ->where($where)
            ->paginate(15)
            ->each(function ($item, $key) {
                $status = [0 => '原创', 1 => '转载', 2 => '翻译'];
                $item['article_type']=$status[$item['article_type']];
                $typeModel = new TypeModel();
                $typename = $typeModel->oneDetail(['id' => $item['type_id']]);
                $item['type_name'] = $typename['type_name'];
                return $item;
            });
        return $data;
    }
}
