layui.define(['element','admin'], function(exports){
  	var element = layui.element
  		,$=layui.$
  		,admin = layui.admin
  		,APP_BODY = "YY_BODY";
  	
	//入口页面
	var entryPage = function(fn){
	    var container = admin.render(APP_BODY)
	    ,router = layui.router()
    	,path = router.path;
    	//默认读取主页
	    if(!path.length) path = [''];
	    
	    //如果最后一项为空字符，则读取默认文件
	    if(path[path.length - 1] === ''){
	      	path[path.length - 1] = '/admin/index/main';
	    }
	    pathURL = admin.correctRouter(path.join('/'));
      	container.render(pathURL).done(function(){
      		renderPage();
	        layui.element.render(); 
	        if(admin.screen() < 2){
	            admin.sideFlexible();
	        }
	        

	    }); 
	}
	,renderPage = function(){
		$("#"+APP_BODY).scrollTop(0);
		var elemTemp = $('.layui-side').find('a');
        elemTemp.removeClass('layui-this');
        for(var i = elemTemp.length; i > 0; i--){
    		var dataElem = elemTemp.eq(i - 1);
    		url = dataElem.attr('lay-href');
    		if (url == pathURL) {
    			dataElem.addClass('layui-this');
    		};
        }
	}
	//初始主体结构
	layui.link(
	    layui.cache.base+'skin/default.css?v='+ (admin.v + '-1'),function(){
	      	entryPage()
	    },'yycmsAdmin'
	);
	//监听Hash改变
	window.onhashchange = function(){
	    entryPage();
	};
	exports('yycms',{})
});