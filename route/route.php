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

Route::get('hello/:name', 'index/hello');


/**
 * 登陆
 */
Route::group('admin',[
    'auth'=>['admin/Adminauth/Login',['method' => 'get']],
    'dologin'=>['admin/Adminauth/doLogin',['method' => 'post']],
    'loginOut'=>['admin/Adminauth/loginOut'],
]);


/**
 * 后台管理相关
 */
Route::group('admin',[
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

    //文章分类管理
    'types/list'   => ['admin/Types/typeList', ['method' => 'get']],
    'types/test'   => ['admin/Types/test', ['method' => 'get']],
    'types/add/[:id]' => ['admin/Types/addType', ['method' => 'get']],
    'types/doAdd' => ['admin/Types/doAddType', ['method' => 'post']],
    'types/delete/:id' => ['admin/Types/delType', ['method' => 'get']],

]);



return [

];
