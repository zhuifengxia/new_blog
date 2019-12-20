<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});



/**
 * 后台管理相关
 */
Route::group('admin',[
    //登录相关
    'auth'=>['admin/AdminAuth/Login',['method' => 'get']],
    'dologin'=>['admin/AdminAuth/doLogin',['method' => 'post']],
    'loginOut'=>['admin/AdminAuth/loginOut'],

    //首页
    'index'=>['admin/Admin/index',['method' => 'get']],
    //用户管理
    'users/list'   => ['admin/Users/userList', ['method' => 'get']],
    'users/add/[:id]' => ['admin/Users/addUser', ['method' => 'get']],
    'users/doAdd' => ['admin/Users/doAddUser', ['method' => 'post']],
    'users/delete/:id' => ['admin/Users/delUser', ['method' => 'get']],
    //微语管理
    'gossips/list'   => ['admin/Gossips/msgList', ['method' => 'get']],
    'gossips/add/[:id]' => ['admin/Gossips/addMsg', ['method' => 'get']],
    'gossips/doAdd' => ['admin/Gossips/doAddMsg', ['method' => 'post']],
    'gossips/delete/:id' => ['admin/Gossips/delMsg', ['method' => 'get']],

    //照片管理
    'album/list'   => ['admin/Album/msgList', ['method' => 'get']],
    'album/add/[:id]' => ['admin/Album/addMsg', ['method' => 'get']],
    'album/doAdd' => ['admin/Album/doAddMsg', ['method' => 'post']],
    'album/delete/:id' => ['admin/Album/delMsg', ['method' => 'get']],

    //文章分类管理
    'types/list'   => ['admin/Types/typeList', ['method' => 'get']],
    'types/add/[:id]' => ['admin/Types/addType', ['method' => 'get']],
    'types/doAdd' => ['admin/Types/doAddType', ['method' => 'post']],
    'types/delete/:id' => ['admin/Types/delType', ['method' => 'get']],

    //文章管理
    'articles/list'   => ['admin/Articles/artList', ['method' => 'get']],
    'articles/add/[:id]' => ['admin/Articles/addArt', ['method' => 'get']],
    'articles/doAdd' => ['admin/Articles/doAddArt', ['method' => 'post']],
    'articles/delete/:id' => ['admin/Articles/delArt', ['method' => 'get']],

    //评论管理
    'posting/list/[:typeid]'   => ['admin/Posting/postList', ['method' => 'get']],
    'posting/delete/:id' => ['admin/Posting/delPost', ['method' => 'get']],

]);

/**
 * 前台管理相关
 */
Route::group('/',[
    //首页
    'index/[:typeid]'=>['index/Index/index',['method' => 'get']],
    //文章详情
    'article/:id'=>['index/Index/artDetails',['method' => 'get']],
    'artnext'=>['index/Index/artNext',['method' => 'post']],
    'artPost'=>['index/Index/artPost',['method' => 'post']],
    'posting'=>['index/Index/artPostSubmit',['method' => 'post']],
    'whisper'=>['index/Index/whisper',['method' => 'get']],
    'whisper/next'=>['index/Index/whisperNext',['method' => 'post']],

    'message'=>['index/Index/msgList',['method' => 'get|post']],

    'album'=>['index/Index/albumList',['method' => 'get|post']],
    'about'=>['index/Index/about',['method' => 'get|post']],
]);

/**
 * 小程序接口相关
 */
Route::group('api',[
    //首页
    'index'=>['api/Index/index',['method' => 'get|post']],
    'login'=>['api/Index/login',['method' => 'post']],
    'article'=>['api/Index/artDetail',['method' => 'post']],
    'dolike'=>['api/Index/artLike',['method' => 'post']],
    'comment'=>['api/Index/writeComm',['method' => 'post']],
    'types'=>['api/Index/artType',['method' => 'post']],
    'mydata/:type'=>['api/Index/myData',['method' => 'post']],
    //小程序apiv2
    'v2/banner'=>['api/Articles/bannerData',['method' => 'get']],
    'v2/whisper'=>['api/Articles/whisperData',['method' => 'get']],
    'v2/articles'=>['api/Articles/articleData',['method' => 'get']],

]);


return [

];
