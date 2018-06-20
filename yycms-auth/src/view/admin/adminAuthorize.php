<div class="layui-fluid">
	<form class="layui-form" action="">
	<ul id="LAY-auth-tree-index" style="border:1px solid #ccc;overflow-y: auto;height:450px;padding-left:20px;padding-top:-3px;"></ul>
	<div class="layui-form-item" style="margin-top:20px;">
	    <div class="layui-input-block">
	    	<input type="hidden" name="id" id="id" value="<?php echo isset($info['id'])?$info['id']:''?>" />
	      	<button class="layui-btn" lay-submit="" lay-filter="dialog" data-action="<?php echo url('') ?>">保存提交</button>
	      	<button type="reset" class="layui-btn layui-btn-primary">重置</button>
	    </div>
	</div>
	</form>
</div>

<script type="text/javascript">
	layui.use(['authtree','admin'],function(obj){
		var element = layui.element,$=layui.$,admin = layui.admin,form = layui.form;
		var authtree = layui.authtree;
		authtree.render(
			{
				elem:'#LAY-auth-tree-index', 
				nodes:[<?php echo isset($info['html'])?$info['html']:''?>],
				url:'<?php echo url('manage/menu')?>', 
				inputname: 'menuid[]', 
				layfilter: 'lay-check-auth', 
				openall: false
			}
		);
	})
</script>