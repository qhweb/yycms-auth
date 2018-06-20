<?php
$status  = isset($info['status'])?$info['status']:'';
$type    = isset($info['type'])?$info['type']:'';
?>
<div class="layui-fluid">
	<form class="layui-form" action="">
	  	
		<div class="layui-form-item layui-form-text">
		    <label class="layui-form-label">状态</label>
		    <div class="layui-input-inline">
		      	<input type="radio" name="status" <?php echo empty($status)|$status==1?'checked':''?> value="1" checked title="显示">
                <input type="radio" name="status" <?php echo $status === 0?'checked':''?>   value="0" title="隐藏"> 
		    </div>
		</div>
		<div class="layui-form-item layui-form-text">
		    <label class="layui-form-label">类型</label>
		    <div class="layui-input-inline">
		      	<input type="radio" name="type" <?php echo empty($type)|$type==1?'checked':''?> value="1" checked title="权限认证+菜单">
                <input type="radio" name="type" <?php echo $type === 0?'checked':''?>   value="0" title="只作为菜单"> 
		    </div>
		</div>
		<div class="layui-form-item">
		    <label class="layui-form-label">上级</label>
		    <div class="layui-input-inline">
		      	<select class="form-control text" name="parent_id">
                    <option value="0">/</option>
                    <?php echo isset($info['selectCategorys'])?$info['selectCategorys']:'';?>
                </select>
		    </div>
		    <label class="layui-form-label">名称</label>
		    <div class="layui-input-inline">
		      <input type="text" name="name" lay-verify="required" autocomplete="off" placeholder="请输入名称" class="layui-input" value="<?php echo isset($info['name'])?$info['name']:''?>">
		    </div>
	  	</div>
	  	<div class="layui-form-item">
		    <label class="layui-form-label">应用</label>
		    <div class="layui-input-inline">
		      <input type="text" name="app" lay-verify="required" autocomplete="off" placeholder="请输入应用名称" class="layui-input" value="<?php echo isset($info['app'])?$info['app']:''?>">
		    </div>

		    <label class="layui-form-label">控制器</label>
		    <div class="layui-input-inline">
		      <input type="text" name="model" lay-verify="required" autocomplete="off" placeholder="请输入控制器名称" class="layui-input" value="<?php echo isset($info['model'])?$info['model']:''?>">
		    </div>
	  	</div>
	  	<div class="layui-form-item">
		    <label class="layui-form-label">方法</label>
		    <div class="layui-input-block">
		      <input type="text" name="action" lay-verify="required" autocomplete="off" placeholder="请输入名称" class="layui-input" value="<?php echo isset($info['action'])?$info['action']:''?>">
		    </div>
	  	</div>
	  	<div class="layui-form-item">
		    <label class="layui-form-label">验证规则</label>
		    <div class="layui-input-inline">
		      <input type="text" name="rule_param"  autocomplete="off" placeholder="请输入验证规则 " class="layui-input" value="<?php echo isset($info['rule_param'])?$info['rule_param']:''?>">
		    </div>
		    <div class="layui-form-mid layui-word-aux">例:{id}==3 and {cid}==3</div>
	  	</div>
	  	
	  	<div class="layui-form-item">
		    <label class="layui-form-label">图标</label>
		    <div class="layui-input-inline">
		      	<input type="text" name="icon" autocomplete="off" placeholder="请选择图标" class="layui-input" value="<?php echo isset($info['icon'])?$info['icon']:''?>">
		    </div>
		    <div class="layui-input-inline">
		    	<button type="button" class="layui-btn layui-btn-danger" layadmin-event="dialog" data-width="800" data-height="700" data-href='{:url("icon")}/input/icon'>选择图标</button>
		    </div>
	  	</div>
		<div class="layui-form-item">
		    <label class="layui-form-label">日志类型</label>
		    <div class="layui-input-block">
		      	<select class="form-control text" name="request">
                    <option value="">关闭</option>
                    <?php
                        $type       = ['GET','POST','PUT','PUT','DELETE','Ajax'];
                        $request   = isset($info['request'])?$info['request']:'';
                        foreach($type as $v){
                            $selected = $request == $v ?'selected':'';
                            echo '<option '.$selected.' value="'.$v.'">'.$v.'</option>';
                        }
                    ?>
                </select>
		    </div>
		</div>
		<div class="layui-form-item layui-form-text">
		    <label class="layui-form-label">日志说明</label>
		    <div class="layui-input-block">
		      	<textarea name="log_rule"  class="layui-textarea" placeholder="请输入日志请求类型{id},{name}"><?php echo isset($info['log_rule'])?$info['log_rule']:''?></textarea>
		    </div>
		</div>
		<div class="layui-form-item layui-form-text">
		    <label class="layui-form-label">备注</label>
		    <div class="layui-input-block">
		      	<textarea name="remark"  class="layui-textarea" placeholder="请输入备注"><?php echo isset($info['remark'])?$info['remark']:''?></textarea>
		    </div>
		</div>
		<div class="layui-form-item">
		    <div class="layui-input-block">
		    	<input type="hidden" name="id" id="id" value="<?php echo isset($info['id'])?$info['id']:''?>" />
		      <button class="layui-btn" lay-submit="" lay-filter="dialog" data-action="<?php echo url('') ?>">保存提交</button>
		      <button type="reset" class="layui-btn layui-btn-primary">重置</button>
		    </div>
		</div>
	</form>
</div>
<script>
	layui.use('set', layui.factory('set'));
</script>