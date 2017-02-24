<?php
namespace app\admin\controller;
use app\admin\model\Category;
use think\Request;
use app\admin\model\Article as ArticleModel;
use app\admin\model\Upload;
use think\Session;

/**
 * 文章控制器
 * User: Administrator
 * Date: 2016/8/19
 * Time: 11:35
 */
class Article extends Base
{
    /**
     * 文章列表
     * @return mixed
     */
    public function index()
    {
        if(Request::instance()->isAjax()){
            $this->getList();
        }
        return $this->fetch();
    }

    /**
     * 新增文章
     * @return mixed
     */
    public function add()
    {
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            //分类
            if(empty($data['category_id'])){
                $this->error('请选择栏目');
            }
            $data['category_id'] = implode(',',$data['category_id']);
            //状态
            if(!empty($data['status']) && $data['status']=="on"){
                $data['status'] = 1;
            }else{
                $data['status'] = 0;
            }

            $url = $this->upload();
            if($url){
                $data['picture'] = $url;
            }
            $data['uid'] = Session::get(config('user_auth_key'));
            $data['release_dt'] = strtotime($data['release_dt']);
            $data['update_dt'] = time();
            $data['create_dt'] = time();
            if(ArticleModel::create($data)){
                $this->success('新增成功');
            }else{
                $this->error('新增失败');
            }
        }
        $category_list = Category::all();
        $this->assign('category_list',$category_list);
        return $this->fetch();
    }

    /**
     * 编辑文章
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            //分类
            if(empty($data['category_id'])){
                $this->error('请选择栏目');
            }
            $data['category_id'] = implode(',',$data['category_id']);
            //状态
            if(!empty($data['status']) && $data['status']=="on"){
                $data['status'] = 1;
            }else{
                $data['status'] = 0;
            }
            $url = $this->upload();
            if($url){
                $data['picture'] = $url;
            }else{
                unset($data['picture']);
            }
            $data['release_dt'] = strtotime($data['release_dt']);
            $data['update_dt'] = time();
            $id = $data['id'];
            unset($data['id']);
            if(ArticleModel::update($data,['id'=>$id])){
                $this->success('编辑成功');
            }else{
                $this->error('编辑失败');
            }
        }

        $data = ArticleModel::get(['id'=>$id]);
        $data['category_id'] = explode(',',$data['category_id']);
        $category_list = Category::all();
        $this->assign('data',$data);
        $this->assign('category_list',$category_list);
        return $this->fetch();
    }

    /**
     * 删除文章
     */
    public function delete()
    {
        if(Request::instance()->isPost()){
            $id = Request::instance()->post('id');

            if($id && is_numeric($id) && $id>0){
                $ArticleModel = new ArticleModel();
                if($ArticleModel->deleteArticle(['id'=>$id])){
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
                }
            }else{
                $this->error('非法操作');
            }
        }
    }
    
    /**
     * 获取文章列表
     */
    private function getList(){
        $ArticleModel = new ArticleModel();

        $tableInfo = get_table_info(); //获取dataTable数据
        //排序
        if($tableInfo['sorting'] == 1){
            $order = ('title '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 2){
            $order = ('category_id '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 3){
            $order = ('status '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 3){
            $order = ('uid '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 4){
            $order = ('status '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 5){
            $order = ('sort '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 6){
            $order = ('click_num '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 7){
            $order = ('praise_num '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 8){
            $order = ('update_dt '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 9){
            $order = ('create_dt '.$tableInfo['desc']);
        }else{
            $order = ('id '.$tableInfo['desc']);
        }

        $condition=[];//条件
        //搜索管理员条件组合
        if($key_words = Request::instance()->get('key_words')){
            $condition['title'] = array('LIKE','%'.$key_words.'%');
        }

        //一页显示的条数
        $number = (int)@$tableInfo['length'] > 0 ? $tableInfo['length'] : 25;
        //当前的页数
        $page = (int)@$tableInfo['start'] > 0 ? $tableInfo['start'] : 0;
        //获取文章结果数据
        $data['data'] = $ArticleModel->getArticleList($condition,$page,$number,$order);

        //总数
        $total = $ArticleModel->getArticleCount($condition);

        //组合dataTable数据
        if($data['data']){
            foreach ($data['data'] as $k=>$v){
                $category_list = Category::all(['id'=>array('in',explode(',',$v['category_id']))]);
                $category = [];
                foreach ($category_list as $vv){
                    $category[] = $vv['name'];
                }

                $data['aaData'][$k][] = $v['id'];
                $data['aaData'][$k][] = $v['title'];
                $data['aaData'][$k][] = implode(',',$category);
                $data['aaData'][$k][] = $v['nickname']?$v['nickname']:$v['username'];
                $data['aaData'][$k][] = $v['status']==1?'显示':"<span style='color:red'>隐藏</span>";
                $data['aaData'][$k][] = $v['sort'];
                $data['aaData'][$k][] = $v['click_num'];
                $data['aaData'][$k][] = $v['praise_num'];
                $data['aaData'][$k][] = date('Y-m-d H:i:s',$v['update_dt']);
                $data['aaData'][$k][] = date('Y-m-d H:i:s',$v['create_dt']);
                $data['aaData'][$k][] = "
                    <a href='".url('article/edit',['id'=>$v['id']])."' alt='编辑' title='编辑' style='color:#676A6C'><i class='fa fa-edit'></i></a>
                    <a href='javascript:;' onclick='del(".$v['id'].")' did='".$v['id']."' uname='".$v['title']."' alt='删除' title='删除' style='color:#676A6C;margin-left: 5px;'><i class='fa fa-trash-o'></i></a>
                    ";
//                <a href='#' alt='图片集' title='图片集' style='color:#676A6C;margin-left: 5px;'><i class='fa fa-image'></i></a>

            }
            unset($data['data']);
        }
        $data["sEcho"] = $tableInfo['sEcho'];
        $data["iTotalRecords"] = $total;
        $data["iTotalDisplayRecords"] = $total;
        $data["iDisplayLength"] = $number;
        exit(json_encode($data));
    }

    /**
     * 文件上传
     * @return bool
     */
    private function upload()
    {
        $UploadModel = new Upload();
        $files = Request::instance()->file();
        if($files){
            $thumb = true;
//            if(Request::instance()->post('thumb') && Request::instance()->post('thumb')==1){
//                $thumb = true;//是否生成缩略图
//            };
            // 移动到框架应用根目录/public/uploads/ 目录下
            if(is_array($files)){
                foreach($files as $file){
                    $filePath = $UploadModel->execUpload($file,$thumb);
                }
            }else{
                $filePath = $UploadModel->execUpload($files,$thumb);
            }
            if($filePath['status']==1){
                return $filePath['url'];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}