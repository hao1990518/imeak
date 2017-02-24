<?php
namespace app\admin\model;
use think\Db;
use think\Model;

/**
 * 文章管理模型
 * User: Administrator
 * Date: 2016/8/19
 * Time: 16:12
 */

class Article extends Model
{
    private $_article = 'imk_article';

    /**
     * 获取管理员（列表）
     * @param array $condition -条件
     * @param int $start       -起始位置
     * @param int $count       -显示数量
     * @param string $order    -排序
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getArticleList($condition=array(),$start=1,$count=10,$order="id desc")
    {
        return Db::table($this->_article)
            ->field('imk_article.*,imk_admin.username,imk_admin_info.nickname')
            ->join('imk_admin','imk_admin.id = imk_article.uid','left')
            ->join('imk_admin_info','imk_admin_info.uid = imk_article.uid','left')
            ->where($condition)
            ->order($order)
            ->limit($start,$count)
            ->select();
    }

    /**
     * 获取文章数量
     * @param $condition
     * @return int
     */
    public function getArticleCount($condition)
    {
        return Db::table($this->_article)->where($condition)->count();
    }

    /**
     * 删除文章
     * @param $condition
     * @return int
     */
    public function deleteArticle($condition)
    {
        return Db::table($this->_article)->where($condition)->delete();
    }
}