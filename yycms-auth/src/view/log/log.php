<div class="layui-fluid">
  	<div class="layui-row layui-col-space15">
      	<div class="layui-card">
      		<div class="layui-card-header">日志列表 </div>
			<div class="layui-card-body" id="view">	
				asdads
			</div> 
		</div>
	</div>
</div>
<script type="text/html" id="toolbar">
    <a data-href="{:url('auth/viewlog')}/id/{{d.id}}" class="layui-btn layui-btn-sm" data-type="dialog" data-width="500" data-height="400">日志详情</a>
</script>
<script>
layui.use('admin', function(){
	var $ = layui.$,admin = layui.admin,layer=layui.layer;
	admin.tableRender({
		table : {
			url: "{:url('logData')}" //模拟接口
			,page: true
			,height:'full-192'
		    ,cols: [[
		      {field: 'id', title: 'ID', width: 60, sort:true,align:'center'}
		      ,{field: 'title', title: '标题', width: 150,align:'center'}
		      ,{field: 'username', title: '用户', width: 100}
		      ,{field: 'log_url', title: '执行地址', align:'center'}
		      ,{field: 'action_ip', title: 'IP', width: 140, align:'center'}
		      ,{field: 'create_time', title: '执行时间', width: 180, align:'center'}
		      ,{title: '操作', width: 120, toolbar: "#toolbar",align:'center'}
		    ]]
		}
		,links : {
			
		}
		,dialog:[true,'600','380']
		,btn:'<button class="layui-btn clear"><i class="layui-icon" aria-hidden="true">&#xe640;</i>清空</button>  &nbsp;'
		,tpl:{
			tool:false,
			btn:false,
			search:true,
		}
	});
	$('body').on('click','.clear',function(){
	    layer.confirm('您确定要清空吗？', function(index){
			layer.close(index);
            var loading = layer.load(1, {shade: [0.1, '#fff']});
            admin.req({
	        	url:"{:url('auth/clear')}"
	        	,type:'post'
	        	,success:function(res){
	        		layer.close(loading);
	        		if(res.code==1){
		                layer.msg(res.msg,{time:1000,icon:1});
		                admin.tableReload();
		            }
	        	}
	        });
            loading ? layer.close(loading) :'';
        });
	});
});
</script>