<?php
//配置文件
return [
    //权限配置
    'auth_config' => array(
        'auth_on'           => true,                      // 认证开关
        'auth_type'         => 1,                         // 认证方式，1为实时认证；2为登录认证。
        'auth_group'        => 'auth_group',        // 用户组数据表名
        'auth_group_access' => 'auth_group_access', // 用户-用户组关系表
        'auth_rule'         => 'auth_rule',         // 权限规则表
        'auth_user'         => 'admin',             // 用户信息表
    ),
    'super_admin' => 'admin',  //超级管理员,对应用户表中某一个用户名-username
    'admin_auth_key' => false,  // 超级管理员识别
    'user_auth_key' => 'authid',//认证识别号 自定义
    'not_auth_dir'  => array(),//默认无需认证目录array("public","manage")
    'not_auth_module' => array('login','index'),//无需认证的模块
    'not_auth_action' => array('login_out'),//无需认证的操作
    'check_password' => 696254, //验证口令

//    'upload_path' => ROOT_PATH . 'public' . DS . 'uploads', //上传路径
    'upload_path' => './uploads', //上传路径
];