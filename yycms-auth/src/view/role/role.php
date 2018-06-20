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
        	<a class="layui-btn layui-btn-xs layui-btn-normal " data-type="dialog" data-width="400" data-height="600" data-href="{:url('authorize')}/id/{{d.id}}">权限设置 </a>
        	<a class="layui-btn layui-btn-xs" lay-event="edit">编辑 </a>
        	<a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除 </a>
        {{# } }}
	</div>
</script>
<script>
layui.use('admin', function(){
	var $ = layui.$,admin = layui.admin;
	admin.tableRender({
		table : {
			url: "{:url('roleData')}" //模拟接口
			,page: true
			,height:'full-192'
		    ,cols: [[
		      {field: 'id', title: 'ID', width: 60, sort:true,align:'center'}
		      ,{field: 'name', title: '角色名称', width: 150, edit:true}
		      ,{field: 'remark', title: '角色描述', minWidth: 300, edit:true}
		      ,{field: 'status', title: '状态', width: 100, templet: admin.tpl.status,align:'center'}
		      ,{title: '操作', width: 220, toolbar: "#toolbar",align:'center'}
		    ]]
		}
		,links : {
			
		}
		,dialog:[true,'600','380']
		,tpl:{
			tool:false,
			btn:true,
			search:false,
		}
 	});
});
</script>