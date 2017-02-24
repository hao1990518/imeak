<?php
namespace app\admin\validate;
use think\Validate;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/10
 * Time: 16:17
 */
class Admin extends Validate
{
    protected $rule = [
        'username'  =>  'require|max:25|token',
        'password' =>  'require',
        'repassword'=>'require|confirm:password'
    ];

    protected $message = [
        'username.require'  =>  '用户名必须填写',
        'password.require' =>  '密码必须填写',
        'repassword.require' =>  '重复密码必须填写',
        'repassword.confirm' =>  '两次输入密码不一致',
    ];

    protected $scene = [
        'add'   =>  ['username','password','repassword'],
        'edit'  =>  ['username'],
    ];
}