layui.define(['layer'],function(exports){
	var $ = layui.$
	,LAY_BODY ="YY_BODY"
	,layer=layui.layer
	,$win=$(window)
	,$body = $('body');
  var Admin = {
  	v:'1.0.1'
    ,page_title:'控制台'
    ,correctRouter: function(href){
      if(!/^\//.test(href)) href = '/' + href;
      //纠正首尾
      return href.replace(/^(\/+)/, '/').replace(new RegExp('\/index$'), '/');
    }
    //屏幕类型
    ,screen: function(){
      var width = $win.width()
      if(width >= 1200){
        return 3; //大屏幕
      } else if(width >= 992){
        return 2; //中屏幕
      } else if(width >= 768){
        return 1; //小屏幕
      } else {
        return 0; //超小屏幕
      }
    }
    //侧边伸缩
    ,sideFlexible: function(status){
    	console.log(status);
    }
    ,view:function(pathUrl){
    	return $("#"+LAY_BODY).html(pathUrl);
    }
  	//构造器
  	,render:function(id){
  	    return new Class(id);
  	}
  	,pageType:''
    ,success:function(msg,fun){
      layer.msg(msg,{icon:1,time:1000},function(res){
        typeof fun === 'function' && fun(res);
      })
    }
    ,error:function(msg){
      layer.alert(msg,{icon:2})
    }
    //加载中
    ,loading:function(elem){
      elem.append(
        this.elemLoad = $('<i class="layui-anim layui-anim-rotate layui-anim-loop layui-icon layui-icon-loading"></i>')
      );
    }
    //移除加载
    ,removeLoad:function(){
      this.elemLoad && this.elemLoad.remove();
    }
    //Ajax请求
    ,req : function(options){
      var that = this
      ,success = options.success
      ,error = options.error
      options.data = options.data || {};
      options.headers = options.headers || {};
      
      // if(request.tokenName){
      //   //自动给参数传入默认 token
      //   options.data[request.tokenName] = request.tokenName in options.data 
      //     ?  options.data[request.tokenName]
      //   : (layui.data(setter.tableName)[request.tokenName] || '');
        
      //   //自动给 Request Headers 传入 token
      //   options.headers[request.tokenName] = request.tokenName in options.headers 
      //     ?  options.headers[request.tokenName]
      //   : (layui.data(setter.tableName)[request.tokenName] || '');
      // }

      delete options.success;
      delete options.error;

      return $.ajax($.extend({
        type: 'get'
        ,dataType: 'json'
        ,success: function(res){
          //只有 response 的 code 一切正常才执行 done
          if(res.code ==1) {
            typeof options.done === 'function' && options.done(res); 
          } 
          
          //登录状态失效，清除本地 access_token，并强制跳转到登入页
          else if(res.code == 1001){
            location.href = res.url
          }
          
          //其它异常
          else {
            if(res['url']){
              layer.msg(res.msg,{icon:2,time:1000,function(){
                location.hash = res.url;
              }})
            }else{
              Admin.error(res.msg);
            }
            
          }
          
          //只要 http 状态码正常，无论 response 的 code 是否正常都执行 success
          typeof success === 'function' && success(res);
        }
        ,error: function(e, code){
          var error = '请求异常，请重试<br><cite>错误信息：</cite>'+ code ;
          Admin.error(error);
          typeof error === 'function' && error(res);
        }
      }, options));
    }
  }
  //构造器
	,Class=function(id){
	    this.id = id;
	    this.container = $('#'+(id || LAY_BODY));
	};
  //请求模板文件渲染
  Class.prototype.render = function(views, params){
    var that = this, router = layui.router();
    views =  views + '.html';
    $('#'+ LAY_BODY).children('.layadmin-loading').remove();
    Admin.loading(that.container); //loading
    
    //请求模板
    $.ajax({
      url: views
      ,type: 'get'
      // ,dataType: 'json'
      ,data: {
        v: layui.cache.version
      }
      ,success: function(html){
        if (html.code == 0) {
          location.href = html.url;
        };
        html = '<div>' + html + '</div>';
        var elemTitle = $(html).find('title')
        ,title = elemTitle.text();
        var res = {
          title: title
          ,body: html
        };
        
        elemTitle.remove();
        that.params = params || {}; //获取参数
        
        if(that.then){
          that.then(res);
          delete that.then; 
        }
        
       
        that.container.html(html);
        that.settitle(title);
        Admin.removeLoad();
        
        if(that.done){
          that.done(res);
          delete that.done; 
        }
        
      }
      ,error: function(e){
        Admin.removeLoad();
        
        if(that.render.isError){
          return Admin.error('请求视图文件异常，状态：'+ e.status)
        };
        
        if(e.status === 404){
          that.render(layui.cache.base+'template/404');
        } else {
          that.render(layui.cache.base+'template/error');
        }
        
        that.render.isError = true;
      }
    });
    return that;
  };
  //视图请求成功后的标题变更
  Class.prototype.settitle = function(title){
    title = title ? title : Admin.page_title
    $("#YY_HEADER").find('cite').text(title);
  };
  //视图请求成功后的回调
  Class.prototype.then = function(callback){
    this.then = callback;
    return this;
  };
  
  //视图渲染完毕后的回调
  Class.prototype.done = function(callback){
    this.done = callback;
    return this;
  };
  //事件
  var events = Admin.events = {

  };
  //点击事件
  $body.on('click', '*[layadmin-event]', function(){
    var othis = $(this)
    ,attrEvent = othis.attr('layadmin-event');
    events[attrEvent] && events[attrEvent].call(this, othis);
  });
  //页面跳转
  $body.on('click', '*[lay-href]', function(){
    var othis = $(this)
    ,href = othis.attr('lay-href')
    ,title = othis.text()
    ,router = layui.router();
    Admin.page_title = title;
    //执行跳转
    location.hash = Admin.correctRouter(href);
  });
  exports('admin', Admin);
}); 
//纠正路由格式
    