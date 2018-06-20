<div class="layui-fluid">
  	<div class="layui-row layui-col-space15">
    	<div class="layui-col-md12">
	      	<div class="layui-card">
	      		<div class="layui-card-header">角色管理 </div>
				<div class="layui-card-body" id="view">
	
				</div> 
			</div>
		</div>
	</div>
</div>
<script type="text/html" id="toolbar">
	<div class="layui-btn-group" id="authtool">
		{{# if(d.id == 1){ }}
			
        {{# }else{ }}
        	<a class="layui-btn layui-btn-xs layui-btn-normal " data-type="dialog" data-width="400" data-height="600" data-href="{:url('auth/adminAuthorize')}/id/{{d.id}}/name/{{d.user_name}}">独立权限 </a>
        	<a class="layui-btn layui-btn-xs" lay-event="edit">编辑 </a>
        	<a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除 </a>
        {{# } }}
	</div>
</script>

<script>
layui.use('admin', function(){
	var $ = layui.$,admin = layui.admin;
	var statusTpl = function(d){
		checked = d.user_status == 1 ? 'checked' : '';
	    return '<input type="checkbox" name="user_status" value="'+d.id+'" lay-skin="switch" lay-text="启用|禁用" lay-filter="status" '+checked+'>';
	}
	admin.tableRender({
		table : {
			url: "{:url('adminUserData')}" //模拟接口
			,page: true
			,height:'full-192'
		    ,cols: [[
		      {type: 'checkbox', fixed: 'left'}
		      ,{field: 'id', title: 'ID', width: 60, sort:true,align:'center'}
		      ,{field: 'user_name', title: '用户名', width: 100}
		      ,{field: 'user_nicename', title: '昵称',edit:true}
		      ,{field: 'user_email', title: '邮箱', width: 180, edit:true}
		      ,{field: 'last_login_ip', title: '最后登录IP', width: 140}
		      ,{field: 'last_login_time', title: '最后登录时间', width: 180}
		      ,{field: 'user_status', title: '状态', width: 100, templet: statusTpl,align:'center'}
		      ,{title: '操作', width: 160, toolbar: "#toolbar",align:'center'}
		    ]]
		}
		,links : {
			
		}
		,dialog:[true,'680','380']
		,tpl:{
			tool:false,
			btn:true,
			search:false,
		}
 	});
});
</script>