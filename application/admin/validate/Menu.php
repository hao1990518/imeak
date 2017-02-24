<?php
namespace app\admin\validate;
use think\Validate;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/10
 * Time: 16:17
 */
class Menu extends Validate
{
    protected $rule = [
        'name'  =>  'require|max:50|token',
        'parent_id'=>'number',
        'status'=>'number',
        'rule'=>'number',
        'sort'=>'number'
    ];

    protected $message = [
        'name.require'      =>  '名称必须填写',
        'parent_id.number'  =>  '父级菜单有误',
        'status.number'      =>  '状态有误',
        'rule.number'      =>  '关联节点有误',
        'sort.number'      =>  '排序必须为数字',
    ];
}