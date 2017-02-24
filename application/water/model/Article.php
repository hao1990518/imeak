<?php
namespace app\water\model;
use think\Db;
use think\Model;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/29
 * Time: 16:39
 */
class Article extends Model
{
    private $_article = 'imk_article';

    /**
     * 获取列表
     * @param $condition   -条件
     * @param int $page    -当前页
     * @param int $count   -数量
     * @param string $order-排序
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getArticleAll($condition,$page=1,$count=15,$order='id desc')
    {
        return Db::table($this->_article)
            ->where($condition)
            ->page($page,$count)
            ->order($order)
            ->select();
    }
}