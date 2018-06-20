<style>
	/*图标管理*/
	.icons{ margin:0 0px;overflow: hidden;padding: 0;}
	.icons div{  margin:5px 0; text-align:center; height:120px; cursor:pointer;}
	.icons div i{ display:block; font-size:35px; margin:10px 0; line-height:60px; height:60px;}
	.icons div:hover{ background:rgba(13,10,49,.9); border-radius:5px; color:#fff;}
	.icons div:hover i{ font-size:50px;}
	#copyText{ width:0;height:0; opacity:0; position:absolute; left:-9999px; top:-9999px;}
</style>
<div class="layui-fluid">
	<blockquote class="layui-elem-quote">
		<p>【点击可复制】此页面并非后台模版需要的，只是为了让大家了解都引入了哪些外部图标，实际应用中可删除。</p>
	    <p>弹窗选择图标的时候，自动填充</p>
	</blockquote>
	<div class="icons" id="wb"></div>
	<textarea id="copyText"></textarea>
</div> 		
<script> 
layui.use('admin',function(){
    var form = layui.form,
        layer = parent.layer === undefined ? layui.layer : top.layer,
        element = layui.element;
        $ = layui.jquery,
        admin = layui.admin;
	var iconUrl = '/static/layui/css/layui.css';
    $.get(iconUrl,function(data){
        var iconHtml = '';
        for(var i=1;i<data.split(".layui-icon-").length;i++){
            iconHtml += "<div class='layui-col-md2'>"+
                            "<i class='layui-icon layui-icon-" + data.split(".layui-icon-")[i].split(":before")[0] + "'></i>" +
                            "" + data.split('.layui-icon-')[i].split(':before')[0] +
                        "</div>";
        }
        $("#wb").html(iconHtml);
        $(".wiconsLength").text(data.split(".layui-icon-").length-1);
    })
//  $.get('/public/static/common/css/font.css',function(data){
//      var iconHtml = '';
//      for(var i=1;i<data.split(".icon-").length;i++){
//          iconHtml += "<li class='layui-col-xs4 layui-col-sm3 layui-col-md2 layui-col-lg1'>"+
//                          "<i class='icon icon-" + data.split(".icon-")[i].split(":before")[0] + "'></i>" +
//                          "icon icon-" + data.split('.icon-')[i].split(':before')[0] +
//                      "</li>";
//      }
//      $("#nb").html(iconHtml);
//      $(".niconsLength").text(data.split(".icon-").length-1);
//  })

    $("body").on("click",".icons div",function(){
       var index = admin.popup.index;
       var $css = $(this).find('i').attr('class');
        if (index) {
        	//给父页面传值
			parent.layui.$('input[name="icon"]').val($css);
		    parent.layer.close(index);
        }else{
        	var copyText = document.getElementById("copyText");
	        copyText.innerText = $css;
	        copyText.select();
	        document.execCommand("copy");
        	layer.msg("复制成功",{anim: 2});
        }
        
    })
})


</script>