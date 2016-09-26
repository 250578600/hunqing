function kokoAPI(config,ajax_url){
	$this=this;
	if(typeof config == 'undefined'){
		config={};
	}
	function init(){
		if(typeof layer == 'undefined'){
			var ua=navigator.userAgent.toLowerCase();
			if(ua.indexOf("android")>0||ua.indexOf("phone")>0){
				$(document.head).append("<script src='/Public/plugin/layer.m/layer.js'></script>");
				$(document.head).append("<link rel='stylesheet' type='text/css' href='/Public/plugin/layer.m/need/layer.css'/>");
			}else{
				$(document.head).append("<script src='/Public/plugin/layer/layer.js'></script>");
				$(document.head).append("<link rel='stylesheet' type='text/css' href='/Public/plugin/layer/skin/layer.css'/>");
			}
			
		}
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
		obj.getKey2=function(key,def){
			$this=$(this);
			if(typeof $this.attr(key) !='undefined'){
				return $this.attr(key);
			}else if(typeof conf[key] !='undefined'){
				return conf[key];
			}else {
				if(typeof def !='undefined'){
					$this.attr(key,def);
					return def;
				}
			}
		}
		return obj;
	}
	function getObj(conf,obj){
		var obj=getObjFun(conf,obj);
		obj.callback=function (para,para1,para2,para3){
			var callback=getObjFun(conf,$(this)).getKey('callback');
			if(typeof window[callback]=='function'){
				return window[callback](para,para1,para2,para3);
			}else if(typeof callback=='function'){
				return callback(para,para1,para2,para3);
			}
			return true;
		}
		return obj;
	}
	function setDef(conf,key,def){
		if(typeof conf[key] == 'undefined'){
			conf[key]=def;
		}
	}

	this.edit=function (conf){
		setDef(conf,'class','kokoEdit');
		var obj=getObj(conf);
		obj.click(function(){
			var a=getObj(conf,$(this));
			var ajax=a.getKey('ajax',"need ajax");
			var key=a.getKey('key');
			var id=a.getKey('id',"");
			var url=a.getKey('url');
			if(url==null&&ajax_url==null){
				layer.alert("need url");
			}else if(url==null){
				url=ajax_url;
			}
			if(a.hasClass("kokoEditing")==false){
				a.addClass("kokoEditing");
				var v_back=a.text();
				a.attr("kokoSaved",encodeURIComponent(a.html()));
				a.html("<input type='text' style='height:20px;line-height:20px;min-width:50px;width:100%'>");
				var input=a.find("input");
				input.val(v_back);
				var func=a.getKey('checkFunc');
				if(typeof func=='string' && func!=''){
					func=eval('window.'+func);
				}
				var isFun=typeof func=='function';
				input.blur(function(){
					if(this.value && ( ( isFun && func(this.value) ) || !isFun) ){
						var v=this.value;
						var data={ajax:ajax,id:id};
						data['data['+key+']']=v;
						$.post(url,data,function(ret){
							if(a.callback(ret)===false){
								return;
							}
							if(ret.state==1){
								a.html(v);
							}else{
								layer.alert(ret.msg);
							}
							e.removeClass("kokoEditing");
						});
					}else{
						a.html(decodeURIComponent(a.attr("kokoSaved")));
						a.removeClass("kokoEditing");
					}
				});
				input[0].focus();
				input.keypress(function(e){
					if(e.keyCode==13){
						input.blur();
					}
				});
			}
		});
		obj.hover(function(){
			e=$(this);
			if(e.hasClass("kokoEditing"))return;
			this.title = '点击修改内容';
			e.attr("background",e.css("background"));
			e.css("background","#278296");
		},function(){
			e=$(this);
			this.title = '';
			e.css("background",e.attr("background"));
		});
	}
	this.switcher=function (conf){
		setDef(conf,'class','kokoSwitcher');
		var obj=getObj(conf);
		obj.click(function(){
			var a=getObj(conf,$(this));
			var on=a.getKey('on',"need on");
			var off=a.getKey('off');
			var ajax=a.getKey('ajax',"need ajax");
			var key=a.getKey('key',"need key");
			var id=a.getKey('id',"");
			var opposite=a.getKey('opposite');
			var no_post=a.getKey('no-post');
			var url=a.getKey('url');
			var value=a.getKey('value');
			var setDefault=a.getKey('setDefault');
			if(url==null&&ajax_url==null){
				layer.alert("need url");
			}else if(url==null){
				url=ajax_url;
			}
			function post(state,call){
				var data={ajax:ajax,key:key,id:id,value:state==(opposite==null?'on':'off')?1:0};
				if(setDefault){
					data['setDefault']=1;
				}
				$.post(url,data,function(e){
					if(a.callback({state:state,result:e})===false){
						return;
					};
					if(e.state==1){
						call();
					}else{
						layer.msg(e.msg);
					}
				});
			}
			function onFun(){
				post('on',function(){
					if(setDefault){
						location.reload();
					}
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
				if(setDefault){
					return;
				}
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
				var off=false;
				if(value=='off'||a.hasClass(on)){
					off=true;
				}
				if(value=='on'){
					off=false;
				}
				if(off){
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
	};
	this.save=function(conf){
		setDef(conf,'class','kokoSave');
		setDef(conf,'auto-name',true);
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
			if(form==null){
				var pf=$(this).parents('form');
				if(pf.length==0){
					layer.alert("need form");
				}else{
					form=pf.eq(0);
				}
			}else{
				form=$(form);
			}
			if(a.getKey('auto-name')){
				form.find("input,textarea,select").each(function(){
					if(('INPUT'==this.tagName&&this.type!='hidden')||'INPUT'!=this.tagName){
						if(this.name!=''&&this.name!='id' && this.name.startsWith("data[")==false){
							this.name="data["+this.name+"]";
						}
					}
				});
			}
			if(form&&ajax){
				if(form.find("input[name=ajax]").length){
					form.find("input[name=ajax]").val(ajax);
				}else{
					form.append("<input type='hidden' name='ajax' value='"+ajax+"'>");
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
				$.post(url,form.serialize(),function(e){
					if(a.callback(e)===false){
						return;
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
	};
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
				var id=a.getKey('id');
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
						if(a.callback(e)===false){
							return;
						}
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
	};
	this.agree=function(conf){
		setDef(conf,'class','kokoAgree');
		var obj=getObj(conf);
		function post(url,obj){
			$.post(url,obj,function(e){
				if(e.state==1){
					
				}
			});
		}
		obj.click(function(){
			var a=getObj(conf,$(this));
			var dis=a.getKey('dis');
			var id=a.getKey('id','没有id');
			var msg=a.getKey('msg');
			var ajax=a.getKey("ajax","need ajax");
			var url= a.getKey('url');
			if(url==null&&ajax_url==null){
				layer.alert("need url");
			}else if(url==null){
				url=ajax_url;
			}
			var obj={ajax:ajax,'data[id]':id,'data[msg]':'','data[type]':'agree'};
			if(dis!=null){
				obj['data[type]']='disagree';
			}
			if(msg!=null){
				layer.prompt({
					title:msg,
					formType:2
				},function(t){
					if(t.length==0){
						layer.alert('内容不能为空');
						return;
					}
					obj['data[msg]']=t;
					post(url,obj);
				});
			}else{
				post(url,obj);
			}
		});
	};
	this.upload=function(conf){
		setDef(conf,'class','kokoUpload');
		var obj=getObj(conf);
		var wx_checked=false;
		obj.each(function(){
			var a=getObj(conf,$(this));
			var count=a.getKey2("count",1);
			if(count>1){
				var model=a.getKey('model');
				if(model==null){
					alert("need model");
					return;
				}
				var v=$("."+model);
				$.kokoTool.menuRemove(v);
				v=v.eq(0);
				v.hide();
				var id="id"+Math.round(Math.random()*10000000000);
				v.attr("id",id);
				a.attr('model_id',id);
				$(document.head).append(v);
				if(a.getKey('insertBefore')==null&&a.getKey('append')==null){
					alert("need insertBefore or append");
					return;
				}
			}else{
				var input=a.getKey("input",'need input');
				var img=a.getKey("img",'need img');
			}
			var mode=a.getKey2('mode','input');
			if(mode=='wx'&&wx_checked==false){
				wx_checked=true;
				var wxUrl='http://res.wx.qq.com/open/js/jweixin-1.0.0.js';
				$("script").each(function(){
					if(this.src==wxUrl){
						$(document.head).append("<script id='wxjs' src='"+wxUrl+"'></script>");
						var wxConfig=function(){
							if(typeof wx == "object"){
								$.post("/index.php/Home/Upload/getWx",{action:'init',url:document.URL},function(e){
									e=JSON.parse(e);
									wx.config({
										debug: false,
										appId: e.appId,
										timestamp: e.timestamp,
										nonceStr: e.nonceStr,
										signature: e.signature,
										jsApiList: ['checkJsApi','chooseImage','uploadImage']
									  });
								});
								clearInterval(wxConfigIndex);
							}
						}
						wxConfigIndex=setInterval(wxConfig,200);
						return false;
					}
				});
			}
		});
		obj.click(function(){
			var a=getObj(conf,$(this));
			var url= a.getKey('url');
			if(url==null){
				url="/index.php/Home/Upload/";
			}
			var file_type=a.getKey('file_type');
			if(file_type==null){
				file_type='image';
			}
			var count=a.getKey2("count",1);
			var mode=a.getKey('mode');
			var show=function(json){
				json=JSON.parse(json);
				if(count>1){
					var input=a.getKey2("input",'input:hidden');
					var img=a.getKey2("img",'img');
					var m=$("#"+a.attr('model_id'));
					for(var i=0;i<json.length;i++){
						if(json[i].state=='SUCCESS'){
							console.log(json[i]);
							if(typeof a.attr('container')!= "undefined" && typeof a.attr('num')!= "undefined"){
								var len=$(a.attr('container')).find("."+a.attr('model')).length;
								if(len>=count){
									continue;
								}
							}
							var c=m.clone();
							c.show();
							c.attr("id",'');
							$.kokoTool.menuRemove(c);
//								c.addClass("model4del");
//								c.removeClass(up.attr('model'));
							c.find(input).val(json[i].url);
							c.find(img).attr("src",json[i].url);
							if(i==json.length-1){
								c.find(img).load(function(){
									layer.close(quan);
								});
							}
							var ap=a.getKey('append');
							var ib=a.getKey('insertBefore');
							if(ap!=null){
								$(ap).append(c);
							}else if(ib!=null){
								$(ib).before(c);
							}else{
								alert("你没有设置插入点");
								return;
							}
							a.callback(c,json[i]);	
						}
					}
				}else{
					if(json.state=='SUCCESS'){
						var img=a.getKey("img");
						$(a.getKey("input")).val(json.url);
						$(img).attr("src",json.url);
						$(img).load(function(){
							layer.close(quan);
						});
						a.callback(json);						
					}				
				}
			}
			if(mode=='wx'){
				wx.chooseImage({
				    count: count, // 默认9
				    sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
				    sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
				    success: function (res) {
				        var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
				        for(var i=0;i<localIds.length;i++){
					        wx.uploadImage({
					            localId: localIds[i], // 需要上传的图片的本地ID，由chooseImage接口获得
					            isShowProgressTips: 1, // 默认为1，显示进度提示
					            success: function (res) {
									quan=layer.load(2, {shade: false,content:'上传中请等待'});
					                var serverId = res.serverId; // 返回图片的服务器端ID
					                $.post('/index.php/Home/Upload/getWx',{action:'get',serverId:serverId},function(e){
					                	show(json);
					                });
					            }
					        });
				        }
				    }
				});
			}else{
				if(count==1){
					var input='<input type="file" accept="'+file_type+'/*" name="upfile">';
				}else{
					var input='<input type="file" multiple="multiple" accept="'+file_type+'/*" name="upfile[]">';
				}
				var upload_koko="upload_koko"+(count>1?1:0);
				var div='<div id="'+upload_koko+'" style="display:none;"><form target="upload_f" method="POST" enctype="multipart/form-data" action="'+url+'?action=uploadimage" >'+input+'</form></div>';
				$("#"+upload_koko).remove();
				$(document.body).append(div);
				if($("#upload_f").length==0){
					$("body").append("<iframe id='upload_f' name='upload_f' style='display:none'></iframe>");	
				}
				var input_file=$("#"+upload_koko+" form input[type=file]");
				input_file.change(function(){
					if(this.value=='')return;
					var size=a.getKey('size');
					if(size!=null && !isNaN(size) && size!=''){
						size=parseInt(size);
						var files=this.files;
						for(var q=0;q<files.length;q++){
							if(files[q].size>size){
								layer.alert("您选择的图片:"+files[q].name+",大小为"+files[q].size +'KB，请选择小于'+size+'KB以下的图片');
								return;
							}
						}				
					}
					var ifr=$("#upload_f")[0];
					quan=layer.load(2, {shade: false});
					var iframeload=function(){
						if (!ifr.readyState || ifr.readyState == "complete") { 
							var json=$(ifr.contentDocument.body).html();
							show(json);
						}
						$("#upload_f").remove();
					}
					ifr. onload = ifr. onreadystatechange = iframeload;
					$("#"+upload_koko+" form").submit();
				});
				input_file[0].click();	
			}
		});
	};
	
	init();
}
(function($){
	$.extend({kokoTool:{
		findVal:function($str){
			if(typeof $str == "string"){
				if($str.substr(0,1)=='.'||$str.substr(0,1)=='#'){
					var str=$($str);
					if(str.length==0){
						layer.alert("没有找到"+$str);
						return false;
					}
					str=str.val();
				}else{
					str=$str;
				}
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
		},checkTel:function (a,b){
			if((a=this.findVal(a))===false){
				return false;
			}
			if(/^1[3-9][0-9]{9}$/.test(a)===false){
				if(b!==null)layer.alert("手机号格式不正确");
				return false;
			}
			return a;
		},checkEmail:function (a,b){
			if((a=this.findVal(a))===false){
				return false;
			}
			if(/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/.test(a)===false){
				if(b!==null)layer.alert("邮箱格式不正确");
				return false;
			}
			return a;
		},checkShenfen:function (a,b){
			if((a=this.findVal(a))===false){
				return false;
			}
			if(/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/.test(a)===false){
				if(b!==null)layer.alert("身份证号码格式不正确");
				return false;
			}
			return a;
		},checkEmpty:function (a,b){
			if((a=this.findVal(a))===false){
				return false;
			}
			if(a.length==0){
				if(b!==null)layer.alert(b);
				return false;
			}
			return a;
		},checkPassword:function (a,b,c){
			if((a=this.findVal(a))===false){
				return false;
			}
			if(a.length<6){
				if(!(c&&a.length==0)){
					layer.alert("密码长度至少为6位");
					return false;
				}
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
		},checkNumber:function (a,b){
			if((a=this.findVal(a))===false){
				return false;
			}
			if(a.length==0||isNaN(a)){
				if(b!==null)layer.alert("请输入数字");
				return false;
			}
			return a;
		},checkInt:function (a,b){
			if((a=this.findVal(a))===false){
				return false;
			}
			if(a.length==0||/\d+^$/.test(a)==false||/0\d+^$/.test(a)){
				if(b!==null)layer.alert("请输入整数");
				return false;
			}
			return a;
		},menuRemove:function(a){
			if(typeof a.smartMenu == 'undefined'){
				$(document.head).append('<script src="/Public/plugin/smartMenu/jquery-smartMenu-min.js"></script><link href="/Public/plugin/smartMenu/smartMenu.css" rel="stylesheet" type="text/css" />');
			}
			$menuData=[[{text:"删除",func:function(){
				var e=$(this);
				e.remove();
			}}]];
			a.smartMenu($menuData);
		},getFromUrl:function (name){
		     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
		     var r = window.location.search.substr(1).match(reg);
		     if(r!=null)return  unescape(r[2]); return null;
		},log:function(a){
			console.log(a);
		},checkValue:function(){
			$("document").on("change","input[type=numeric]",function(){
				if(this.value!=''&&isNaN(this.value)){
					this.value=0;
				}
			});
		},savePageData:function(){
			var d={'input':[],'select':[],'textarea':[]};
			$("input").each(function(i,v){
				d.input[i]=this.value;
			});
			$("select").each(function(i,v){
				d.select[i]=this.value;
			});
			$("textarea").each(function(i,v){
				d.textarea[i]=this.value;
			});
			localStorage.setItem('pageSave_'+document.URL,JSON.stringify(d));
		},readPageData:function(){
			var d=JSON.parse(localStorage.getItem('pageSave_'+document.URL));
			$("input").each(function(i,v){
				this.value=d.input[i];
			});
			$("select").each(function(i,v){
				$(this).val(d.select[i]);
			});
			$("textarea").each(function(i,v){
				this.value=d.textarea[i];
			});
		},toUrlData:function (data,prev){
			var out={};
			for(var i in data){
				if(typeof data[i] == 'object'){
					var ret=this.toUrlData(data[i],(prev==null?i:prev+'['+i+']'));
					for(var k in ret){
						out[k]=ret[k];
					}
				}else{
					out[prev==null?i:prev+'['+i+']']=data[i];
				}
			}
			if(prev==null){
				
			}
			return out;
		}
	}});
})(jQuery);