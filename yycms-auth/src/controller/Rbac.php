<?php


namespace yycms\auth\controller;


use think\Cache;
use think\Config;
use think\Validate;
use yycms\auth\library\Tree;
use yycms\auth\model\ActionLog;
use yycms\auth\model\AuthAccess;
use yycms\auth\model\AuthRole;
use yycms\auth\model\AuthRoleUser;
use yycms\auth\model\Menu;
use yycms\auth\model\AdminUser;
class Rbac
{

    public $menuValidate    = ['name|名称'=>'require' , 'app|应用'=>'require' , 'model|控制器'=>'require' , 'action|方法'=>'require'];
    public $roleValidate    = ['name|角色名称'  => 'require'];
    private $id;
    public function __construct($request)
    {
        $this->request  = $request;
        $this->param    = $this->request->param();
        $this->post     = $this->request->post();
        $this->id       = isset($this->param['id'])?intval($this->param['id']):'';
        $this->data     = ['pach'=>VIEW_PATH];
    }

    /**
     * 菜单and权限列表
     */
    public function menu()
    {
        $result     = Menu::where('')->order(["list_order" => "asc",'id'=>'asc'])->column('*','id');
        $tree       = new Tree();
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        foreach ($result as $n=> $r) {
            $result[$n]['level'] = $tree->get_level($r['id'], $result);
            $result[$n]['parent_id_node'] = ($r['parent_id']) ? ' class="child-of-node-' . $r['parent_id'] . '"' : '';

            $result[$n]['str_manage'] = checkAuthPath('auth/menuAdd',["parent_id" => $r['id']]) ? '<button class="layui-btn layui-btn-primary layui-btn-xs"  layadmin-event="dialog" data-href="'.url("auth/menuAdd",["parent_id" => $r['id']]).'">添加子菜单</button>':'';
            $result[$n]['str_manage'] .= checkAuthPath('auth/menuEdit',["id" => $r['id']]) ?'<button class="layui-btn layui-btn-primary layui-btn-xs" layadmin-event="dialog" data-href="'.url("auth/menuEdit",["id" => $r['id']]).'">编辑</button>':'';
            $result[$n]['str_manage'] .= checkAuthPath('auth/menuDelete',["id" => $r['id']]) ?'<button class="layui-btn layui-btn-primary layui-btn-xs a-post" post-msg="你确定要删除吗" post-url="'.url("auth/menuDelete",["id" => $r['id']]).'" href="javascript:;">删除</button>':'';
            $result[$n]['status'] = $r['status'] ? '开启' : '隐藏';
            $result[$n]['icon'] = $r['icon'] ? '<i class="layui-icon icon '.$r['icon'].'"></i>' : '';
        }
        $str = "<tr id='node-\$id' \$parent_id_node>
                    <td align='center'>
                        <input name='listorders[\$id]' type='text' size='3' value='\$list_order' data-id='\$id' class='listOrder' style='text-align:center'>
                    </td>
                    <td align='center'>\$id</td>
                    <td>\$spacer  \$icon \$name</td>
                    <td align='center'>\$app</td>
                    <td align='center'>\$model</td>
                    <td align='center'>\$action</td>
                    <td align='center'>\$request</td>
                    <td align='center'>\$status</td>
                    <td align='center'>\$str_manage</td>
                </tr>";

        $tree->init($result);
        $info = $tree->get_tree(0, $str);
        return [VIEW_PATH.'menu/menu.php',array_merge($this->data,['info'=>$info])];
    }
	/**
     * 菜单and权限数据json
     */
    public function menujson(){
    	$result     = Menu::where('')->order(["list_order" => "asc",'id'=>'asc'])->column('*','id');
        $tree       = new Tree();
        $result = $tree->ArrayTree($result);
        foreach ($result as $k =>$v){
        	$top[] = [
        		'title'=> $v['name'],
        		'href' => '/'. $v['app'] .'/'. $v['model'] .'/'. $v['action'],
        		'icon' => $v['icon']
        	];
        	if($v['children']){
        		$sider[] = $this->getlist($v['children']);
        	}
        	
        }
        return ['code'=>1,'msg'=>'获取成功','data'=>$sider,'top'=>$top];
    }
    public function getlist($arr){
    	$child = [];
    	foreach ($arr as $k => $v){
    		$child[] = [
    			'open' => $k ==0 ? true : false,
        		'title'=> $v['name'],
        		'href' => '/'. $v['app'] .'/'. $v['model'] .'/'. $v['action'],
        		'icon' => $v['icon'],
        		'list' => $this->getlist($v['children'])
        	];
    	}
    	return $child;
    }
    public function menuData(){
    	$result     = Menu::where('')->order(["list_order" => "asc",'id'=>'asc'])->column('*','id');
        $tree       = new Tree();
        $result = $tree->ArrayTree($result);
        return ['code'=>1,'msg'=>'获取成功','data'=>$result];
    }
    /**
     * 菜单and权限 修改
     */
    public function menuEdit()
    {

        $post   = $this->post;
        $info   = Menu::get($this->id);

        if(empty($info)){
            return false;
        }

        if($this->request->isPost()){

            $validate = new Validate($this->menuValidate);

            if (!$validate->check($post)) {
                return ['code'=>0,'msg'=>$validate->getError()];
            }

            if($info->allowField(true)->isUpdate(true)->save($post)){
                return ['code'=>1,'msg'=>'修改成功','url'=>url('auth/menu')];
            }else{
                return ['code'=>0,'msg'=>'修改失败'];
            }
        }

        $info['selectCategorys'] = menu($info['parent_id']);
        return [VIEW_PATH.'menu/menuForm.php',array_merge($this->data,['info'=>$info])];
    }

    /**
     * 菜单and权限 增加
     */
    public function menuAdd()
    {
        $parent_id  = isset($this->param['parent_id'])?$this->param['parent_id']:'';
        if($this->request->isPost()){
            $post   = $this->post;
            $validate = new Validate($this->menuValidate);
			
			if (!$post = access_token($post)) {
                return ['code'=>0,'msg'=>'access_token匹配不成功，增加失败'];
            }
            
            if (!$validate->check($post)) {
                return ['code'=>0,'msg'=>$validate->getError()];
            }
            
            if(Menu::create($post)){
                return ['code'=>1,'msg'=>'增加成功','url'=>url('auth/menu')];
            }else{
                return ['code'=>0,'msg'=>'增加失败'];
            }
        }

        $info['selectCategorys']  = menu($parent_id);
        return [VIEW_PATH.'menu/menuForm.php',array_merge($this->data,['info'=>$info])];
    }

    /**
     * 菜单and权限 删除
     */
    public function menuDelete()
    {
        if($this->request->isPost()){
            $result   = Menu::get($this->id);

            if(empty($result)){
                return ['code'=>0,'msg'=>'没有数据'];
            }else if(Menu::where(['parent_id'=>$result['id']])->find()){
                return ['code'=>0,'msg'=>'有子目录不可删除'];
            };

            if($result->delete()){
                return ['code'=>1,'msg'=>'删除成功','url'=>url('auth/menu')];
            }else{
                return ['code'=>0,'msg'=>'删除失败'];
            }
        }
        return ['code'=>0,'msg'=>'请求方式错误'];
    }

    /**
     * 菜单 排序
     */
    public function menuOrder()
    {
        if($this->request->isPost()) {
            $order  = isset($this->param['order'])?intval($this->param['order']):'';
            $result = Menu::get($this->id);

            if(empty($result)){
                return ['code'=>0,'msg'=>'没有数据'];
            }else if ($result) {
                if ($result->save(['list_order' => $order])) {
                    return ['code' => 1, 'msg' => '数据已更新'];
                }
            }

            return ['code'=>0,'msg'=>'数据无变化'];
        }
        return ['code'=>0,'msg'=>'请求方式错误'];
    }
    /**
     * 角色列表
     */
    public function role(){
        return [VIEW_PATH.'role/role.php',''];
    }
	/**
     * 角色列表
     */
    public function roleData(){
        $data = AuthRole::all();
        return ['code'=>0,'msg'=>'获取成功','data'=>$data];
    }
    private static function roleSelect($roleid = ''){
        $roleid = explode(',',$roleid);

        $role = AuthRole::column('name','id');
        $html = '';
        foreach($role as $k=>$v){
            $selected = in_array($k, $roleid)?'selected':'';

            $html   .= ' <option '.$selected.' value="'.$k.'">'.$v.'</option>';
        }

        return $html;
    }
    /**
     * 角色字段修改
     */
    public function roleFieldUpdate()
    {
    	$AuthRole = new AuthRole();
        //post 数据处理
        if($this->request->isPost()){
        	$post   = $this->post;
			if (!$post = access_token($post)) {
                return ['code'=>0,'msg'=>'access_token匹配不成功，增加失败'];
          	}
            if($AuthRole->allowField(true)->isUpdate(true)->save($post)){
                return ['code'=>1,'msg'=>'修改成功','url'=>url('auth/role')];
            }else{
                return ['code'=>0,'msg'=>'修改失败'];
            }
        }

    }
    /**
     * 角色修改
     */
    public function roleEdit()
    {
    	$info   = AuthRole::get($this->id);
        if(empty($info)){
            return false;
        }
        //post 数据处理
        if($this->request->isPost()){
        	$post   = $this->post;
			$post['status'] = isset($post['status']) ? 1 : 0;
			
			if (!$post = access_token($post)) {
                return ['code'=>0,'msg'=>'access_token匹配不成功，增加失败'];
            }
            
            $validate = new Validate($this->roleValidate);

            if (!$validate->check($post)) {
                return ['code'=>0,'msg'=>$validate->getError()];
            }

            if($info->save($post)){
                return ['code'=>1,'msg'=>'修改成功','url'=>url('auth/role')];
            }else{
                return ['code'=>0,'msg'=>'修改失败'];
            }
        }
        
    	
        return [VIEW_PATH.'role/roleForm.php',array_merge($this->data,['info'=>$info])];
    }

    /**
     * 角色增加
     */
    public function roleAdd()
    {

        //post 数据处理
        if($this->request->isPost()){
            $post   = $this->post;
			$post['status'] = isset($post['status']) ? 1 : 0;

			if (!$post = access_token($post)) {
                return ['code'=>0,'msg'=>'access_token匹配不成功，增加失败'];
            }
            
            //现在数据
            $validate = new Validate($this->roleValidate);
            if (!$validate->check($post)) {
                return ['code'=>0,'msg'=>$validate->getError()];
            }

            if(AuthRole::create($post)){
                return ['code'=>1,'msg'=>'增加成功','url'=>url('auth/role')];
            }else{
                return ['code'=>0,'msg'=>'增加失败'];
            }
        }
        return [VIEW_PATH.'role/roleForm.php',$this->data];
    }

    public function roleDel()
    {
        if($this->request->isPost()){
            $result   = AuthRole::get($this->id);

            if($this->id==1){
                return ['code'=>0,'msg'=>'超级管理员不可删除'];
            }else if(empty($result)){
                return ['code'=>0,'msg'=>'没有数据'];
            }
            $delete = $result->authRoleDelete();
            if(is_string($delete)){
                return ['code'=>0,'msg'=>$delete];
            }else if($delete === true){
                return ['code'=>1,'msg'=>'删除成功','url'=>url('auth/role')];
            }else{
                return ['code'=>0,'msg'=>'删除失败'];
            }
        }
        return ['code'=>0,'msg'=>'请求方式错误'];
    }
    /**
     * 角色授权
     */
    public function authorize()
    {


        $menu       = Menu::where('')->order(["list_order" => "asc",'id'=>'asc'])->column('*','id');

        if($this->request->isPost()){//表单处理

            $post   = $this->post;
            $menuid = isset($post['menuid']) ? $post['menuid'] : [];;

            if(empty($this->id)){
                return ['code'=>0,'msg'=>'需要授权的角色不存在'];
            }

            AuthAccess::where(["role_id" => $this->id,'type'=>'admin_url'])->delete();

            if (is_array($menuid) && count($menuid)>0) {
                foreach ($menuid as $v) {

                    $menus   = isset($menu[$v])?$menu[$v]:'';

                    if($menus){
                        $name   = strtolower("{$menus['app']}/{$menus['model']}/{$menus['action']}");
                        $data[]   = [
                            "role_id"   => $this->id,
                            "rule_name" => $name,
                            'type'      => 'admin_url',
                            'menu_id'   => $v
                        ];
                    }
                }

                if(!empty($data)){
                    $AuthAccess = new AuthAccess();
                    if($AuthAccess->saveAll($data)){
                        return ['code'=>1,'msg'=>'增加成功','url'=>url('auth/role')];
                    }else{
                        return ['code'=>0,'msg'=>'增加失败'];
                    }
                }

            }else{
                return ['code'=>0,'msg'=>'没有接收到数据，执行清除授权成功！'];
            }
        }//表单处理结束

        if(empty($this->id)){
            return false;
        }
        $info = self::authorizeHtml($menu,'admin_url');

        return [VIEW_PATH.'admin/authorize.php',array_merge($this->data,['info'=>$info])];
    }
    /**
     *  管理员授权
     */
    public function adminAuthorize()
    {
        $menu       = Menu::where('')->order(["list_order" => "asc",'id'=>'asc'])->column('*','id');

        if($this->request->isPost()){//表单处理

            $post   = $this->post;
            $menuid = isset($post['menuid']) ? $post['menuid'] : [];

            if(empty($this->id)){
                return ['code'=>0,'msg'=>'需要授权的角色不存在'];
            }

            AuthAccess::where(["role_id" => $this->id,'type'=>'admin'])->delete();

            if (is_array($menuid) && count($menuid)>0) {
                foreach ($menuid as $v) {

                    $menus   = isset($menu[$v])?$menu[$v]:'';

                    if($menus){
                        $name   = strtolower("{$menus['app']}/{$menus['model']}/{$menus['action']}");
                        $data[]   = [
                            "role_id"   => $this->id,
                            "rule_name" => $name,
                            'type'      => 'admin',
                            'menu_id'   => $v
                        ];
                    }
                }

                if(!empty($data)){
                    $AuthAccess = new AuthAccess();
                    if($AuthAccess->saveAll($data)){
                        return ['code'=>1,'msg'=>'增加成功','url'=>''];
                    }else{
                        return ['code'=>0,'msg'=>'增加失败'];
                    }
                }

            }else{
                return ['code'=>0,'msg'=>'没有接收到数据，执行清除授权成功！'];
            }
        }//表单处理结束

        if(empty($this->id)){
            return false;
        }

        //管理员所有角色权限
        $roleId     = AuthRoleUser::innerAuthRole($this->id);
        if(in_array(1,$roleId)){
            $AuthAccess = true;
        }else if(empty($roleId)){
            $AuthAccess = [];
        }else{
            $AuthAccess   = AuthAccess::where(["role_id"=>["in",$roleId]])->column('*','menu_id');
        }


        $info = self::authorizeHtml($menu,'admin',$AuthAccess);

        return [VIEW_PATH.'admin/adminAuthorize.php',array_merge($this->data,['info'=>$info])];
    }

    /**
     * 注册样式文件
     */
    public function openFile()
    {

        $text       = '';
        $file       = strtr($this->param['file'], '_', DS);
        $extension  = substr(strrchr($file, '.'), 1);

        switch ($extension)
        {
            case 'css':
                $text = 'text/css';
                break;
            case 'js':
                $text = 'text/js';
                break;
            default:
                return false;
        }

        $pach = VIEW_PATH.'../static/'.$file;
        $file = file_get_contents($pach);

        return ['file'=>response($file, 200, ['Content-Length' => strlen($file)])->contentType($text)];
    }

    /**
     * 日志首页
     */
    public function log(){
        return [VIEW_PATH.'log/log.php',''];
    }
	/**
     * 日志列表
     */
    public function logData(){
        $where  = [];
         if($this->request->isPost()){//表单处理
         	
	        if(!empty($this->post['key'])){
	            $where['title|username|user_id']  = ['like','%'.$this->post['key'].'%'];
	        }

			$page = $this->post['page'];
			$limit = $this->post['limit'];
	        $list   = ActionLog::where($where)->limit($limit)->page($page)->order('id desc')->select();
	        $count = ActionLog::count();
	        return ['code'=>0,'msg'=>'获取成功','data'=>$list,'count'=>$count];
	    }else{
	    	return ['code'=>1,'msg'=>'获取失败'];
	    }
    }
    /**
     * 日志详情
     */
    public function viewLog()
    {
        $info   = ActionLog::get($this->id);
        return [VIEW_PATH.'log/viewLog.php',array_merge($this->data,['info'=>$info])];
    }

    /**
     * 清空日志
     */
    public function clear()
    {
    	if($this->request->isPost()){//表单处理
	        if(ActionLog::where('1=1')->delete()){
	            return ['code'=>1,'msg'=>'数据已清空','url'=>url('auth/log')];
	        }
	        return ['code'=>0,'msg'=>'操作失败'];
     	}
     	return ['code'=>0,'msg'=>'操作失败'];
    }

    /**
     * 清除缓存
     */
    public function cache()
    {
        Cache::rm('logMenu');
        return ['code'=>1,'msg'=>'操作成功','url'=>url('auth/menu')];
    }

    protected function authorizeHtml($menu,$type,$authMenu=[])
    {
        $priv_data  = AuthAccess::where(['role_id'=>$this->id,'type'=>$type])->field("rule_name")->column('menu_id');
        $tree       = new Tree();
        foreach ($menu as $n => $t) {
            $menu[$n]['checked']  = (in_array($t['id'], $priv_data)) ? ' checked' : '';
            $menu[$n]['level']    = $tree->get_level($t['id'], $menu);
            $menu[$n]['width']    = 100-$menu[$n]['level'];
            $menu[$n]['disabled'] = isset($authMenu[$t['id']])||$authMenu===true?[0=>"style='display: none;'disabled=''",1=>'★']:[0=>'',
                1=>''];

        }
        $info['html']   = json_encode($tree->ArrayTree($menu));
        $info['id']     = $this->id;
        return $info;
    }
    //管理员管理首页
    public function adminUser(){
        return [VIEW_PATH.'admin/adminUser.php',''];
    }
    //管理员数据获取
    public function adminUserData(){
    	$arr = [];
       	$list = AdminUser::paginate(15);
       	foreach($list as $k => $v){
       		$arr[] = $v;
       	}
       	return ['code'=>0,'msg'=>'操作成功','data'=>$arr,'count'=>count($arr)];
    }
    //管理员字段更新
    public function adminUserfieldUpdate(){
    	$AdminUser = new AdminUser();
        //post 数据处理
        if($this->request->isPost()){
        	$post   = $this->post;
			if (!$post = access_token($post)) {
                return ['code'=>0,'msg'=>'access_token匹配不成功，增加失败'];
          	}
            if($AdminUser->allowField(true)->isUpdate(true)->save($post)){
                return ['code'=>1,'msg'=>'修改成功','url'=>url('auth/adminUser')];
            }else{
                return ['code'=>0,'msg'=>'修改失败'];
            }
        }
    }
    /**
     * 管理员修改
     */
    public function adminUserEdit()
    {
    	$info   = AdminUser::get($this->id);
        if(empty($info)){
            return false;
        }
        //post 数据处理
        if($this->request->isPost()){
        	$post   = $this->post;
			$post['user_status'] = isset($post['user_status']) ? 1 : 0;
			
			if (!$post = access_token($post)) {
                return ['code'=>0,'msg'=>'access_token匹配不成功，增加失败'];
            }
            
            $validate = new Validate([
                ['user_email|邮箱','require|email'],
                ['role|角色','require'],
            ]);
			
            if (!$validate->check($post)) {
                return ['code'=>0,'msg'=>$validate->getError()];
            }
            
			$role           = $post['role'];
            $data = array(
                'user_email'    => $post['user_email'],
                'role'          => implode(',',$role),
                'user_status'   => $post['user_status'],
                'user_nicename'   => $post['user_nicename']
            );
            
			//判断是否修改密码
            $password = $post['user_password'];
            
            if(!empty($password)){
                $data['user_password'] = md5($password);
            }
            if($info->save($data)){
            	//加入角色
                $authRoleUser = new AuthRoleUser();
                $authRoleUser->authRoleUserAdd($role,$this->id);
                return ['code'=>1,'msg'=>'修改成功','url'=>url('auth/AdminUser')];
            }else{
                return ['code'=>0,'msg'=>'修改失败'];
            }
        }
        
    	$info['role'] = self::roleSelect($info['role']);
        return [VIEW_PATH.'admin/adminUserForm.php',array_merge($this->data,['info'=>$info])];
    }

    /**
     * 管理员增加
     */
    public function AdminUserAdd()
    {

        //post 数据处理
        if($this->request->isPost()){
            $post   = $this->post;
			$post['user_status'] = isset($post['user_status']) ? 1 : 0;

			if (!$post = access_token($post)) {
                return ['code'=>0,'msg'=>'access_token匹配不成功，增加失败'];
            }
            
            //现在数据
            $validate = new Validate([
                ['user_name|用户名','require|unique:admin_user,user_name,'.$post['user_name'].',id'],
                ['user_email|邮箱','require|email'],
                ['user_password|密码','require'],
                ['role|角色','require'],
            ]);
            if (!$validate->check($post)) {
                return ['code'=>0,'msg'=>$validate->getError()];
            }
			$role           = $post['role'];
            $post['user_password']  = md5(input($post['user_password']));
            $post['create_time']    = date("Y-m-d H:i:s");
            $post['role']           = implode(',',$role);
            $insert = AdminUser::create($post);//增加
            if($insert){
                //加入角色
                $authRoleUser = new AuthRoleUser();
                $authRoleUser->authRoleUserAdd($role,$insert['id']);
                
                return ['code'=>1,'msg'=>'增加成功','url'=>url('auth/AdminUser')];
            }else{
                return ['code'=>0,'msg'=>'增加失败'];
            }
        }
        $info['role'] = self::roleSelect();
        return [VIEW_PATH.'admin/adminUserForm.php',array_merge($this->data,['info'=>$info])];
    }
	/**
	 * 管理员删除 
	 */
    public function adminUserDel()
    {
    	$ids = $this->id ? $this->id : (isset($this->post['id']) ? $this->post['id'] : $this->post['ids']);
		$ids = (array)$ids;
    	if($ids){
    		$where['id'] = array('IN',implode(',',$ids));
    	} else{
    		return ['code'=>0,'msg'=>'参数错误'];
    	}
        if(!$this->request->isAjax() && !$this->request->isPost()){
            return abort(404, lang('404 denied access'));
        }else if(in_array(1,$ids)){
        	return ['code'=>0,'msg'=>'超级管理员不能删除'];
        }

        if(AdminUser::where($where)->delete()){

            //删除角色权限
            $authRoleUser = new AuthRoleUser();
            foreach($ids as $k =>$v){
            	$authRoleUser->authRoleUserDelete($v);
            }
            
			return ['code'=>1,'msg'=>'删除成功'];
        }else{
            return ['code'=>0,'msg'=>'删除失败'];
        }
        return ['code'=>0,'msg'=>'请求方式错误'];
    }
     /**
     * 系统图标选择
     */
    public function icon(){
        return [VIEW_PATH.'icon.php',''];
    }
}

/**
 * 所有后台菜单
 * @param int   $selected       默认id
 * @return mixed
 */
function menu($selected = 1)
{
    $array = '';
    $result = Menu::where('')->order(["list_order" => "asc",'id'=>'asc'])->column('*','id');

    $tree = new Tree();
    foreach ($result as $r) {
        $r['selected'] = $r['id'] == $selected ? 'selected' : '';
        $array[] = $r;
    }
    $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
    $tree->init($array);
    $parentid = isset($where['parentid'])?$where['parentid']:0;

    return $tree->get_tree($parentid, $str);
}