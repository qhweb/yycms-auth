<div class="layui-fluid">
  	<div class="layui-row layui-col-space15">
    	<div class="layui-col-md12">
	      	<div class="layui-card">
	      		<div class="layui-card-header">
	      			日志列表 
	      			<button type="button"  post-url="{:url('auth/cache')}" class="layui-btn layui-btn-xs a-post">清除日志缓存</button>
	      			<button type="button" layadmin-event="dialog" data-href="{:url('auth/menuAdd')}" class="layui-btn layui-btn-xs">添加</button>
	      		</div>
				<div class="layui-card-body">	
				    <table class="layui-table" id="menus-table" lay-filter="menus-table">
					    <thead>
					    <tr>
					        <th width="40">排序</th>
					        <th width="50">ID</th>
					        <th>菜单名称</th>
					        <th width="80">应用</th>
					        <th width="80">控制器</th>
					        <th width="80">方法</th>
					        <th width="80">日志请求</th>
					        <th width="80">状态</th>
					        <th width="180">操作</th>
					    </tr>
					    </thead>
					    <tbody>
					        <?php echo $info?>
					    </tbody>
					</table>
				</div> 
			</div>
		</div>
	</div>
</div>
<input type="hidden" value="{:url('auth/menuOrder')}" class="listOrderUrl">

<script>
layui.use(['admin','treeGird'], function(){
	var $ = layui.$,admin = layui.admin,layer = parent.layer === undefined ? layui.layer : top.layer,form = layui.form;

	$('body').on('click','.a-post',function(){
		var posturl = $(this).attr('post-url');
		var msg = $(this).attr('post-msg')||'确定执行该操作吗？';
	    layer.confirm(msg, function(index){
			layer.close(index);
            admin.req({
	        	url:posturl
	        	,type:'post'
	        	,success:function(res){
	        		if(res.code==1){
		                layer.msg(res.msg,{time:1000,icon:1});
		                layui.table.reload('menus-table');
		            }
	        	}
	        });
	        return false;
        });
        return false;
	});
	var order_old='';
	$(".listOrder").on('focus',function(){
        order_old = $(this).val();
    }).on('blur',function(){
		var order = $(this).val();
		var id = $(this).data('id');
		var posturl = $('.listOrderUrl').val();
		
		if(order != order_old){
			admin.req({
	        	url:posturl
	        	,type:'post'
	        	,data:{order:order,id:id}
	        	,success:function(res){
	        		if(res.code==1){
		                layer.msg(res.msg,{time:1000,icon:1});
		            }
	        	}
	       });
		}
   	})
});
</script>


