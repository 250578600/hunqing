function kokoAPI(config,ajax_url){
	$this=this;
	if(typeof config == 'undefined'){
		config={};
	}
	var tools=new kokoTools();
	function init(){
		for(var func in config){
			if(typeof $this[func] == 'function'){
				$this[func](config[func]);
			}else{
				console.log("没有找到"+func+"方法");
			}
		}
	}
	function getObjFun(conf,obj){
		if(typeof obj =='undefined'){
			var obj=$("."+conf.class);
			obj.css('cursor','pointer');
		}
		obj.getKey=function(key,msg){
			$this=$(this);
			if(typeof $this.attr(key) !='undefined'){
				return $this.attr(key);
			}else if(typeof conf[key] !='undefined'){
				return conf[key];
			}else {
				if(typeof msg !='undefined'){
					layer.alert("need "+key);
				}
				return null;
			}
		}
		return obj;
	}
	function getObj(conf,obj){
		var obj=getObjFun(conf,obj);
		obj.callback=function (para){
			var callback=getObjFun($(this)).getKey('callback');
			if(typeof window[callback]=='function'){
				return window[callback](para);
			}
			return false;
		}
		return obj;
	}
	function setDef(conf,key,def){
		if(typeof conf[key] == 'undefined'){
			conf[key]=def;
		}
	}
	this.switcher=function (conf){
		setDef(conf,'class','kokoSwitch');
		var obj=getObj(conf);
		obj.click(function(){
			var a=getObj(conf,$(this));
			var on=a.getKey('on',"need on");
			var off=a.getKey('off');
			var ajax=a.getKey('ajax',"need ajax");
			var key=a.getKey('key',"need key");
			var id=a.getKey('id',"");
			var url=a.getKey('url');
			var opposite=a.getKey('opposite');
			var no_post=a.getKey('no-post');
			if(url==null&&ajax_url==null){
				layer.alert("need url");
			}else if(url==null){
				url=ajax_url;
			}
			function post(state,call){
				$.post(url,{ajax:ajax,key:key,id:id,value:state==(opposite==null?'on':'off')?1:0},function(e){
					if(e.state==1){
						call();
					}else{
						layer.msg(e.msg);
					}
					a.callback({state:state,result:e});
				});
			}
			function onFun(){
				post('on',function(){
					a.addClass(on);
					if(off){
						a.removeClass(off);
					}
					var h=a.getKey('html-on');
					if(h){
						a.html(h);
					}
				});
				if(no_post){
					a.callback({state:'on'});
				}
			}
			function offFun(){
				post('off',function(){
					a.removeClass(on);
					if(off){
						a.addClass(off);
					}
					var h=a.getKey('html-off');
					if(h){
						a.html(h);
					}
				});
				if(no_post){
					a.callback({state:'off'});
				}	
			}
			function func(){
				if(a.hasClass(on)){
					if(a.getKey('confirm-off')){
						layer.confirm({
							title:a.getKey('confirm-off'),
							yes:offFun
						});
					}else{
						offFun();
					}
				}else{
					if(a.getKey('confirm-on')){
						layer.confirm({
							title:a.getKey('confirm-on'),
							yes:onFun
						});
					}else{
						onFun();
					}
				}
			}
			func();
		});
	}
	this.save=function(conf){
		setDef(conf,'class','kokoSave');
		var obj=getObj(conf);
		obj.click(function(){
			var a=getObj(conf,$(this));
			var ajax=a.getKey("ajax","need ajax");
			var url= a.getKey('url');
			if(url==null&&ajax_url==null){
				layer.alert("need url");
			}else if(url==null){
				url=ajax_url;
			}
			var form= a.getKey('form');
			var before=a.getKey("before");
			var after=a.getKey("after");
			if(form==null){
				var pf=$(this).parents('form');
				if(pf.length==0){
					layer.alert("need form");
				}else{
					form=pf.eq(0);
				}
			}
			if(a.getKey('auto-name')){
				form.find("input,textarea,select").each(function(){
					if(('INPUT'==this.tagName&&this.type!='hidden')||'INPUT'!=this.tagName){
						if(this.name!='' && this.name.startsWith("data[")==false){
							this.name="data["+this.name+"]";
						}
					}
				});
			}
			if(form&&ajax){
				if($(form).find("input[name=ajax]").length){
					$(form).find("input[name=ajax]").val(ajax);
				}else{
					$(form).append("<input type='hidden' name='ajax' value='"+ajax+"'>");
				}
				if(typeof before=='function'){
					if(before()===false){
						return;
					}
				}else if(typeof before=='string' && typeof window[before] =='function'){
					if(window[before]()===false){
						return;
					}
				}
				$.post(url,$(form).serialize(),function(e){
					if(typeof window[after] =='function'){
						window[after](e);
					}else{
						if(e.state==1){
							layer.alert(e.msg,function(){
								if(a.getKey("back")!=null){
									history.back();
								}else{
									location.reload();
								}
							});
						}else{
							layer.alert(e.msg);
						}
					}
				});
			}
		});
	}
	this.del=function(conf){
		setDef(conf,'class','kokoDel');
		var obj=getObj(conf);
		obj.click(function(){
			var a=getObj(conf,$(this));
			var str=a.getKey('selStr');
			if(str!=null){
				var id=[];
				$(str).each(function(){
					if(this.checked){
						id[id.length]=this.id;
					}
				});
				if(id.length==0){
					layer.alert("您没有选择任何数据");
					return;
				}
				id=id.join(",")
			}else{
				var id=a.getKey('id',"need id");
			}
			var url= a.getKey('url');
			if(url==null&&ajax_url==null){
				layer.alert("need url");
			}else if(url==null){
				url=ajax_url;
			}
			var ajax= a.getKey('ajax',"need ajax");
			if(url){
				var confirm_text=a.getKey('confirm');
				if(confirm_text==null){
					confirm_text='您确定要是删除所选内容吗';
				}
				var obj_={ajax:ajax,id:id};
				layer.confirm(confirm_text,function(){
					$.post(url,obj_,function(e){
						if(e.state==1){
							layer.alert("删除成功",function(){
								location.reload();
							});
						}else{
							layer.alert(e.msg);
						}
					});					
				});
			}
		});
	}
	init();
}
function kokoTools(){
	this.findVal=function($str){
		if(typeof $str == "string"){
			var str=$($str);
			if(str.length==0){
				layer.alert("没有找到"+$str);
				return false;
			}
			str=str.val();
		}else if(typeof $str == "undefined"){
			layer.alert("空");
			return false;
		}
		else if($str.length==0){
			layer.alert("没有找到节点");
			return false;
		}
		else{
			str=$str.val();
		}
		return str;
	}
	this.checkTel=function (a){
		if((a=this.findVal(a))===false){
			return false;
		}
		if(/^1[3-9][0-9]{9}$/.test(a)===false){
			layer.alert("手机号格式不正确");
			return false;
		}
		return a;
	}
	this.checkEmail=function (a){
		if((a=this.findVal(a))===false){
			return false;
		}
		if(/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/.test(a)===false){
			layer.alert("邮箱格式不正确");
			return false;
		}
		return a;
	}
	this.checkShenfen=function (a){
		if((a=this.findVal(a))===false){
			return false;
		}
		if(/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/.test(a)===false){
			layer.alert("身份证号码格式不正确");
			return false;
		}
		return a;
	}
	this.checkEmpty=function (a,b){
		if((a=this.findVal(a))===false){
			return false;
		}
		if(a.length==0){
			layer.alert(b);
			return false;
		}
		return a;
	}
	this.checkPassword=function (a,b){
		if((a=this.findVal(a))===false){
			return false;
		}
		if(a.length<6){
			layer.alert("密码长度至少为6位");
			return false;
		}
		if(typeof b != 'undefined'){
			if((b=this.findVal(b))===false){
				return false;
			}
			if(a!=b){
				layer.alert("两次密码不一致");
				return false;
			}
		}
		return a;
	}
	this.menuRemove=function(a){
		if(typeof a.smartMenu == 'undefined'){
			$(document.head).append('<script src="/Public/plugin/smartMenu/jquery-smartMenu-min.js"></script><link href="/Public/plugin/smartMenu/smartMenu.css" rel="stylesheet" type="text/css" />');
		}
		$menuData=[[{text:"删除",func:function(){
			var e=$(this);
			e.remove();
		}}]];
		a.smartMenu($menuData);
	}

	this.getFromUrl=function (name)
	{
	     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
	     var r = window.location.search.substr(1).match(reg);
	     if(r!=null)return  unescape(r[2]); return null;
	}
	this.log=function(a){
		console.log(a);
	}
}