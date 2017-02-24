<?php
namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\Menu;
use think\Cache;
use think\Request;
use think\Session;

/**
 * 首页
 * Class Index
 * @package app\admin\controller
 */
class Index extends Base
{
    public function index()
    {
        $Auth = new \think\Auth;
//        $AuthModel = new Auth();
        $MenuModel = new Menu();
        $AdminModel = new Admin();

        $rules = [];
        if(!Session::get('admin_auth_key')){
            //获取所有权限节点
            if($result = $Auth->getGroups(Session::get(config('user_auth_key')))){
                $title =[];
                foreach ($result as $v){
                    $arr = explode(',',$v['rules']);
                    $rules = array_unique(array_merge($rules,$arr));
                    $title[] = $v['title'];
                }
                $result['title'] = implode(',',$title);
            }
            //节点列表
//            if($result = $AuthModel->getAuthRules(['id'=>array('in',$rules)])){
//                $rules = [];
//                foreach ($result as $v){
//                    $rules[] = $v['name'];
//                }
//            }

            //获取菜单列表
            $menus = $MenuModel->getMenus(['status'=>1,'rule'=>array('in',$rules)],['parent_id'=>0,'url'=>'']);

        }else{
            $result['title'] = '超级管理员';
            $menus = $MenuModel->getMenus(['status'=>1],['parent_id'=>0,'url'=>'']);
        }
        $admin_info = $AdminModel->getAdminInfo(['uid'=>Session::get(config('user_auth_key'))]);
        if(!empty($admin_info['picture'])){
            $h_arr = pathinfo($admin_info['picture']);
            $result['picture_64x64'] = $h_arr['dirname'].'/'.$h_arr['filename'].'-64x64.'.$h_arr['extension'];
            if(!file_exists($result['picture_64x64'])){
                $result['picture_64x64'] ='/static/admin/img/default.jpg';
            }
        }else{
            $result['picture'] ='/static/admin/img/default.jpg';
        }

        $menus = format_tree($menus);

        //获取用户信息
        $this->assign('menus',$menus);
        $this->assign('data',$result);
        return $this->fetch('index');
    }

    public function home()
    {
        return $this->fetch('home');
    }

    public function search()
    {
        return $this->redirect(url('admin/index'));
    }

    /**
     * 清理页面缓存
     */
    public function clearCache()
    {
       if(Cache::clear()){
           $this->clearCaches('temp');
           $this->success('清除成功');
       }else{
           $this->error('清除失败');
       }
    }

    /**
     * 清除缓存 lhb_printf(get_defined_constants(true))-->打印出所有系统自定义常量;
     * @param string $cacheDir 要删除的缓存的目录，目录用"-"隔开 [如Temp-Data-Logs]
     */
   public function clearCaches($cacheDir)
    {
        $type = $cacheDir;
        //将传递过来的值进行切割，我是已“-”进行切割的
        $name = explode('-', $type);
        //得到切割的条数，便于下面循环
        $count = count($name);
        //循环调用上面的方法
        for ($i = 0; $i < $count; $i++)
        {
            //得到文件的绝对路径
//            $abs_dir = dirname(dirname(dirname(dirname(__FILE__))));
            $abs_dir = "";
            //组合路径
            $pa = $abs_dir . str_replace("/", "\\", str_replace("./", "\\", RUNTIME_PATH)); //得到运行时的目录
            $runtime = $pa . 'common~runtime.php';
            if (file_exists($runtime))//判断 文件是否存在
            {
                unlink($runtime); //进行文件删除
            }
            //调用删除文件夹下所有文件的方法
            $this->rmFile($pa, $name[$i]);
        }
    }

    /**
     * 删除文件和目录
     * @param type $path 要删除文件夹路径
     * @param type $fileName 要删除的目录名称
     * @return bool
     */
    private function rmFile($path, $fileName)
    {//删除执行的方法
        //去除空格
        $path = preg_replace('/(\/){2,}|{\\\}{1,}/', '/', $path);
        //得到完整目录
        $path.= $fileName;

        //判断此文件是否为一个文件目录
        if (is_dir($path))
        {

            //打开文件
            if ($dh = opendir($path))
            {
                //遍历文件目录名称
                while (($file = readdir($dh)) != false)
                {
                    $sub_file_path = $path . "\\" . $file;

                    if ("." == $file || ".." == $file)
                    {
                        continue;
                    }
                    if (is_dir($sub_file_path))
                    {

                        $this->rmFile($sub_file_path, "");
                        rmdir($sub_file_path);
                    }

                    //逐一进行删除
                    unlink($sub_file_path);
                }
                //关闭文件
                closedir($dh);
            }
//            rmdir($path);//删除当前目录
        }
    }
}
