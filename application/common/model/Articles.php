<?php
/**
 * Description: 文章类文件.
 * Author: momo
 * Date: 2019-09-12
 * Copyright: momo
 */

namespace app\common\model;


class Articles extends Base
{
    public function __construct($data = [])
    {
        $data=[
            'id'=>0,
            'article_title'=>'',
            'type_id'=>0,
            'read_num'=>0,
            'is_publish'=>0,
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
}
